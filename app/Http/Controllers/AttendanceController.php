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
    if (!$request->hasFile('file')) {
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    $file = $request->file('file');

    try {
        // Send the image to the Python server
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

        if (!isset($responseData['student'])) {
            return response()->json(['error' => 'No student recognized'], 400);
        }

        $studentId = $responseData['student'];
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found in database'], 404);
        }

        // Check for attendance within last minute
        $recent = Attendance::where('student_id', $studentId)
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if ($recent) {
            return response()->json([
                'message' => $student->name . ' already marked recently',
                'marked' => false,
                'student' => $student->name
            ]);
        }

        // Mark attendance
        Attendance::create([
            'student_id' => $studentId,
            'date' => now()->toDateString()
        ]);

        return response()->json([
            'message' => '✅ Attendance marked for ' . $student->name,
            'marked' => true,
            'student' => $student->name
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}
}
