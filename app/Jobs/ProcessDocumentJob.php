<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Document $document)
    {
        $this->onQueue('documents');
    }

    public function handle(): void
    {
        $doc = $this->document->fresh();
        $path = Storage::disk('public')->path($doc->filename);
        if (! file_exists($path)) {
            $doc->status = 'failed';
            $doc->save();
            Log::warning('ProcessDocumentJob: file not found', ['path' => $path]);
            return;
        }

        $extractor = new DocumentTextExtractor();
        $extracted = null;

        try {
            $extracted = $extractor->extract($path, $doc->original_name, $doc->mime);

            if (! empty($extracted)) {
                $doc->extracted_text = Str::limit($extracted, 200000);
                $doc->status = 'processed';
                $doc->save();
                Log::info('Document processed', ['document_id' => $doc->id]);
                return;
            }

            $doc->status = 'uploaded';
            $doc->save();
            Log::info('Document processing completed with no extracted text', ['document_id' => $doc->id]);
        } catch (\Throwable $e) {
            $doc->status = 'failed';
            $doc->save();
            Log::error('ProcessDocumentJob failed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
