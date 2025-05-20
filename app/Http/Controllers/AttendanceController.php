<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
                    'user_id' => auth()->id(),
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

    public function showReports(Request $request)
    {
        $date = $request->input('date');
        $start = $request->input('start_time');
        $end = $request->input('end_time');

        $query = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->select('students.name', 'attendances.date', 'attendances.created_at')
            ->where('attendances.user_id', auth()->id()) 
            ->orderBy('attendances.created_at', 'desc');

        if ($date) {
            $query->whereDate('attendances.created_at', $date);
        }

        if ($start && $end) {
            $query->whereTime('attendances.created_at', '>=', $start)
                ->whereTime('attendances.created_at', '<=', $end);
        }

        $records = $query->get();

        return view('attendance.reports', compact('records', 'date', 'start', 'end'));
    }

    public function downloadReport(Request $request)
    {
        $date = $request->input('date');
        $start = $request->input('start_time');
        $end = $request->input('end_time');

        $query = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->select('students.name', 'attendances.date', 'attendances.created_at')
            ->where('attendances.user_id', auth()->id()) 
            ->orderBy('attendances.created_at', 'desc');

        if ($date) {
            $query->whereDate('attendances.created_at', $date);
        }

        if ($start && $end) {
            $query->whereTime('attendances.created_at', '>=', $start)
                ->whereTime('attendances.created_at', '<=', $end);
        }

        $records = $query->get();

        $response = new StreamedResponse(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Date', 'Time']);

            foreach ($records as $row) {
                fputcsv($handle, [$row->name, $row->date, $row->created_at]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="attendance_report.csv"');

        return $response;
    }

}
