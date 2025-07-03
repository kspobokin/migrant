@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Template Details</h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Name:</strong> {{ $template->name }}</p>
            <p><strong>Type:</strong> {{ $template->type }}</p>
            @if ($template->file_path)
                <p><strong>File:</strong> <a href="{{ Storage::url($template->file_path) }}" class="btn btn-info btn-sm">Download</a></p>
            @endif
            @if ($template->content)
                <p><strong>Content:</strong> {{ $template->content }}</p>
            @endif
            <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('templates.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
