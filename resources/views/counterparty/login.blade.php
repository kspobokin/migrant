@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Login</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->has('login'))
        <div class="alert alert-danger">{{ $errors->first('login') }}</div>
    @endif
    <form action="{{ route('counterparty.login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email or Phone</label>
            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="{{ route('counterparty.forgot-password') }}" class="btn btn-link">Forgot Password?</a>
        <a href="{{ route('counterparty.register') }}" class="btn btn-secondary">Register</a>
    </form>
</div>
@endsection
