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
