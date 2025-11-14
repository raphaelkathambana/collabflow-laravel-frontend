<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;

class DocumentParser
{
    /**
     * Extract text content from uploaded file
     *
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function extractContent(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            return match($extension) {
                'txt', 'md', 'markdown' => $this->extractText($file),
                'pdf' => $this->extractPdf($file),
                'doc', 'docx' => $this->extractDocx($file),
                default => throw new \Exception("Unsupported file type: {$extension}")
            };
        } catch (\Exception $e) {
            Log::error('Document parsing failed', [
                'file' => $file->getClientOriginalName(),
                'extension' => $extension,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Failed to parse {$file->getClientOriginalName()}: {$e->getMessage()}");
        }
    }

    /**
     * Extract text from plain text files
     *
     * @param UploadedFile $file
     * @return string
     */
    private function extractText(UploadedFile $file): string
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new \Exception('Failed to read file contents');
        }

        return trim($content);
    }

    /**
     * Extract text from PDF files using smalot/pdfparser
     *
     * @param UploadedFile $file
     * @return string
     */
    private function extractPdf(UploadedFile $file): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getRealPath());
            $text = $pdf->getText();

            // Clean up extracted text (PDFs often have extra whitespace)
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);

            if (empty($text)) {
                throw new \Exception('No text content found in PDF');
            }

            return $text;

        } catch (\Exception $e) {
            throw new \Exception("PDF parsing error: {$e->getMessage()}");
        }
    }

    /**
     * Extract text from DOCX files using phpoffice/phpword
     *
     * @param UploadedFile $file
     * @return string
     */
    private function extractDocx(UploadedFile $file): string
    {
        try {
            $phpWord = IOFactory::load($file->getRealPath());
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                $elements = $section->getElements();

                foreach ($elements as $element) {
                    // Handle text elements
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }

                    // Handle text runs (formatting)
                    if (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $text .= $childElement->getText() . ' ';
                            }
                        }
                    }
                }
            }

            $text = trim($text);

            if (empty($text)) {
                throw new \Exception('No text content found in DOCX');
            }

            return $text;

        } catch (\Exception $e) {
            throw new \Exception("DOCX parsing error: {$e->getMessage()}");
        }
    }

    /**
     * Validate file before parsing
     *
     * @param UploadedFile $file
     * @param int $maxSize Maximum file size in bytes (default 10MB)
     * @return bool
     * @throws \Exception
     */
    public function validateFile(UploadedFile $file, int $maxSize = 10485760): bool
    {
        // Check file exists and is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Check file size
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 2);
            throw new \Exception("File exceeds maximum size of {$maxSizeMB}MB");
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['txt', 'md', 'markdown', 'pdf', 'doc', 'docx'];

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Unsupported file type: {$extension}. Allowed: " . implode(', ', $allowedExtensions));
        }

        return true;
    }

    /**
     * Get supported file extensions
     *
     * @return array
     */
    public function getSupportedExtensions(): array
    {
        return ['txt', 'md', 'markdown', 'pdf', 'doc', 'docx'];
    }

    /**
     * Get supported MIME types
     *
     * @return array
     */
    public function getSupportedMimeTypes(): array
    {
        return [
            'text/plain',
            'text/markdown',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }
}
