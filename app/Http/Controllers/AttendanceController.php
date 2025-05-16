<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function start()
    {
        return view('attendance.start');
    }


    public function recognize(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $client = new \GuzzleHttp\Client();
            $response = $client->post('http://127.0.0.1:8001/predict', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (!isset($responseData['student']) || !isset($responseData['confidence'])) {
                return response()->json(['error' => 'Invalid response from model'], 400);
            }

            $studentId = $responseData['student'];
            $confidence = $responseData['confidence'];

            if ($confidence < 0.5) {
                return response()->json(['message' => 'Confidence too low to mark attendance', 'marked' => false]);
            }

            $now = now();
            $oneMinuteAgo = $now->copy()->subMinute();

            $alreadyMarked = Attendance::where('student_id', $studentId)
                ->where('created_at', '>=', $oneMinuteAgo)
                ->exists();

            $student = Student::find($studentId);

            if (!$alreadyMarked) {
                Attendance::create([
                    'student_id' => $studentId,
                    'date' => $now->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                return response()->json([
                    'message' => "✅ Attendance marked: {$student->name}",
                    'student' => $student->name,
                    'marked' => true 
                ]);
            }

            return response()->json([
                'message' => "✅ Already marked: {$student->name}",
                'student' => $student->name,
                'marked' => false 
            ]);
        }

        return response()->json(['error' => 'No file uploaded', 'marked' => false], 400);
    }


}
