<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        @if (Auth::user())
            <p class="success">Welcome, {{ Auth::user()->email }}! Admin Status: {{ Auth::user()->is_admin ? 'Active' : 'Inactive' }}</p>
            <a href="{{ route('logout') }}">Logout</a>
        @else
            <p class="error">You are not authenticated.</p>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
    </div>
</body>
</html>
