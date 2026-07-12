<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\AIServiceInterface;
use App\Services\AIUsageLimiterService;
use App\Services\DocumentTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function upload(Request $request, DocumentTextExtractor $extractor)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,docx,doc,txt,pptx,ppt,xlsx,xls,csv,png,jpg,jpeg,webp,mp3,wav,m4a',
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $original = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $size = $file->getSize();
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('documents/' . $user->id, $filename, 'public');

        $extractedText = $extractor->extract(Storage::disk('public')->path($path), $original, $mime);
        $status = $extractedText ? 'processed' : 'uploaded';

        $doc = Document::create([
            'user_id' => $user->id,
            'original_name' => $original,
            'filename' => $path,
            'mime' => $mime,
            'size' => $size,
            'extracted_text' => $extractedText ? Str::limit($extractedText, 200000) : null,
            'status' => $status,
        ]);

        $user->logActivity('Uploaded document', $original, 'Uploaded a document for study resources', 'document');

        return response()->json([
            'success' => true,
            'document' => $doc,
            'message' => $extractedText ? 'File uploaded and text extracted successfully.' : 'File uploaded. Text extraction is pending or the file could not be immediately parsed.',
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $docs = Document::where('user_id', $user->id)->latest('created_at')->get();
        return response()->json(['success' => true, 'documents' => $docs]);
    }

    public function show(Document $document)
    {
        $user = Auth::user();
        if ($document->user_id !== $user->id) {
            return response()->json(['success' => false], 404);
        }

        return response()->json(['success' => true, 'document' => $document]);
    }

    public function download(Document $document)
    {
        $user = Auth::user();
        if ($document->user_id !== $user->id) {
            abort(404);
        }

        $path = Storage::disk('public')->path($document->filename);
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $document->original_name);
    }

    public function process(Document $document, AIServiceInterface $aiService, AIUsageLimiterService $usageLimiter, DocumentTextExtractor $extractor)
    {
        $user = Auth::user();
        if ($document->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
        }

        try {
            if (empty($document->extracted_text)) {
                $path = Storage::disk('public')->path($document->filename);
                $extracted = $extractor->extract($path, $document->original_name, $document->mime);
                if ($extracted) {
                    $document->extracted_text = Str::limit($extracted, 200000);
                    $document->status = 'processed';
                    $document->save();
                } else {
                    // Extraction failed for plain-text methods. Keep the document as uploaded
                    // and allow the later vision-model fallback (if available) to attempt
                    // reading the file. Do not return early here.
                    $document->status = 'uploaded';
                    $document->save();
                }
            }

            if (empty($document->summary)) {
                $usageLimiter->ensureCanUse($user);

                $attachmentPayload = null;
                if (! is_string($document->extracted_text) || trim($document->extracted_text) === '') {
                    if ($this->canUseVisionFallback($document)) {
                        $attachmentPayload = [
                            'type' => 'image',
                            'path' => Storage::disk('public')->path($document->filename),
                            'mime' => $document->mime,
                            'name' => $document->original_name,
                        ];
                    } else {
                        $isImage = str_starts_with($document->mime ?? '', 'image/') || preg_match('/\.(png|jpe?g|webp|bmp|tiff?)$/i', $document->filename);
                        $message = $isImage
                            ? 'The uploaded image contains no readable text. To summarize images enable a vision-capable model (set GROQ_VISION_MODEL) or ensure server-side OCR (e.g. Tesseract) is installed.'
                            : 'The uploaded document contains no readable text to summarize. If this is a scanned document, install OCR tools (e.g. Tesseract) or enable a vision-capable model.';

                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 422);
                    }
                }

                $messages = [['role' => 'system', 'content' => $this->buildSystemPrompt()]];
                if ($attachmentPayload) {
                    $messages[] = ['role' => 'user', 'content' => $this->buildAttachmentSummaryPrompt($document->original_name)];
                } else {
                    $extractedText = $document->extracted_text;
                    $maxChars = config('services.groq.max_document_chars', 15000);
                    if (strlen($extractedText) > $maxChars) {
                        $extractedText = Str::limit($extractedText, $maxChars, "\n\n... [document content truncated for summary]");
                    }
                    $messages[] = ['role' => 'user', 'content' => $this->buildSummaryPrompt($document->original_name, $extractedText)];
                }

                $model = $attachmentPayload ? config('services.groq.vision_model', config('services.groq.model')) : config('services.groq.model', 'llama-3.3-70b-versatile');
                $summary = $aiService->chat($messages, $model, 'summarize', $attachmentPayload);

                $document->summary = trim($summary);
                $document->status = 'processed';
                $document->save();
                $user->logActivity('Document summarized', $document->original_name, 'Generated a summary from the uploaded document', 'summary');

                $usageLimiter->incrementUsage($user);
            }

            return response()->json(['success' => true, 'document' => $document]);
        } catch (\RuntimeException $e) {
            Log::error('Document summarization failed: ' . $e->getMessage(), ['document_id' => $document->id]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('Document summarization failed: ' . $e->getMessage(), ['document_id' => $document->id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Unable to summarize the document at this time. Please try again later.'], 500);
        }
    }

    protected function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are SHEELEARN, an intelligent AI assistant for learning. Analyze uploaded documents carefully and summarize them clearly. Use only the extracted document content or the attached file as the source of truth. Do not rely on file names, titles, or metadata. Provide concise, structured summaries with key points.
PROMPT;
    }

    protected function buildSummaryPrompt(string $filename, string $content): string
    {
        return "Here is the extracted text from an uploaded document. Summarize the information accurately and concisely, highlighting the most important concepts, conclusions, and actionable points. Use bullet points or short sections if appropriate, and avoid guessing based on the file name or metadata.\n\n" . $content;
    }

    protected function buildAttachmentSummaryPrompt(string $filename): string
    {
        return "The document could not be read as text, but the file is attached. Analyze the attached file directly and summarize the information accurately and concisely. Focus on the actual file content, not the file name or metadata. Use bullet points or short sections if appropriate.";
    }

    protected function canUseVisionFallback(Document $document): bool
    {
        $visionModel = config('services.groq.vision_model');
        return ! empty($visionModel) && is_string($visionModel) && str_starts_with($document->mime ?? '', 'image/');
    }
}
