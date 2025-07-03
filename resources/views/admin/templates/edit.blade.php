<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template</title>
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
        <h1>Edit Template</h1>
        <form method="POST" action="{{ route('templates.update', $template->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="text" name="title" placeholder="Template Title" value="{{ old('title', $template->title) }}" required>
            <textarea id="content-editor" name="content" placeholder="Template Content">{{ old('content', $template->content) }}</textarea>
            <input type="file" name="file" accept=".doc,.docx,.xlsx,.pdf,.csv,.html">
            <p>Current File: {{ $template->file_path ? basename($template->file_path) : 'None' }}</p>
            @if (!empty($placeholders))
                <h2>Template Placeholders</h2>
                @foreach ($placeholders as $placeholder)
                    <div>
                        <label for="placeholder_{{ $placeholder }}">{{ $placeholder }}</label>
                        <input type="text" name="placeholders[{{ $placeholder }}]" id="placeholder_{{ $placeholder }}" value="{{ old('placeholders.' . $placeholder, $template->placeholders[$placeholder] ?? '') }}">
                    </div>
                @endforeach
            @endif
            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <button type="submit">Save Template</button>
        </form>
        <p><a href="{{ route('templates.index') }}">Back to Templates</a></p>
    </div>
</body>
</html>
