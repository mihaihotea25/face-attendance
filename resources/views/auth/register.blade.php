@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen">
    <h2 class="text-2xl font-bold mb-4">Register</h2>

    <form method="POST" action="/register" class="w-full max-w-xs">
        @csrf
        <input name="name" type="text" placeholder="Name" class="mb-2 w-full p-2 border rounded" required>
        <input name="email" type="email" placeholder="Email" class="mb-2 w-full p-2 border rounded" required>
        <input name="password" type="password" placeholder="Password" class="mb-2 w-full p-2 border rounded" required>
        <input name="password_confirmation" type="password" placeholder="Confirm Password" class="mb-2 w-full p-2 border rounded" required>

        @error('email')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror

        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Register</button>
    </form>

    <a href="/login" class="mt-4 text-blue-600">Already have an account? Login</a>
</div>
@endsection
