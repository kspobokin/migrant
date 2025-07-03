@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Forgot Password</h1>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->has('email'))
        <div class="alert alert-danger">{{ $errors->first('email') }}</div>
    @endif
    <form action="{{ route('counterparty.forgot-password') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
        <a href="{{ route('counterparty.login') }}" class="btn btn-secondary">Back to Login</a>
    </form>
</div>
@endsection
