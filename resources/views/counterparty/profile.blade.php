@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Profile</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card mb-4">
        <div class="card-body">
            <h5>Contractor Details</h5>
            <p><strong>FIO (RU):</strong> {{ $contractor->last_name_ru }} {{ $contractor->first_name_ru }} {{ $contractor->patronymic_ru }}</p>
            <p><strong>FIO (LAT):</strong> {{ $contractor->last_name_lat }} {{ $contractor->first_name_lat }} {{ $contractor->patronymic_lat }}</p>
            <p><strong>Email:</strong> {{ $contractor->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $contractor->phone ?? 'N/A' }}</p>
            <p><strong>INN:</strong> {{ $contractor->inn ?? 'N/A' }}</p>
            <p><strong>Insurance Policy:</strong> {{ $contractor->insurance_policy ?? 'N/A' }}</p>
            <p><strong>Registration Address:</strong> {{ $contractor->registration_address ?? 'N/A' }}</p>
            <p><strong>Type:</strong> {{ $contractor->type }}</p>
            <p><strong>Role:</strong> {{ $contractor->role }}</p>
            <p><strong>Extra Fields:</strong> {{ json_encode($contractor->extra_fields ?? []) }}</p>
        </div>
    </div>
    <h5>Update Profile</h5>
    <form action="{{ route('counterparty.profile') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Last Name (RU)</label>
            <input type="text" name="last_name_ru" class="form-control @error('last_name_ru') is-invalid @enderror" value="{{ old('last_name_ru', $contractor->last_name_ru) }}">
            @error('last_name_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">First Name (RU)</label>
            <input type="text" name="first_name_ru" class="form-control @error('first_name_ru') is-invalid @enderror" value="{{ old('first_name_ru', $contractor->first_name_ru) }}">
            @error('first_name_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Patronymic (RU)</label>
            <input type="text" name="patronymic_ru" class="form-control @error('patronymic_ru') is-invalid @enderror" value="{{ old('patronymic_ru', $contractor->patronymic_ru) }}">
            @error('patronymic_ru')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $contractor->email) }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $contractor->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">INN</label>
            <input type="text" name="inn" class="form-control @error('inn') is-invalid @enderror" value="{{ old('inn', $contractor->inn) }}">
            @error('inn')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Insurance Policy</label>
            <input type="text" name="insurance_policy" class="form-control @error('insurance_policy') is-invalid @enderror" value="{{ old('insurance_policy', $contractor->insurance_policy) }}">
            @error('insurance_policy')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Registration Address</label>
            <input type="text" name="registration_address" class="form-control @error('registration_address') is-invalid @enderror" value="{{ old('registration_address', $contractor->registration_address) }}">
            @error('registration_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Extra Fields (JSON)</label>
            <textarea name="extra_fields" class="form-control @error('extra_fields') is-invalid @enderror">{{ old('extra_fields', json_encode($contractor->extra_fields)) }}</textarea>
            @error('extra_fields')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
    <h5 class="mt-4">Documents</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>File</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr>
                    <td>{{ $document->title }}</td>
                    <td><a href="{{ Storage::url($document->file_path) }}" class="btn btn-info btn-sm">Download</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
