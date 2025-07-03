@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Contractors</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('contractors.create') }}" class="btn btn-primary mb-3">Create Contractor</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>FIO</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Type</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contractors as $contractor)
                <tr>
                    <td>{{ $contractor->last_name_ru }} {{ $contractor->first_name_ru }} {{ $contractor->patronymic_ru }}</td>
                    <td>{{ $contractor->email ?? 'N/A' }}</td>
                    <td>{{ $contractor->phone ?? 'N/A' }}</td>
                    <td>{{ $contractor->type }}</td>
                    <td>{{ $contractor->role }}</td>
                    <td>
                        <a href="{{ route('contractors.show', $contractor->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('contractors.edit', $contractor->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('contractors.destroy', $contractor->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
