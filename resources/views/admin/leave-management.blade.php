<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HR Admin Panel - Leave Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div id="app" class="min-h-screen flex flex-col">
        <header class="bg-white shadow p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">HR Admin Panel</h1>
                <nav>
                    <a href="/" class="text-blue-600 hover:text-blue-800 mx-2">Home</a>
                    <a href="/admin/leave-management" class="text-blue-600 hover:text-blue-800 mx-2">Leave Management</a>
                    {{-- Add other admin links here --}}
                </nav>
            </div>
        </header>

        <main class="flex-grow container mx-auto p-6">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Leave Request Management</h2>
            <leave-management></leave-management>
        </main>

        <footer class="bg-gray-800 text-white p-4 text-center">
            &copy; {{ date('Y') }} HRMS. All rights reserved.
        </footer>
    </div>
</body>
</html>