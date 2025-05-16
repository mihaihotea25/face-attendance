@extends('layouts.app')

@section('title', 'Start Attendance')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen">

    <h1 class="text-3xl font-bold mb-6">📸 Start Attendance</h1>

    <video id="video" class="rounded-lg shadow-md w-80 h-60 mb-4" autoplay playsinline></video>

    <button id="startButton" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 mb-4">
        Start Camera
    </button>

    <p id="status" class="text-gray-600"></p>

    <a href="{{ url('/attendance') }}" class="mt-6 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow">
    ← Back
    </a>

    <audio id="tickSound" src="{{ asset('sounds/tick.mp3') }}"></audio>

    <script>
        const video = document.getElementById('video');
        const startButton = document.getElementById('startButton');
        const statusText = document.getElementById('status');
        const tickSound = document.getElementById('tickSound');

        let stream = null;
        let isCameraRunning = false;

        let intervalId;

        startButton.addEventListener('click', async () => {
            if (!isCameraRunning) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = stream;

                    intervalId = setInterval(captureAndSendFrame, 3000);

                    startButton.innerText = 'Stop Camera';
                    isCameraRunning = true;
                    statusText.innerText = '📸 Camera started.';

                } catch (error) {
                    console.error('Error accessing camera:', error);
                    statusText.innerText = '❌ Could not access camera.';
                }
            } else {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                }

                clearInterval(intervalId);

                startButton.innerText = 'Start Camera';
                isCameraRunning = false;
                statusText.innerText = '⏹️ Camera stopped.';
            }
        });

        async function captureAndSendFrame() {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));

            const formData = new FormData();
            formData.append('file', blob, 'frame.jpg');

            try {
                const response = await fetch('{{ route('attendance.recognize') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();
                statusText.innerText = result.message;
                if (result.marked === true) {
                    tickSound.play();
                }

            } catch (error) {
                console.error('Error sending frame:', error);
                statusText.innerText = '❌ Error: ' + error.message;
            }
        }
    </script>

</div>
@endsection
