@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen">
    <h2 class="text-2xl font-bold mb-4">Login</h2>

    <form method="POST" action="/login" class="w-full max-w-xs">
        @csrf
        <input name="email" type="email" placeholder="Email" class="mb-2 w-full p-2 border rounded" required>
        <input name="password" type="password" placeholder="Password" class="mb-2 w-full p-2 border rounded" required>

        @error('email')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror

        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Login</button>
    </form>

    <a href="/register" class="mt-4 text-blue-600">Don't have an account? Register</a>
</div>
@endsection
