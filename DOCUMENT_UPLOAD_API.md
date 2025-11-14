# Document Upload API Documentation

## Overview

The Python service now supports document upload to ChromaDB for knowledge-based task generation. When documents are uploaded for a project, they are automatically used as context during task generation to create more relevant and accurate tasks.

## Endpoint

**POST** `/api/context/documents/upload`

## Request Format

```json
{
  "project_id": "string (required)",
  "documents": [
    {
      "content": "string (required) - The full text content of the document",
      "source": "string (required) - File name or identifier (e.g., 'requirements.pdf')",
      "type": "string (required) - Document type (pdf, markdown, txt, docx, etc.)",
      "created_at": "string (required) - ISO 8601 timestamp"
    }
  ]
}
```

## Response Format

**Success (200)**:
```json
{
  "status": "success",
  "message": "Successfully ingested 2 document(s)",
  "project_id": "proj-123",
  "document_count": 2
}
```

**Error (500)**:
```json
{
  "error": "Validation error",
  "status_code": 500,
  "detail": "Failed to upload documents: <error message>"
}
```

## Laravel Integration

### Step 1: Extract Document Content

When a user uploads a file in Laravel, you need to extract the text content:

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class DocumentParser
{
    /**
     * Extract text content from uploaded file
     */
    public function extractContent(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        return match($extension) {
            'txt' => $this->extractText($file),
            'pdf' => $this->extractPdf($file),
            'md', 'markdown' => $this->extractText($file),
            'docx' => $this->extractDocx($file),
            default => throw new \Exception("Unsupported file type: {$extension}")
        };
    }

    private function extractText(UploadedFile $file): string
    {
        return file_get_contents($file->getRealPath());
    }

    private function extractPdf(UploadedFile $file): string
    {
        // Option 1: Use smalot/pdfparser
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($file->getRealPath());
        return $pdf->getText();

        // Option 2: Use spatie/pdf-to-text
        // return (new \Spatie\PdfToText\Pdf())
        //     ->setPdf($file->getRealPath())
        //     ->text();
    }

    private function extractDocx(UploadedFile $file): string
    {
        // Use phpoffice/phpword
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }
}
```

**Required Composer Packages**:
```bash
# For PDF parsing (choose one)
composer require smalot/pdfparser
# OR
composer require spatie/pdf-to-text

# For DOCX parsing
composer require phpoffice/phpword
```

### Step 2: Upload to Python Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class AIEngineService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_engine.url', 'http://localhost:8001');
    }

    /**
     * Upload documents to AI Engine knowledge base
     *
     * @param string $projectId
     * @param array $files Array of UploadedFile objects
     * @return array Response from AI Engine
     */
    public function uploadDocuments(string $projectId, array $files): array
    {
        $parser = new DocumentParser();
        $documents = [];

        foreach ($files as $file) {
            try {
                $content = $parser->extractContent($file);

                $documents[] = [
                    'content' => $content,
                    'source' => $file->getClientOriginalName(),
                    'type' => $file->getClientOriginalExtension(),
                    'created_at' => now()->toIso8601String()
                ];
            } catch (\Exception $e) {
                \Log::error("Failed to parse document: {$file->getClientOriginalName()}", [
                    'error' => $e->getMessage()
                ]);
                // Continue with other files
            }
        }

        if (empty($documents)) {
            throw new \Exception('No documents could be parsed successfully');
        }

        $response = Http::timeout(30)
            ->post("{$this->baseUrl}/api/context/documents/upload", [
                'project_id' => $projectId,
                'documents' => $documents
            ]);

        if ($response->failed()) {
            throw new \Exception("Document upload failed: {$response->body()}");
        }

        return $response->json();
    }
}
```

### Step 3: Usage in Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\AIEngineService;
use Illuminate\Http\Request;

class ProjectDocumentController extends Controller
{
    public function upload(Request $request, string $projectId)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,txt,md,docx|max:10240' // 10MB max
        ]);

        try {
            $aiEngine = new AIEngineService();
            $result = $aiEngine->uploadDocuments(
                $projectId,
                $request->file('documents')
            );

            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### Step 4: Frontend Upload Form

```blade
<!-- resources/views/projects/upload-documents.blade.php -->
<form action="{{ route('projects.documents.upload', $project) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label>Upload Project Documents</label>
        <input type="file"
               name="documents[]"
               multiple
               accept=".pdf,.txt,.md,.docx"
               class="form-control">
        <small class="form-text text-muted">
            Supported formats: PDF, TXT, Markdown, DOCX (max 10MB each)
        </small>
    </div>

    <button type="submit" class="btn btn-primary">Upload Documents</button>
</form>
```

## How It Works

1. **Upload**: Documents are uploaded via `/api/context/documents/upload`
2. **Processing**:
   - Text is extracted and chunked (512 words per chunk with 50-word overlap)
   - Chunks are embedded using `all-MiniLM-L6-v2` model
   - Embeddings are stored in ChromaDB collection `project_{project_id}`
3. **Task Generation**:
   - When generating tasks via `/api/tasks/generate`, the service automatically queries ChromaDB
   - Relevant document chunks are retrieved and used as additional context
   - Tasks are generated based on both user input AND document content

## Benefits

- **More accurate tasks**: Tasks reflect actual project requirements from documents
- **Better context**: AI understands specific technical details, architectures, and constraints
- **Consistency**: Tasks align with documented standards and specifications
- **RAG-powered**: Uses Retrieval-Augmented Generation for improved accuracy

## Example: Full Workflow

```php
// 1. User creates project
$project = Project::create([
    'name' => 'Authentication System',
    'description' => 'Build secure auth system'
]);

// 2. User uploads requirements document
$aiEngine = new AIEngineService();
$aiEngine->uploadDocuments($project->id, [
    $request->file('requirements_doc')
]);

// 3. Generate tasks (documents are automatically used)
$tasks = $aiEngine->generateTasks(
    projectId: $project->id,
    context: [...],
    contextAnalysis: [...]
);

// Tasks will now reference specific details from the requirements document!
```

## Debugging

To verify documents are being used, check the Python service logs:

```
2025-11-12 10:00:00,123 - INFO - Ingested 2 documents for project test-proj-123
2025-11-12 10:01:00,456 - INFO - ChromaDB query found 5 relevant chunks for project test-proj-123
```

If you see:
```
2025-11-12 10:01:00,456 - WARNING - ChromaDB query failed for project test-proj-123: Collection [...] does not exist
```

This means documents were not uploaded before task generation.

## Testing

Use the provided test script to verify the integration:

```bash
cd python-service
./venv/Scripts/python.exe test_document_upload.py
```

## Notes

- ChromaDB must be running (`docker-compose up chromadb`)
- Documents persist across task generations for the same project
- Each project has its own isolated collection
- Large documents are automatically chunked for optimal retrieval
- Semantic search ensures most relevant content is used

## Troubleshooting

### "ChromaDB client not initialized"
- Ensure ChromaDB Docker container is running
- Check `CHROMA_HOST` and `CHROMA_PORT` in Python service `.env`

### "Collection does not exist"
- Upload documents before generating tasks
- Verify `project_id` matches between upload and generation

### "Failed to parse document"
- Check file format is supported
- Ensure file is not corrupted
- Verify parser library is installed (pdfparser, phpword)

## API Reference

See FastAPI docs at `http://localhost:8001/docs` for interactive API documentation.
