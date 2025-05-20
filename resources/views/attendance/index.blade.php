@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')
    <h1 class="text-3xl font-bold mb-6 text-center">🎓 Attendance Dashboard</h1>

    <div class="flex flex-col gap-4 items-center">
        <a href="{{ route('attendance.start') }}"
           class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg text-center hover:bg-blue-700">
            Start Attendance
        </a>

        <a href="{{ route('attendance.reports') }}"
           class="bg-blue-600 text-white font-semibold px-6 py-3 rounded-lg text-center hover:bg-blue-700">
            Attendance Reports
        </a>
    </div>
@endsection
