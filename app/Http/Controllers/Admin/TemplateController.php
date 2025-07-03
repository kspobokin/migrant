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
