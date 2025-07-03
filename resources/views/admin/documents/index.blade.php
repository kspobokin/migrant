@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Documents</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('documents.create') }}" class="btn btn-primary mb-3">Create Document</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Contractor 1</th>
                <th>Contractor 2</th>
                <th>File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr>
                    <td>{{ $document->title }}</td>
                    <td>{{ $document->contractor1 ? $document->contractor1->last_name_ru . ' ' . $document->contractor1->first_name_ru : 'N/A' }}</td>
                    <td>{{ $document->contractor2 ? $document->contractor2->last_name_ru . ' ' . $document->contractor2->first_name_ru : 'N/A' }}</td>
                    <td><a href="{{ Storage::url($document->file_path) }}" class="btn btn-info btn-sm">Download</a></td>
                    <td>
                        <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline">
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
