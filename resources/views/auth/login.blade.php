<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            @if (session('error'))
                <div class="error">{{ session('error') }}</div>
            @endif
            <button type="submit">Login</button>
        </form>
        <p><a href="{{ route('counterparty.forgot-password') }}">Forgot Password?</a></p>
    </div>
</body>
</html>
