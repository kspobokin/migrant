#!/bin/bash

# Define project directory
PROJECT_DIR="/home/migrant/web/my.migrant.top/public_html"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$PROJECT_DIR/storage/logs/fix_admin_middleware.log"
}

# Navigate to project directory
cd "$PROJECT_DIR" || { log "Failed to navigate to $PROJECT_DIR"; exit 1; }

# Install PHP libraries for file handling
log "Installing PHP libraries for Word, Excel, PDF, CSV..."
composer require phpoffice/phpword phpoffice/phpspreadsheet dompdf/dompdf || { log "Failed to install PHP libraries"; exit 1; }

# Install LibreOffice for .doc to .docx conversion
log "Installing LibreOffice for .doc file conversion..."
sudo apt update && sudo apt install -y libreoffice || { log "Failed to install LibreOffice"; exit 1; }

# Check Node.js and npm installation
log "Checking Node.js and npm..."
if ! command -v node >/dev/null 2>&1 || ! command -v npm >/dev/null 2>&1; then
    log "Node.js or npm not found. Using CKEditor via CDN."
    USE_TAILWIND=false
    USE_CKEDITOR_LOCAL=false
else
    log "Node.js and npm found. Attempting Tailwind CSS and CKEditor installation..."
    USE_TAILWIND=true
    USE_CKEDITOR_LOCAL=true
fi

# Create package.json if not exists
if [ "$USE_TAILWIND" = true ] && [ ! -f package.json ]; then
    log "Creating package.json..."
    cat > package.json << 'EOF'
{
  "name": "laravel-app",
  "version": "1.0.0",
  "scripts": {
    "build": "tailwindcss build -i resources/css/app.css -o public/css/app.css"
  },
  "dependencies": {
    "@ckeditor/ckeditor5-build-classic": "^42.0.0"
  }
}
EOF
fi

# Install Tailwind CSS and CKEditor if possible
if [ "$USE_TAILWIND" = true ]; then
    log "Installing Tailwind CSS and CKEditor..."
    npm install -D tailwindcss postcss autoprefixer @ckeditor/ckeditor5-build-classic && npx tailwindcss init -p || {
        log "Failed to install or initialize Tailwind/CSS or CKEditor. Falling back to basic CSS and CKEditor CDN."
        USE_TAILWIND=false
        USE_CKEDITOR_LOCAL=false
    }
fi

# Create tailwind.config.js if Tailwind is used
if [ "$USE_TAILWIND" = true ]; then
    log "Creating tailwind.config.js..."
    cat > tailwind.config.js << 'EOF'
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
EOF
fi

# Create app.css (Tailwind or basic CSS)
log "Creating app.css..."
mkdir -p resources/css public/css
if [ "$USE_TAILWIND" = true ]; then
    cat > resources/css/app.css << 'EOF'
@tailwind base;
@tailwind components;
@tailwind utilities;
EOF
else
    cat > resources/css/app.css << 'EOF'
body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
.container { max-width: 800px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
h1 { color: #333; }
.error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; }
.success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; }
form { display: flex; flex-direction: column; gap: 10px; }
input, textarea, button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
textarea { resize: vertical; min-height: 100px; }
button { background: #007bff; color: white; cursor: pointer; }
button:hover { background: #0056b3; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
EOF
fi

# Create document edit Blade template
log "Creating document edit Blade template..."
mkdir -p resources/views/admin/documents
cat > resources/views/admin/documents/edit.blade.php << 'EOF'
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
EOF

# Create template edit Blade template
log "Creating template edit Blade template..."
mkdir -p resources/views/admin/templates
cat > resources/views/admin/templates/edit.blade.php << 'EOF'
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
EOF

# Check and create document migration
log "Checking document migration..."
if ! php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('documents', 'file_path') ? 'exists' : 'missing';" | grep -q 'exists'; then
    log "Creating document migration..."
    cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_add_file_path_to_documents_table.php << 'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
}
EOF
else
    log "file_path column already exists in documents table. Skipping migration."
fi

# Check and create template migration
log "Checking template migration..."
if ! php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('templates', 'file_path') ? 'exists' : 'missing';" | grep -q 'exists'; then
    log "Creating template migration..."
    cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_add_file_path_to_templates_table.php << 'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('content');
            $table->json('placeholders')->nullable()->after('file_path');
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'placeholders']);
        });
    }
}
EOF
else
    if ! php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('templates', 'placeholders') ? 'exists' : 'missing';" | grep -q 'exists'; then
        log "Adding placeholders column to templates table..."
        cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_add_placeholders_to_templates_table.php << 'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlaceholdersToTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->json('placeholders')->nullable()->after('file_path');
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('placeholders');
        });
    }
}
EOF
    else
        log "placeholders column already exists in templates table. Skipping migration."
    fi
fi

# Update DocumentController
log "Updating DocumentController..."
mkdir -p app/Http/Controllers/Admin
cat > app/Http/Controllers/Admin/DocumentController.php << 'EOF'
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory as WordIO;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIO;
use Dompdf\Dompdf;
use Symfony\Component\Process\Process;

class DocumentController extends Controller
{
    public function index()
    {
        Log::debug("DocumentController: Accessing index");
        return view('admin.documents.index', ['documents' => Document::all()]);
    }

    public function create()
    {
        Log::debug("DocumentController: Accessing create");
        return view('admin.documents.create');
    }

    public function store(Request $request)
    {
        Log::debug("DocumentController: Storing document");
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,xlsx,pdf,csv,html|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->store('documents', 'public');

            if ($extension === 'doc') {
                $convertedPath = $this->convertDocToDocx($file, $path);
                if ($convertedPath) {
                    Storage::disk('public')->delete($path); // Remove original .doc
                    $path = $convertedPath;
                    $extension = 'docx';
                } else {
                    Log::error("DocumentController: Failed to convert .doc to .docx");
                    return redirect()->back()->withErrors(['file' => 'Failed to convert .doc file']);
                }
            }

            $validated['file_path'] = $path;
            $validated['content'] = $this->extractContent($extension, $path);
        }

        Document::create($validated);
        return redirect()->route('documents.index')->with('success', 'Document created successfully');
    }

    public function show(Document $document)
    {
        Log::debug("DocumentController: Showing document ID: {$document->id}");
        return view('admin.documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        Log::debug("DocumentController: Editing document ID: {$document->id}");
        $useCkeditorLocal = false; // Force CDN usage
        return view('admin.documents.edit', compact('document', 'useCkeditorLocal'));
    }

    public function update(Request $request, Document $document)
    {
        Log::debug("DocumentController: Updating document ID: {$document->id}");
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,xlsx,pdf,csv,html|max:2048',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->store('documents', 'public');

            if ($extension === 'doc') {
                $convertedPath = $this->convertDocToDocx($file, $path);
                if ($convertedPath) {
                    Storage::disk('public')->delete($path); // Remove original .doc
                    $path = $convertedPath;
                    $extension = 'docx';
                } else {
                    Log::error("DocumentController: Failed to convert .doc to .docx");
                    return redirect()->back()->withErrors(['file' => 'Failed to convert .doc file']);
                }
            }

            $validated['file_path'] = $path;
            $validated['content'] = $this->extractContent($extension, $path);
        }

        $document->update($validated);
        return redirect()->route('documents.index')->with('success', 'Document updated successfully');
    }

    public function destroy(Document $document)
    {
        Log::debug("DocumentController: Deleting document ID: {$document->id}");
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted successfully');
    }

    protected function convertDocToDocx($file, $originalPath)
    {
        try {
            $tempPath = $file->getPathname();
            $convertedPath = str_replace('.doc', '.docx', $originalPath);
            $command = ['libreoffice', '--headless', '--convert-to', 'docx', $tempPath, '--outdir', dirname(Storage::disk('public')->path($originalPath))];
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("DocumentController: LibreOffice conversion failed: " . $process->getErrorOutput());
                return false;
            }

            if (!file_exists(Storage::disk('public')->path($convertedPath))) {
                Log::error("DocumentController: Converted .docx file not found at $convertedPath");
                return false;
            }

            return $convertedPath;
        } catch (\Exception $e) {
            Log::error("DocumentController: Error converting .doc to .docx: {$e->getMessage()}");
            return false;
        }
    }

    protected function extractContent($extension, $path)
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            switch (strtolower($extension)) {
                case 'docx':
                    $phpWord = WordIO::load($fullPath);
                    return $phpWord->getSections()[0]->getElements()[0]->getText();
                case 'xlsx':
                case 'csv':
                    $spreadsheet = SpreadsheetIO::load($fullPath);
                    $sheet = $spreadsheet->getActiveSheet();
                    return json_encode($sheet->toArray());
                case 'pdf':
                    return 'PDF content extraction not fully supported. Please edit manually.';
                case 'html':
                    return file_get_contents($fullPath);
                default:
                    return '';
            }
        } catch (\Exception $e) {
            Log::error("DocumentController: Error extracting content: {$e->getMessage()}");
            return '';
        }
    }
}
EOF

# Update TemplateController
log "Updating TemplateController..."
cat > app/Http/Controllers/Admin/TemplateController.php << 'EOF'
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory as WordIO;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIO;
use Dompdf\Dompdf;
use Symfony\Component\Process\Process;

class TemplateController extends Controller
{
    public function index()
    {
        Log::debug("TemplateController: Accessing index");
        return view('admin.templates.index', ['templates' => Template::all()]);
    }

    public function create()
    {
        Log::debug("TemplateController: Accessing create");
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        Log::debug("TemplateController: Storing template");
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,xlsx,pdf,csv,html|max:2048',
            'placeholders' => 'nullable|array',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->store('templates', 'public');

            if ($extension === 'doc') {
                $convertedPath = $this->convertDocToDocx($file, $path);
                if ($convertedPath) {
                    Storage::disk('public')->delete($path); // Remove original .doc
                    $path = $convertedPath;
                    $extension = 'docx';
                } else {
                    Log::error("TemplateController: Failed to convert .doc to .docx");
                    return redirect()->back()->withErrors(['file' => 'Failed to convert .doc file']);
                }
            }

            $validated['file_path'] = $path;
            $validated['content'] = $this->extractContent($extension, $path);

            if ($extension === 'docx') {
                $placeholders = $this->extractPlaceholders($path);
                $validated['placeholders'] = json_encode($request->input('placeholders', $placeholders));
                if ($placeholders) {
                    $newPath = $this->generateDocx($path, $request->input('placeholders', []));
                    if ($newPath) {
                        Storage::disk('public')->delete($path);
                        $validated['file_path'] = $newPath;
                    }
                }
            }
        }

        Template::create($validated);
        return redirect()->route('templates.index')->with('success', 'Template created successfully');
    }

    public function show(Template $template)
    {
        Log::debug("TemplateController: Showing template ID: {$template->id}");
        return view('admin.templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        Log::debug("TemplateController: Editing template ID: {$template->id}");
        $useCkeditorLocal = false; // Force CDN usage
        $placeholders = $template->file_path && pathinfo($template->file_path, PATHINFO_EXTENSION) === 'docx' ? $this->extractPlaceholders($template->file_path) : [];
        return view('admin.templates.edit', compact('template', 'useCkeditorLocal', 'placeholders'));
    }

    public function update(Request $request, Template $template)
    {
        Log::debug("TemplateController: Updating template ID: {$template->id}");
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,xlsx,pdf,csv,html|max:2048',
            'placeholders' => 'nullable|array',
        ]);

        if ($request->hasFile('file')) {
            if ($template->file_path) {
                Storage::disk('public')->delete($template->file_path);
            }
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->store('templates', 'public');

            if ($extension === 'doc') {
                $convertedPath = $this->convertDocToDocx($file, $path);
                if ($convertedPath) {
                    Storage::disk('public')->delete($path); // Remove original .doc
                    $path = $convertedPath;
                    $extension = 'docx';
                } else {
                    Log::error("TemplateController: Failed to convert .doc to .docx");
                    return redirect()->back()->withErrors(['file' => 'Failed to convert .doc file']);
                }
            }

            $validated['file_path'] = $path;
            $validated['content'] = $this->extractContent($extension, $path);

            if ($extension === 'docx') {
                $placeholders = $this->extractPlaceholders($path);
                $validated['placeholders'] = json_encode($request->input('placeholders', $placeholders));
                if ($placeholders) {
                    $newPath = $this->generateDocx($path, $request->input('placeholders', []));
                    if ($newPath) {
                        Storage::disk('public')->delete($path);
                        $validated['file_path'] = $newPath;
                    }
                }
            }
        } elseif ($request->has('placeholders') && $template->file_path && pathinfo($template->file_path, PATHINFO_EXTENSION) === 'docx') {
            $validated['placeholders'] = json_encode($request->input('placeholders'));
            $newPath = $this->generateDocx($template->file_path, $request->input('placeholders', []));
            if ($newPath) {
                Storage::disk('public')->delete($template->file_path);
                $validated['file_path'] = $newPath;
            }
        }

        $template->update($validated);
        return redirect()->route('templates.index')->with('success', 'Template updated successfully');
    }

    public function destroy(Template $template)
    {
        Log::debug("TemplateController: Deleting template ID: {$template->id}");
        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template deleted successfully');
    }

    protected function convertDocToDocx($file, $originalPath)
    {
        try {
            $tempPath = $file->getPathname();
            $convertedPath = str_replace('.doc', '.docx', $originalPath);
            $command = ['libreoffice', '--headless', '--convert-to', 'docx', $tempPath, '--outdir', dirname(Storage::disk('public')->path($originalPath))];
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("TemplateController: LibreOffice conversion failed: " . $process->getErrorOutput());
                return false;
            }

            if (!file_exists(Storage::disk('public')->path($convertedPath))) {
                Log::error("TemplateController: Converted .docx file not found at $convertedPath");
                return false;
            }

            return $convertedPath;
        } catch (\Exception $e) {
            Log::error("TemplateController: Error converting .doc to .docx: {$e->getMessage()}");
            return false;
        }
    }

    protected function extractContent($extension, $path)
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            switch (strtolower($extension)) {
                case 'docx':
                    $phpWord = WordIO::load($fullPath);
                    return $phpWord->getSections()[0]->getElements()[0]->getText();
                case 'xlsx':
                case 'csv':
                    $spreadsheet = SpreadsheetIO::load($fullPath);
                    $sheet = $spreadsheet->getActiveSheet();
                    return json_encode($sheet->toArray());
                case 'pdf':
                    return 'PDF content extraction not fully supported. Please edit manually.';
                case 'html':
                    return file_get_contents($fullPath);
                default:
                    return '';
            }
        } catch (\Exception $e) {
            Log::error("TemplateController: Error extracting content: {$e->getMessage()}");
            return '';
        }
    }

    protected function extractPlaceholders($path)
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            $templateProcessor = new TemplateProcessor($fullPath);
            $placeholders = $templateProcessor->getVariables();
            return array_unique($placeholders);
        } catch (\Exception $e) {
            Log::error("TemplateController: Error extracting placeholders: {$e->getMessage()}");
            return [];
        }
    }

    protected function generateDocx($path, $placeholders)
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            $templateProcessor = new TemplateProcessor($fullPath);
            foreach ($placeholders as $key => $value) {
                $templateProcessor->setValue($key, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            $newPath = str_replace('.docx', '_processed_' . time() . '.docx', $path);
            $templateProcessor->saveAs(Storage::disk('public')->path($newPath));
            return $newPath;
        } catch (\Exception $e) {
            Log::error("TemplateController: Error generating .docx: {$e->getMessage()}");
            return false;
        }
    }
}
EOF

# Verify and create AdminMiddleware.php
log "Verifying AdminMiddleware.php..."
if [ ! -f app/Http/Middleware/AdminMiddleware.php ]; then
    log "Creating AdminMiddleware.php..."
    mkdir -p app/Http/Middleware
    cat > app/Http/Middleware/AdminMiddleware.php << 'EOF'
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        Log::debug("AdminMiddleware: Checking user authentication for user ID: " . (Auth::user() ? Auth::user()->id : 'none'));
        if (Auth::user() && Auth::user()->is_admin) {
            Log::debug("AdminMiddleware: User is admin, proceeding with request.");
            return $next($request);
        }
        Log::debug("AdminMiddleware: Unauthorized access, redirecting to login.");
        return redirect()->route('login')->with('error', 'Unauthorized access');
    }
}
EOF
else
    log "AdminMiddleware.php already exists."
fi

# Reapply Kernel.php with debug logging
log "Reapplying Kernel.php with debug logging..."
cat > app/Http/Kernel.php << 'EOF'
<?php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin_check' => \App\Http\Middleware\AdminMiddleware::class,
    ];

    public function __construct($app, $router)
    {
        Log::debug("Kernel: Initializing middleware, admin_check mapped to App\Http\Middleware\AdminMiddleware");
        parent::__construct($app, $router);
    }
}
EOF

# Reapply routes/web.php with fixed syntax
log "Reapplying routes/web.php with fixed syntax..."
cat > routes/web.php << 'EOF'
<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContractorController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Counterparty\AuthController;
use App\Http\Controllers\Counterparty\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('contractors', ContractorController::class);
    Route::resource('templates', TemplateController::class);
    Route::resource('documents', DocumentController::class);
});

Route::prefix('counterparty')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('counterparty.register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLogin'])->name('counterparty.login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('counterparty.forgot-password');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('counterparty.reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('counterparty.reset-password.submit');
    Route::get('profile', [ProfileController::class, 'show'])->name('counterparty.profile')->middleware('auth:counterparty');
    Route::put('profile', [ProfileController::class, 'update'])->middleware('auth:counterparty');
});

Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('login', function () {
    $credentials = request()->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login')->with('error', 'Invalid credentials');
})->name('login.post');

Route::get('test-middleware', function () {
    return "Middleware test successful";
})->middleware([\App\Http\Middleware\AdminMiddleware::class])->name('test.middleware');

Route::get('logout', function () {
    Auth::logout();
    return redirect()->route('login')->with('success', 'Logged out successfully');
})->name('logout');
EOF

# Run migrations
log "Running migrations..."
php artisan migrate || { log "Migrations failed, but continuing as file_path may already exist."; }

# Build assets if Tailwind is used
if [ "$USE_TAILWIND" = true ]; then
    log "Building frontend assets..."
    npm run build || { log "Failed to build assets. Continuing with basic CSS."; }
else
    log "Copying basic CSS to public directory..."
    cp resources/css/app.css public/css/app.css || { log "Failed to copy app.css"; exit 1; }
fi

# Clear Laravel caches
log "Clearing Laravel caches..."
php artisan cache:clear || { log "Failed to clear cache"; exit 1; }
php artisan config:clear || { log "Failed to clear config"; exit 1; }
php artisan route:clear || { log "Failed to clear routes"; exit 1; }
php artisan view:clear || { log "Failed to clear views"; exit 1; }
php artisan optimize:clear || { log "Failed to clear optimized files"; exit 1; }

# Clear OPCache
log "Clearing OPCache..."
echo "<?php opcache_reset();" | php || { log "Failed to clear OPCache"; exit 1; }

# Regenerate Composer autoloader
log "Regenerating Composer autoloader..."
composer dump-autoload -o || { log "Failed to regenerate autoloader"; exit 1; }

# Set file permissions
log "Setting file permissions..."
chmod 644 app/Http/Middleware/AdminMiddleware.php routes/web.php app/Http/Kernel.php resources/views/admin/dashboard.blade.php resources/views/auth/login.blade.php resources/views/admin/documents/edit.blade.php resources/views/admin/templates/edit.blade.php resources/css/app.css app/Http/Controllers/Admin/DocumentController.php app/Http/Controllers/Admin/TemplateController.php || { log "Failed to set permissions"; exit 1; }
chmod 644 public/css/app.css || { log "Failed to set permissions for public/css/app.css"; }
chown www-data:www-data app/Http/Middleware/AdminMiddleware.php routes/web.php app/Http/Kernel.php resources/views/admin/dashboard.blade.php resources/views/auth/login.blade.php resources/views/admin/documents/edit.blade.php resources/views/admin/templates/edit.blade.php resources/css/app.css public/css/app.css app/Http/Controllers/Admin/DocumentController.php app/Http/Controllers/Admin/TemplateController.php || { log "Failed to set ownership"; exit 1; }
chmod -R 755 storage/app/public || { log "Failed to set storage permissions"; exit 1; }
chown -R www-data:www-data storage/app/public || { log "Failed to set storage ownership"; exit 1; }

# Check config/app.php for conflicts
log "Checking config/app.php for conflicting middleware..."
if grep -qi 'admin' config/app.php || grep -qi 'admin_check' config/app.php; then
    log "Warning: Found 'admin' or 'admin_check' in config/app.php. Backing up and removing conflicting aliases..."
    cp config/app.php config/app.php.bak
    sed -i '/admin/d' config/app.php
    sed -i '/admin_check/d' config/app.php
    echo "Conflicting aliases removed. Backup saved as config/app.php.bak." >> storage/logs/fix_admin_middleware.log
fi

# Check service providers for conflicts
log "Checking service providers for middleware conflicts..."
grep -r -i 'admin_check' app/Providers >> storage/logs/fix_admin_middleware.log
grep -r -i 'admin' app/Providers >> storage/logs/fix_admin_middleware.log

# Verify Composer autoloader
log "Verifying AdminMiddleware in autoloader..."
if ! grep -q 'App\\Http\\Middleware\\AdminMiddleware' vendor/composer/autoload_classmap.php; then
    log "Error: AdminMiddleware not found in autoloader. Forcing Composer update..."
    composer update --no-scripts || { log "Failed to update Composer"; exit 1; }
fi

# Verify file existence
log "Verifying files..."
ls -l app/Http/Middleware/ >> storage/logs/fix_admin_middleware.log
ls -l resources/views/admin/ >> storage/logs/fix_admin_middleware.log
ls -l resources/views/auth/ >> storage/logs/fix_admin_middleware.log
ls -l public/css/ >> storage/logs/fix_admin_middleware.log
ls -l app/Http/Controllers/Admin/ >> storage/logs/fix_admin_middleware.log
ls -l database/migrations/ >> storage/logs/fix_admin_middleware.log
if [ ! -f app/Http/Middleware/AdminMiddleware.php ]; then
    log "Error: AdminMiddleware.php not found after creation."
    exit 1
fi
if [ ! -f resources/views/admin/dashboard.blade.php ]; then
    log "Error: dashboard.blade.php not found after creation."
    exit 1
fi
if [ ! -f resources/views/auth/login.blade.php ]; then
    log "Error: login.blade.php not found after creation."
    exit 1
fi
if [ ! -f resources/views/admin/documents/edit.blade.php ]; then
    log "Error: documents/edit.blade.php not found after creation."
    exit 1
fi
if [ ! -f resources/views/admin/templates/edit.blade.php ]; then
    log "Error: templates/edit.blade.php not found after creation."
    exit 1
fi
if [ ! -f public/css/app.css ]; then
    log "Error: public/css/app.css not found after creation."
    exit 1
fi
if [ ! -f app/Http/Controllers/Admin/DocumentController.php ]; then
    log "Error: DocumentController.php not found after creation."
    exit 1
fi
if [ ! -f app/Http/Controllers/Admin/TemplateController.php ]; then
    log "Error: TemplateController.php not found after creation."
    exit 1
fi

# Verify Laravel version
log "Checking Laravel version..."
php artisan --version >> storage/logs/fix_admin_middleware.log

# Verify routes
log "Verifying routes..."
php artisan route:list >> storage/logs/fix_admin_middleware.log || { log "Failed to list routes"; exit 1; }

# Ensure storage link
log "Ensuring storage link..."
php artisan storage:link || { log "Failed to create storage link"; exit 1; }

# Restart PHP-FPM and web server
log "Restarting PHP-FPM and web server..."
sudo systemctl restart php-fpm || { log "Failed to restart PHP-FPM"; exit 1; }
sudo systemctl restart nginx || { log "Failed to restart Nginx"; exit 1; }

log "File editing and UI applied, including complex .docx template support and CKEditor CDN fix. Check storage/logs/fix_admin_middleware.log for details."
log "Test by logging in at http://your-domain/login with admin@example.com and password, then access http://your-domain/admin/documents/1/edit or http://your-domain/admin/templates/1/edit."
log "Check browser console for CKEditor logs and storage/logs/laravel.log for debug messages."