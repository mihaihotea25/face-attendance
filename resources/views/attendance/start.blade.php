<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">

    <h1 class="text-3xl font-bold mb-6">📸 Start Attendance</h1>

    <video id="video" class="rounded-lg shadow-md w-80 h-60 mb-4" autoplay playsinline></video>

    <button id="startButton" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 mb-4">
        Start Camera
    </button>

    <p id="status" class="text-gray-600"></p>

    <script>
    const video = document.getElementById('video');
    const startButton = document.getElementById('startButton');
    const statusText = document.getElementById('status');

    let stream = null;
    let isCameraRunning = false;

    let intervalId;

    startButton.addEventListener('click', async () => {
        if (!isCameraRunning) {
            // Start camera
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
            // Stop camera
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

        } catch (error) {
            console.error('Error sending frame:', error);
            statusText.innerText = '❌ Error: ' + error.message;
        }
    }
</script>


</body>
</html>
