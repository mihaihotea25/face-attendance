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
    
            // Prepare file to send to Python server
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
    
            // Create attendance record
            \App\Models\Attendance::create([
                'student_id' => $studentId,
                'date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            return response()->json([
                'message' => 'Attendance marked successfully',
                'student' => $studentId
            ]);
        }
    
        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
