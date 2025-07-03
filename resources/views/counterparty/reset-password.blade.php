@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reset Password</h1>
    @if ($errors->has('email'))
        <div class="alert alert-danger">{{ $errors->first('email') }}</div>
    @endif
    <form action="{{ route('counterparty.reset-password.submit') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
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
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
        <a href="{{ route('counterparty.login') }}" class="btn btn-secondary">Back to Login</a>
    </form>
</div>
@endsection
