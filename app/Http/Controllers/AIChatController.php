<?php

namespace App\Http\Controllers;

use App\Http\Requests\AIChatRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AIServiceInterface;
use App\Services\AIUsageLimiterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AIChatController extends Controller
{
    public function index(AIUsageLimiterService $usageLimiter): View
    {
        $user = Auth::user();

        $conversations = $user->chatConversations()
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $activeConversation = null;
        $conversationId = request()->query('conversation_id');

        if ($conversationId) {
            $activeConversation = $conversations->firstWhere('id', $conversationId);
        }

        if (! $activeConversation) {
            $activeConversation = $conversations->first();
        }

        $activeMessages = collect();
        if ($activeConversation) {
            $activeMessages = $activeConversation->messages()->get();
        }

        $usage = $usageLimiter->getUsageSummary($user);

        if (request()->wantsJson() || request()->query('usage')) {
            return response()->json(['usage' => $usage]);
        }

        return view('ai-chat', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'activeMessages' => $activeMessages,
            'usage' => $usage,
        ]);
    }

    public function conversation(ChatConversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if ($conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Conversation not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
            ],
            'messages' => $conversation->messages()->get()->map(fn (ChatMessage $message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
            ]),
        ]);
    }

    public function deleteConversation(ChatConversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if ($conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Conversation not found.'], 404);
        }

        DB::transaction(function () use ($conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted.',
        ]);
    }

    public function clearHistory(): JsonResponse
    {
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            ChatMessage::where('user_id', $user->id)->delete();
            ChatConversation::where('user_id', $user->id)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
        ]);
    }

    public function clearAttachment(): JsonResponse
    {
        $user = Auth::user();
        // Find any unprocessed uploaded document for this user and remove it as active context
        $doc = \App\Models\Document::where('user_id', $user->id)->latest('created_at')->first();
        if ($doc) {
            // Do not delete the file from disk; just mark it inactive/removed to preserve history
            $doc->status = 'removed';
            $doc->save();
        }

        return response()->json(['success' => true]);
    }

    public function send(AIChatRequest $request, AIServiceInterface $aiService, AIUsageLimiterService $usageLimiter): JsonResponse
    {
        try {
            $user = Auth::user();
            $mode = $request->validated('mode');
            $messageText = $request->validated('message');
            $attachedFile = $request->file('file');
            $document = null;
            $extractedText = null;
            $activeDocument = null;

            $conversation = null;
            $conversationId = $request->validated('conversation_id');
            if ($conversationId) {
                $conversation = ChatConversation::where('user_id', $user->id)->find($conversationId);
            }

            if (! $conversation) {
                $conversation = ChatConversation::create([
                    'user_id' => $user->id,
                    'title' => $this->generateConversationTitle($messageText),
                ]);
            }

            if (! $attachedFile) {
                $activeDocument = \App\Models\Document::where('user_id', $user->id)
                    ->where('status', '!=', 'removed')
                    ->latest('created_at')
                    ->first();

                if ($activeDocument) {
                    $document = $activeDocument;
                    $extractedText = $activeDocument->extracted_text;
                }
            }

            if ($attachedFile || $activeDocument) {
                $fileReferenceName = $attachedFile?->getClientOriginalName() ?? $activeDocument->original_name;
                $messageText = $this->normalizeAttachmentPrompt($messageText, $fileReferenceName);
            }

            $historyMessages = $conversation->messages()
                ->orderBy('id')
                ->get()
                ->map(fn (ChatMessage $message) => [
                    'role' => $message->role,
                    'content' => $message->content,
                ])
                ->toArray();

            $personaPrompt = $this->buildPrompt($mode, $messageText);

            if ($attachedFile) {
                try {
                    $original = $attachedFile->getClientOriginalName();
                    $mime = $attachedFile->getClientMimeType();
                    $filename = Str::random(40) . '.' . $attachedFile->getClientOriginalExtension();
                    $path = $attachedFile->storeAs('documents/' . $user->id, $filename, 'public');

                    $document = \App\Models\Document::create([
                        'user_id' => $user->id,
                        'original_name' => $original,
                        'filename' => $path,
                        'mime' => $mime,
                        'size' => $attachedFile->getSize(),
                        'status' => 'uploaded',
                    ]);

                    $localPath = Storage::disk('public')->path($path);
                    $extractedText = $this->extractTextFromFile($localPath, $original, $mime);

                    if (! empty($extractedText)) {
                        $document->extracted_text = Str::limit($extractedText, 200000);
                        $document->status = 'processed';
                        $document->save();
                    } else {
                        Log::info('File uploaded but no text extracted yet, binary content will be sent to model if needed.', ['user_id' => $user->id, 'file' => $path, 'mime' => $mime]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to store or process uploaded file: ' . $e->getMessage(), ['exception' => $e]);
                }
            }

            $fileContextSystemPrompt = null;
            if (! empty($document)) {
                $fileContextSystemPrompt = 'A file is attached or active for this request. Use the file content as the primary source of truth and answer based on it. If text was extracted from the file, prioritize that extracted text. Do not invent facts unrelated to the uploaded file.';
            }

            if (! empty($extractedText)) {
                $maxChars = config('services.groq.max_document_chars', 15000);
                if (strlen($extractedText) > $maxChars) {
                    $extractedText = Str::limit($extractedText, $maxChars, "\n\n... [document truncated for length]");
                    Log::info('Extracted document trimmed before sending to Groq', ['user_id' => $user->id, 'document_id' => $document->id ?? null, 'original_length' => strlen($document->extracted_text ?? ''), 'trimmed_to' => $maxChars]);
                }

                $personaPrompt = "Attached file contents:\n\n" . $extractedText . "\n\n" . $personaPrompt;
                Log::debug('Passing extracted document text to Groq request', ['user_id' => $user->id, 'document_id' => $document->id ?? null, 'extracted_length' => strlen($extractedText)]);
            } elseif (! empty($document) && empty($attachedFile) && empty($extractedText)) {
                Log::debug('No extracted text available; binary file may be sent to the model if possible.', ['user_id' => $user->id, 'document_id' => $document->id ?? null, 'mime' => $document->mime, 'name' => $document->original_name]);
            }

            if (! empty($attachedFile)) {
                $personaPrompt = "Read the attached document and answer exclusively using its content. " .
                    "Ignore earlier conversation history. " .
                    $personaPrompt;
            }

            $defaultModel = config('services.groq.model', 'llama-3.3-70b-versatile');
            $visionModel = config('services.groq.vision_model');
            $modelToUse = $defaultModel;
            if (! empty($document) && empty($extractedText) && $visionModel && str_starts_with($document->mime, 'image/')) {
                $modelToUse = $visionModel;
            }

            $attachmentPayload = null;
            if (! empty($document) && empty($extractedText)) {
                $filePath = Storage::disk('public')->path($document->filename ?? '');
                $isImage = str_starts_with($document->mime, 'image/');

                if ($isImage) {
                    if (empty($visionModel)) {
                        Log::warning('Image attachment uploaded without a vision-capable model; continuing without image binary.', ['user_id' => $user->id, 'document_id' => $document->id ?? null, 'mime' => $document->mime, 'name' => $document->original_name]);
                        $fileContextSystemPrompt = 'An image file is attached, but the configured model cannot process images directly. Answer based on the text prompt and any extracted image text if available.';
                    } else {
                        $attachmentPayload = [
                            'type' => 'image',
                            'path' => $filePath,
                            'mime' => $document->mime,
                            'name' => $document->original_name,
                        ];
                    }
                } else {
                    $attachmentPayload = [
                        'type' => 'file',
                        'path' => $filePath,
                        'mime' => $document->mime,
                        'name' => $document->original_name,
                    ];
                }
            } elseif (! empty($document) && ! empty($extractedText)) {
                Log::debug('Using extracted document text instead of attaching file binary to Groq request.', [
                    'user_id' => $user->id,
                    'document_id' => $document->id ?? null,
                    'mime' => $document->mime,
                    'name' => $document->original_name,
                    'extracted_text_length' => strlen($extractedText),
                ]);
            }

            $messages = array_merge([
                ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ], $historyMessages, $fileContextSystemPrompt ? [['role' => 'system', 'content' => $fileContextSystemPrompt]] : [], [
                ['role' => 'user', 'content' => $personaPrompt],
            ]);

            $assistantResponse = $aiService->chat($messages, $modelToUse, $mode, $attachmentPayload);
            $usageLimiter->incrementUsage($user);

            $conversation->messages()->create([
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $personaPrompt,
                'mode' => $mode,
            ]);

            $conversation->messages()->create([
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $assistantResponse,
                'mode' => $mode,
            ]);

            $conversation->touch();
            $user->logActivity('AI chat interaction', null, sprintf('Mode: %s · Message length: %d', $mode, mb_strlen($messageText)), 'chat');

            return response()->json([
                'success' => true,
                'reply' => $assistantResponse,
                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('AI chat failed: ' . $exception->getMessage(), ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'AI service is currently unavailable. Please try again later.',
            ], 500);
        }
    }

    protected function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are SHEELEARN, an intelligent AI learning assistant. Your role is to help students understand academic material, answer questions clearly, explain difficult concepts, solve math problems with step-by-step reasoning, summarize notes and documents, generate quizzes and flashcards, improve essays and grammar, assist with research, and provide personalized study recommendations. Stay encouraging, precise, and focused on learning outcomes.
PROMPT;
    }

    protected function generateConversationTitle(string $prompt): string
    {
        $title = trim(preg_replace('/\s+/', ' ', strip_tags($prompt)));
        $title = preg_replace('/^(explain|summarize|generate|create|solve|improve|research|help|provide|answer)\s+/i', '', $title);
        $title = preg_replace('/[\r\n]+/', ' ', $title);
        $title = trim($title, " .?;!\-\n");

        if ($title === '') {
            return 'New Study Session';
        }

        if (strlen($title) > 50) {
            $title = substr($title, 0, 50);
            $title = preg_replace('/\s+\S+$/', '', $title);
        }

        return ucfirst($title);
    }

    protected function buildPrompt(string $mode, string $input): string
    {
        return $input;
    }

    protected function normalizeAttachmentPrompt(string $message, string $fileReferenceName): string
    {
        $normalized = trim($message);

        if ($normalized === '') {
            return 'Please analyze the attached file.';
        }

        $basename = basename($fileReferenceName);
        $nameWithoutExtension = pathinfo($basename, PATHINFO_FILENAME);

        $patterns = [
            '/\b' . preg_quote($basename, '/') . '\b/i',
            '/\b' . preg_quote($nameWithoutExtension, '/') . '\b/i',
        ];

        $normalized = preg_replace($patterns, 'the attached file', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        if ($normalized === '') {
            return 'Please analyze the attached file.';
        }

        return $normalized;
    }

    /**
     * Attempt to extract readable text from a variety of uploaded file types.
     * Returns null if no text could be extracted.
     */
    protected function extractTextFromFile(string $localPath, string $originalName, string $mime = null): ?string
    {
        try {
            $lower = strtolower($originalName);

            if (in_array($mime, ['text/plain', 'text/csv']) || preg_match('/\.(txt|csv)$/i', $lower)) {
                return file_get_contents($localPath);
            }

            if (preg_match('/\.pdf$/i', $lower)) {
                if (class_exists(\Smalot\PdfParser\Parser::class)) {
                    try {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf = $parser->parseFile($localPath);
                        $text = $pdf->getText();
                        if (trim($text) !== '') {
                            return trim(preg_replace('/\s+/', ' ', $text));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('PDF parser failed: ' . $e->getMessage(), ['path' => $localPath]);
                    }
                }

                if (function_exists('shell_exec')) {
                    $cmd = 'pdftotext -layout ' . escapeshellarg($localPath) . ' -';
                    $output = @shell_exec($cmd);
                    if ($output && trim($output) !== '') {
                        return trim(preg_replace('/\s+/', ' ', $output));
                    }

                    // Fallback OCR for scanned PDFs using pdftoppm + tesseract if available
                    $pdftoppm = @shell_exec('where pdftoppm 2>nul || which pdftoppm 2>/dev/null');
                    $tesseract = @shell_exec('where tesseract 2>nul || which tesseract 2>/dev/null');
                    if ($pdftoppm && trim($pdftoppm) !== '' && $tesseract && trim($tesseract) !== '') {
                        $tmpPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdfocr_' . uniqid();
                        $cmd = escapeshellarg(trim($pdftoppm)) . ' -png ' . escapeshellarg($localPath) . ' ' . escapeshellarg($tmpPrefix);
                        @shell_exec($cmd);
                        $ocrText = '';
                        foreach (glob($tmpPrefix . '-*.png') as $pageImage) {
                            $pageOutput = @shell_exec(escapeshellarg(trim($tesseract)) . ' ' . escapeshellarg($pageImage) . ' stdout -l eng');
                            if ($pageOutput && trim($pageOutput) !== '') {
                                $ocrText .= $pageOutput . "\n";
                            }
                            @unlink($pageImage);
                        }
                        if (trim($ocrText) !== '') {
                            return trim(preg_replace('/\s+/', ' ', $ocrText));
                        }
                    }
                }
            }

            if (preg_match('/\.docx$/i', $lower)) {
                if (class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
                    try {
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load($localPath);
                        $text = '';
                        foreach ($phpWord->getSections() as $section) {
                            foreach ($section->getElements() as $element) {
                                if (method_exists($element, 'getText')) {
                                    $text .= $element->getText() . "\n";
                                }
                            }
                        }
                        if (trim($text) !== '') {
                            return trim(preg_replace('/\s+/', ' ', $text));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('PhpWord failed to read DOCX: ' . $e->getMessage(), ['path' => $localPath]);
                    }
                }

                $zip = new \ZipArchive();
                if ($zip->open($localPath) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    if ($xml) {
                        $text = strip_tags($xml);
                        return trim(preg_replace('/\s+/', ' ', $text));
                    }
                }
            }

            if (preg_match('/\.pptx$/i', $lower)) {
                $zip = new \ZipArchive();
                if ($zip->open($localPath) === true) {
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
                        return trim(preg_replace('/\s+/', ' ', $text));
                    }
                }
            }

            if (preg_match('/\.xlsx$/i', $lower) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($localPath);
                    $text = '';
                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $text .= $sheet->getTitle() . "\n";
                        foreach ($sheet->getRowIterator() as $row) {
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false);
                            foreach ($cellIterator as $cell) {
                                $text .= $cell->getValue() . "\t";
                            }
                            $text .= "\n";
                        }
                        $text .= "\n";
                    }
                    if (trim($text) !== '') {
                        return trim(preg_replace('/\s+/', ' ', $text));
                    }
                } catch (\Throwable $e) {
                    Log::warning('PhpSpreadsheet failed to read XLSX: ' . $e->getMessage(), ['path' => $localPath]);
                }
            }

            if (preg_match('/\.(png|jpe?g)$/i', $lower) && function_exists('shell_exec')) {
                $which = @shell_exec('where tesseract 2>nul || which tesseract 2>/dev/null');
                if ($which && trim($which) !== '') {
                    $cmd = 'tesseract ' . escapeshellarg($localPath) . ' stdout -l eng';
                    $output = @shell_exec($cmd);
                    if ($output && trim($output) !== '') {
                        return trim(preg_replace('/\s+/', ' ', $output));
                    }
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('File extraction failed: ' . $e->getMessage(), ['path' => $localPath]);
            return null;
        }
    }
}
