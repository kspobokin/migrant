@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create Template</h1>
    <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select @error('type') is-invalid @enderror">
                <option value="txt">Text</option>
                <option value="html">HTML</option>
                <option value="docx">Word</option>
                <option value="xlsx">Excel</option>
                <option value="pdf">PDF</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">File (for docx, xlsx, pdf)</label>
            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror">
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Content (for txt, html)</label>
            <textarea name="content" class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
