<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/42.0.2/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ClassicEditor !== 'undefined') {
                ClassicEditor.create(document.querySelector('#content-editor'))
                    .then(editor => console.log('CKEditor initialized'))
                    .catch(error => console.error('CKEditor initialization error:', error));
            } else {
                console.error('CKEditor not loaded: ClassicEditor is undefined');
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Document</h1>
        <form method="POST" action="{{ route('documents.update', $document->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="text" name="title" placeholder="Document Title" value="{{ old('title', $document->title) }}" required>
            <textarea id="content-editor" name="content" placeholder="Document Content">{{ old('content', $document->content) }}</textarea>
            <input type="file" name="file" accept=".doc,.docx,.xlsx,.pdf,.csv,.html">
            <p>Current File: {{ $document->file_path ? basename($document->file_path) : 'None' }}</p>
            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <button type="submit">Save Document</button>
        </form>
        <p><a href="{{ route('documents.index') }}">Back to Documents</a></p>
    </div>
</body>
</html>
