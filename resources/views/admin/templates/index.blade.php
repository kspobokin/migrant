@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Templates</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('templates.create') }}" class="btn btn-primary mb-3">Create Template</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($templates as $template)
                <tr>
                    <td>{{ $template->name }}</td>
                    <td>{{ $template->type }}</td>
                    <td>
                        <a href="{{ route('templates.show', $template->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="d-inline">
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
