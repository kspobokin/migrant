@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create Contractor</h1>
    <form action="{{ route('contractors.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Last Name (RU)</label>
            <input type="text" name="last_name_ru" class="form-control @error('last_name_ru') is-invalid @enderror" value="{{ old('last_name_ru') }}">
            @error('last_name_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">First Name (RU)</label>
            <input type="text" name="first_name_ru" class="form-control @error('first_name_ru') is-invalid @enderror" value="{{ old('first_name_ru') }}">
            @error('first_name_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Patronymic (RU)</label>
            <input type="text" name="patronymic_ru" class="form-control @error('patronymic_ru') is-invalid @enderror" value="{{ old('patronymic_ru') }}">
            @error('patronymic_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">INN</label>
            <input type="text" name="inn" class="form-control @error('inn') is-invalid @enderror" value="{{ old('inn') }}">
            @error('inn')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Insurance Policy</label>
            <input type="text" name="insurance_policy" class="form-control @error('insurance_policy') is-invalid @enderror" value="{{ old('insurance_policy') }}">
            @error('insurance_policy')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Registration Address</label>
            <input type="text" name="registration_address" class="form-control @error('registration_address') is-invalid @enderror" value="{{ old('registration_address') }}">
            @error('registration_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select @error('type') is-invalid @enderror">
                <option value="individual">Individual</option>
                <option value="legal">Legal</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select @error('role') is-invalid @enderror">
                <option value="customer">Customer</option>
                <option value="performer">Performer</option>
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Extra Fields (JSON)</label>
            <textarea name="extra_fields" class="form-control @error('extra_fields') is-invalid @enderror">{{ old('extra_fields') }}</textarea>
            @error('extra_fields')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('contractors.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
