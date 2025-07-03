@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Contractor Details</h1>
    <div class="card">
        <div class="card-body">
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
            <a href="{{ route('contractors.edit', $contractor->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('contractors.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
