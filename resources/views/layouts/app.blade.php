<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>I am present!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <a href="{{ url('/attendance') }}" class="text-xl font-bold text-blue-600 hover:text-blue-800">I am present!</a>

        @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-white bg-red-600 px-4 py-2 rounded hover:bg-red-700">
                Logout
            </button>
        </form>
        @endauth
    </nav>

    <!-- Page Content -->
    <main class="flex-grow container mx-auto p-6">
        @yield('content')
    </main>

</body>
</html>
