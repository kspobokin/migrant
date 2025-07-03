@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create Document</h1>
    <form action="{{ route('documents.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Template</label>
            <select name="template_id" class="form-select @error('template_id') is-invalid @enderror">
                <option value="">Select Template</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->type }})</option>
                @endforeach
            </select>
            @error('template_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Contractor 1</label>
            <select name="contractor1_id" class="form-select @error('contractor1_id') is-invalid @enderror">
                <option value="">Select Contractor</option>
                @foreach ($contractors as $contractor)
                    <option value="{{ $contractor->id }}">{{ $contractor->last_name_ru }} {{ $contractor->first_name_ru }}</option>
                @endforeach
            </select>
            @error('contractor1_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Contractor 2 (optional)</label>
            <select name="contractor2_id" class="form-select @error('contractor2_id') is-invalid @enderror">
                <option value="">None</option>
                @foreach ($contractors as $contractor)
                    <option value="{{ $contractor->id }}">{{ $contractor->last_name_ru }} {{ $contractor->first_name_ru }}</option>
                @endforeach
            </select>
            @error('contractor2_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
