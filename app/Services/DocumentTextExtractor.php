<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentTextExtractor
{
    public function extract(string $path, ?string $originalName = null, ?string $mime = null): ?string
    {
        $extension = strtolower(pathinfo($originalName ?? $path, PATHINFO_EXTENSION));
        if ($extension === '' && $mime) {
            $extension = $this->extensionFromMime($mime) ?? '';
        }

        if (in_array($extension, ['txt', 'csv'], true)) {
            return $this->normalizeText(@file_get_contents($path));
        }

        if ($extension === 'pdf') {
            return $this->extractPdf($path);
        }

        if ($extension === 'docx') {
            return $this->extractDocx($path);
        }

        if ($extension === 'doc') {
            return $this->extractDoc($path);
        }

        if ($extension === 'pptx') {
            return $this->extractPptx($path);
        }

        if ($extension === 'ppt') {
            return $this->extractPpt($path);
        }

        if ($extension === 'xlsx') {
            return $this->extractXlsx($path);
        }

        if ($extension === 'xls') {
            return $this->extractXls($path);
        }

        if (preg_match('/\.(png|jpe?g|webp|bmp|tiff?)$/i', $path)) {
            return $this->extractImage($path);
        }

        return null;
    }

    protected function extractPdf(string $path): ?string
    {
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
                if (trim($text) !== '') {
                    return $this->normalizeText($text);
                }
            } catch (\Throwable $e) {
                Log::warning('PDF parser failed: ' . $e->getMessage(), ['path' => $path]);
            }
        }

        if ($this->commandExists('pdftotext')) {
            $output = $this->runShellCommand('pdftotext -layout ' . escapeshellarg($path) . ' -');
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        if ($this->commandExists('pdftoppm') && $this->commandExists('tesseract')) {
            $tmpPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'doc_extract_' . uniqid();
            $this->runShellCommand('pdftoppm -png ' . escapeshellarg($path) . ' ' . escapeshellarg($tmpPrefix));
            $text = '';
            foreach (glob($tmpPrefix . '-*.png') as $pageImage) {
                $pageOutput = $this->runShellCommand('tesseract ' . escapeshellarg($pageImage) . ' stdout -l eng');
                if ($pageOutput) {
                    $text .= $pageOutput . "\n";
                }
                @unlink($pageImage);
            }
            if (trim($text) !== '') {
                return $this->normalizeText($text);
            }
        }

        return null;
    }

    protected function extractDocx(string $path): ?string
    {
        if (class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            try {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
                $text = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . "\n";
                        }
                    }
                }
                if (trim($text) !== '') {
                    return $this->normalizeText($text);
                }
            } catch (\Throwable $e) {
                Log::warning('PhpWord failed to read DOCX: ' . $e->getMessage(), ['path' => $path]);
            }
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml) {
                return $this->normalizeText(strip_tags($xml));
            }
        }

        return null;
    }

    protected function extractDoc(string $path): ?string
    {
        if ($this->commandExists('antiword')) {
            $output = $this->runShellCommand('antiword ' . escapeshellarg($path));
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        if ($this->commandExists('catdoc')) {
            $output = $this->runShellCommand('catdoc ' . escapeshellarg($path));
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        return null;
    }

    protected function extractPptx(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $text = '';
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#ppt/slides/slide[0-9]+\.xml$#', $name)) {
                    $slide = $zip->getFromName($name);
                    $text .= strip_tags($slide) . "\n";
                }
            }
            $zip->close();
            if (trim($text) !== '') {
                return $this->normalizeText($text);
            }
        }

        return null;
    }

    protected function extractPpt(string $path): ?string
    {
        if ($this->commandExists('pptotext')) {
            $output = $this->runShellCommand('pptotext ' . escapeshellarg($path) . ' -');
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        return null;
    }

    protected function extractXlsx(string $path): ?string
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $text = '';
                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    $text .= $sheet->getTitle() . "\n";
                    foreach ($sheet->getRowIterator() as $row) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        foreach ($cellIterator as $cell) {
                            if ($cell !== null) {
                                $text .= $cell->getValue() . "\t";
                            }
                        }
                        $text .= "\n";
                    }
                    $text .= "\n";
                }
                if (trim($text) !== '') {
                    return $this->normalizeText($text);
                }
            } catch (\Throwable $e) {
                Log::warning('PhpSpreadsheet failed to read XLSX: ' . $e->getMessage(), ['path' => $path]);
            }
        }

        return $this->extractSpreadsheetByZip($path);
    }

    protected function extractXls(string $path): ?string
    {
        if ($this->commandExists('xls2csv')) {
            $output = $this->runShellCommand('xls2csv ' . escapeshellarg($path));
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        return null;
    }

    protected function extractSpreadsheetByZip(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $sharedStrings = [];
        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            $sharedStrings = $this->parseSharedStrings($xml);
        }

        $text = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#xl/worksheets/.*\.xml$#', $name)) {
                $sheet = $zip->getFromName($name);
                if ($sheet) {
                    $text .= $this->extractTextFromXml($sheet, $sharedStrings) . "\n";
                }
            }
        }

        $zip->close();

        return trim($text) !== '' ? $this->normalizeText($text) : null;
    }

    protected function extractImage(string $path): ?string
    {
        if ($this->commandExists('tesseract')) {
            $output = $this->runShellCommand('tesseract ' . escapeshellarg($path) . ' stdout -l eng');
            if ($output) {
                return $this->normalizeText($output);
            }
        }

        return null;
    }

    protected function normalizeText(?string $text): ?string
    {
        if (! is_string($text)) {
            return null;
        }

        $text = preg_replace('/\x0D\x0A|\x0D|\x0A/', "\n", $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    protected function parseSharedStrings(string $xml): array
    {
        $strings = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if ($dom->loadXML($xml)) {
            foreach ($dom->getElementsByTagName('si') as $si) {
                $strings[] = trim($si->textContent);
            }
        }
        return $strings;
    }

    protected function extractTextFromXml(string $xml, array $sharedStrings = []): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (! $dom->loadXML($xml)) {
            return '';
        }

        $text = '';
        foreach ($dom->getElementsByTagName('c') as $cell) {
            $valueNodes = $cell->getElementsByTagName('v');
            if ($valueNodes->length === 0) {
                continue;
            }
            $value = $valueNodes->item(0)->textContent;
            if ($cell->hasAttribute('t') && $cell->getAttribute('t') === 's') {
                $index = (int) $value;
                $text .= $sharedStrings[$index] ?? '';
            } else {
                $text .= $value;
            }
            $text .= "\t";
        }

        return trim($text);
    }

    protected function commandExists(string $command): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $which = @shell_exec('where ' . escapeshellarg($command) . ' 2>nul || which ' . escapeshellarg($command) . ' 2>/dev/null');
        return trim((string) $which) !== '';
    }

    protected function runShellCommand(string $command): ?string
    {
        try {
            $output = @shell_exec($command . ' 2>/dev/null');
            return trim((string) $output) ?: null;
        } catch (\Throwable $e) {
            Log::warning('Shell command failed: ' . $e->getMessage(), ['command' => $command]);
            return null;
        }
    }

    protected function extensionFromMime(string $mime): ?string
    {
        $map = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
        ];

        return $map[$mime] ?? null;
    }
}
