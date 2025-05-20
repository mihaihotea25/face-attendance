@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">📊 Attendance Reports</h1>

    <form method="GET" action="{{ route('attendance.reports') }}" class="mb-6 space-x-4">
        <input type="date" name="date" value="{{ $date }}" class="border rounded px-2 py-1">
        <input type="time" name="start_time" value="{{ $start }}" class="border rounded px-2 py-1">
        <input type="time" name="end_time" value="{{ $end }}" class="border rounded px-2 py-1">
        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded">Filter</button>
        @if(count($records))
            <a href="{{ route('attendance.reports.download', request()->query()) }}"
               class="bg-green-600 text-white px-4 py-1 rounded">Download CSV</a>
        @endif
    </form>

    @if(count($records))
        <table class="min-w-full bg-white border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2 text-left">Student Name</th>
                    <th class="border px-4 py-2 text-left">Date</th>
                    <th class="border px-4 py-2 text-left">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td class="border px-4 py-1">{{ $record->name }}</td>
                    <td class="border px-4 py-1">{{ $record->date }}</td>
                    <td class="border px-4 py-1">{{ \Carbon\Carbon::parse($record->created_at)->format('H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No records found for selected filters.</p>
    @endif
    <div class="flex justify-center mt-6">
        <a href="{{ url('/attendance') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow">
            ← Back
        </a>
    </div>
</div>
@endsection
