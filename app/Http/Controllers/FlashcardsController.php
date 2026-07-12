<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Flashcard;
use App\Models\FlashcardDeck;
use App\Http\Controllers\DocumentController;
use App\Services\AIServiceInterface;
use App\Services\AIUsageLimiterService;
use App\Services\DocumentTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\FlashcardInteraction;
use App\Models\StudySession;
use Carbon\Carbon;

class FlashcardsController extends Controller
{
    public function index()
    {
        return view('flashcards');
    }

    public function listDecks()
    {
        $user = Auth::user();
        $decks = FlashcardDeck::withCount([
                'flashcards as due_cards_count' => fn($query) => $query->where('mastered', false)->whereNull('reviewed_at'),
                'flashcards as mastered_cards_count' => fn($query) => $query->where('mastered', true),
            ])
            ->withCount('flashcards')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->get();

        return response()->json(['success' => true, 'decks' => $decks]);
    }

    public function uploadDocument(Request $request, DocumentTextExtractor $extractor)
    {
        return app(DocumentController::class)->upload($request, $extractor);
    }

    public function generate(Request $request, AIServiceInterface $aiService, AIUsageLimiterService $usageLimiter, DocumentTextExtractor $extractor)
    {
        $request->validate([
            'source_type' => 'required|string|in:document,chat,manual,summary,notes,image,voice',
            'document_id' => 'nullable|integer|exists:documents,id',
            'content' => 'nullable|string',
            'title' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:400',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
            'count' => 'nullable|integer|min:1|max:100',
            'card_type' => 'nullable|string|in:question_answer,definition,term,fill_in_the_blank,true_false,multiple_choice,mixed',
            'learning_focus' => 'nullable|array',
            'learning_focus.*' => 'string',
            'include_images' => 'nullable|boolean',
            'include_examples' => 'nullable|boolean',
            'include_mnemonics' => 'nullable|boolean',
            'highlight_terms' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $sourceType = $request->input('source_type');
        $settings = [
            'difficulty' => $request->input('difficulty', 'medium'),
            'count' => $request->input('count', 10),
            'card_type' => $request->input('card_type', 'question_answer'),
            'learning_focus' => $request->input('learning_focus', ['concepts', 'key_ideas']),
            'include_images' => $request->boolean('include_images'),
            'include_examples' => $request->boolean('include_examples'),
            'include_mnemonics' => $request->boolean('include_mnemonics'),
            'highlight_terms' => $request->boolean('highlight_terms'),
        ];

        $content = null;
        $sourceReference = ucfirst($sourceType);
        $attachmentPayload = null;

        if (in_array($sourceType, ['document', 'image', 'voice'], true)) {
            $document = Document::where('id', $request->input('document_id'))->where('user_id', $user->id)->first();
            if (! $document) {
                return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
            }

            if (empty($document->extracted_text)) {
                $path = storage_path('app/public/' . $document->filename);
                $extracted = $extractor->extract($path, $document->original_name, $document->mime);
                if ($extracted) {
                    $document->extracted_text = Str::limit($extracted, 200000);
                    $document->status = 'processed';
                    $document->save();
                }
            }

            $content = $document->extracted_text ?? '';
            $sourceReference = $document->original_name;

            if (trim($content) === '' && $this->canUseAttachmentFallback($document)) {
                $attachmentPayload = [
                    'type' => str_starts_with((string) $document->mime, 'image/') ? 'image' : 'file',
                    'path' => storage_path('app/public/' . $document->filename),
                    'mime' => $document->mime,
                    'name' => $document->original_name,
                ];
                $content = 'Please generate flashcards from the attached file content. Use the attached file as the primary source of truth.';
            }
        } elseif (in_array($sourceType, ['chat', 'manual', 'summary', 'notes'], true)) {
            $content = $request->input('content', '');
        }

        if (trim($content) === '') {
            return response()->json(['success' => false, 'message' => 'No source content provided. Please upload a supported document or enter text manually.'], 422);
        }

        try {
            $usageLimiter->ensureCanUse($user);

            $model = config('services.groq.model', 'llama-3.3-70b-versatile');
            $maxDocumentChars = config('services.groq.max_document_chars', 15000);
            $chunks = $this->buildContentChunks($content, $maxDocumentChars);
            $cards = [];
            $chunkCount = count($chunks);
            $cardsPerChunk = max(3, (int) ceil($settings['count'] / max(1, $chunkCount)));

            foreach ($chunks as $index => $chunk) {
                $messages = [
                    ['role' => 'system', 'content' => $this->buildSystemPrompt()],
                    ['role' => 'user', 'content' => $this->buildFlashcardPrompt($chunk, $sourceType, $sourceReference, $settings, $index + 1, $chunkCount, $cardsPerChunk)],
                ];

                $raw = $aiService->chat($messages, $model, 'flashcards', $attachmentPayload);
                $parsed = $this->parseFlashcardPayload($raw);
                if (! empty($parsed)) {
                    $cards = array_merge($cards, $parsed);
                }
                if ($attachmentPayload) {
                    $attachmentPayload = null; // only send attachment on first chunk
                }
            }

            $cards = $this->dedupeFlashcards($cards);
            $cards = array_slice($cards, 0, max(1, (int) $settings['count']));

            if (empty($cards)) {
                Log::warning('Flashcard generation returned no cards', ['user_id' => $user->id, 'response' => $raw]);
                return response()->json(['success' => false, 'message' => 'AI returned no flashcards.'], 500);
            }

            $deck = FlashcardDeck::create([
                'user_id' => $user->id,
                'title' => $request->input('title', 'AI Generated Deck'),
                'description' => $request->input('description', 'Flashcards created from uploaded content.'),
                'ai_confidence' => min(100, max(0, $request->input('ai_confidence', 87))),
                'estimated_study_time' => min(60, max(5, (int) ($settings['count'] * 1.5))),
            ]);

            foreach ($cards as $card) {
                Flashcard::create([
                    'user_id' => $user->id,
                    'deck_id' => $deck->id,
                    'subject' => $deck->title,
                    'question' => $card['question'] ?? $card['front'] ?? 'Untitled question',
                    'answer' => $card['answer'] ?? $card['back'] ?? 'No answer provided',
                    'explanation' => $card['explanation'] ?? $card['detail'] ?? null,
                    'example' => $card['example'] ?? null,
                    'mnemonic' => $card['mnemonic'] ?? null,
                    'tags' => isset($card['tags']) ? implode(',', (array) $card['tags']) : null,
                    'card_type' => $card['type'] ?? $settings['card_type'],
                    'source' => $sourceType,
                    'source_reference' => $sourceReference,
                    'difficulty' => $card['difficulty'] ?? $settings['difficulty'],
                    'reviewed_at' => null,
                    'due_at' => now()->addDays(1),
                    'mastered' => false,
                    'accuracy' => null,
                ]);
            }

            $user->logActivity(
                'Generated flashcards deck',
                $deck->title,
                sprintf('Created %d flashcards from %s source.', count($cards), $sourceType),
                'flashcard'
            );

            $usageLimiter->incrementUsage($user);

            return response()->json(['success' => true, 'deck' => $deck, 'cards' => $cards, 'raw' => $raw]);
        } catch (\RuntimeException $e) {
            Log::error('Flashcard generation failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('Flashcard generation failed: ' . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Unable to generate flashcards at this time.'], 500);
        }
    }

    protected function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are SHEELEARN, an intelligent AI learning assistant. Generate high-quality flashcards from the source content, with spaced repetition and study-ready formatting. Return a JSON array of cards with question, answer, explanation, example, mnemonic, tags, type, and difficulty. Keep the output concise and structured.
PROMPT;
    }

    protected function buildFlashcardPrompt(string $content, string $sourceType, string $reference, array $settings, int $chunkNumber = 1, int $chunkCount = 1, int $cardsPerChunk = 10): string
    {
        $focus = implode(', ', array_map(fn($item) => ucfirst(str_replace('_', ' ', $item)), $settings['learning_focus']));
        $chunkMeta = $chunkCount > 1 ? "Chunk {$chunkNumber} of {$chunkCount}: " : '';
        $cardsPerChunk = min(20, max(3, $cardsPerChunk));

        return "Source type: {$sourceType}\nSource reference: {$reference}\n\n{$chunkMeta}Generate {$cardsPerChunk} flashcards using the following settings:\n- Difficulty: " . ucfirst($settings['difficulty']) . "\n- Card type: " . str_replace('_', ' ', ucfirst($settings['card_type'])) . "\n- Learning focus: {$focus}\n- Include images: " . ($settings['include_images'] ? 'Yes' : 'No') . "\n- Include examples: " . ($settings['include_examples'] ? 'Yes' : 'No') . "\n- Include mnemonics: " . ($settings['include_mnemonics'] ? 'Yes' : 'No') . "\n- Highlight important terms: " . ($settings['highlight_terms'] ? 'Yes' : 'No') . "\n\nProcess the content below and return valid JSON. Focus on the most important concepts in this section. Avoid inventing unrelated facts.\n\nContent:\n{$content}";
    }

    protected function parseFlashcardPayload(string $response): array
    {
        $payload = json_decode($response, true);
        if (is_array($payload)) {
            return $payload;
        }

        if (preg_match('/(\[\s*\{.*\}\s*\])/s', $response, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected function buildContentChunks(string $content, int $maxChars): array
    {
        $content = trim(preg_replace('/\s+/u', ' ', $content));
        if ($content === '') {
            return [];
        }

        $chunks = [];
        $words = preg_split('/\s+/u', $content);
        $currentChunk = '';

        foreach ($words as $word) {
            $next = $currentChunk === '' ? $word : $currentChunk . ' ' . $word;
            if (mb_strlen($next) > $maxChars) {
                if ($currentChunk !== '') {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $word;
                continue;
            }
            $currentChunk = $next;
        }

        if (trim($currentChunk) !== '') {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    protected function dedupeFlashcards(array $cards): array
    {
        $seen = [];
        $filtered = [];

        foreach ($cards as $card) {
            $key = md5(strtolower(trim(($card['question'] ?? '') . '|' . ($card['answer'] ?? ''))));
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $filtered[] = $card;
            }
        }

        return $filtered;
    }

    public function showDeck(FlashcardDeck $deck)
    {
        $user = Auth::user();
        if ($deck->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Deck not found.'], 404);
        }

        $deck->load(['flashcards' => fn($query) => $query->orderBy('id')]);

        return response()->json(['success' => true, 'deck' => $deck]);
    }

    public function updateFlashcard(Request $request, Flashcard $flashcard)
    {
        $user = Auth::user();
        if ($flashcard->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Flashcard not found.'], 404);
        }

        $data = $request->validate([
            'question' => 'nullable|string',
            'answer' => 'nullable|string',
            'explanation' => 'nullable|string',
            'example' => 'nullable|string',
            'mnemonic' => 'nullable|string',
            'tags' => 'nullable|string',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
            'mastered' => 'nullable|boolean',
        ]);

        $flashcard->update($data);
        $user->logActivity('Updated flashcard', $flashcard->subject, 'Flashcard details updated', 'flashcard');

        return response()->json(['success' => true, 'card' => $flashcard]);
    }

    public function deleteFlashcard(Flashcard $flashcard)
    {
        $user = Auth::user();
        if ($flashcard->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Flashcard not found.'], 404);
        }

        $subject = $flashcard->subject;
        $flashcard->delete();
        $user->logActivity('Deleted flashcard', $subject, 'Flashcard deleted', 'flashcard');

        return response()->json(['success' => true]);
    }

    public function deleteDeck(FlashcardDeck $deck)
    {
        $user = Auth::user();
        if ($deck->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Deck not found.'], 404);
        }

        $deck->delete();

        return response()->json(['success' => true]);
    }

    public function recordInteraction(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'deck_id' => 'nullable|integer|exists:flashcard_decks,id',
            'flashcard_id' => 'nullable|integer|exists:flashcards,id',
            'action' => 'required|string|in:flip,answer,reset,session_complete',
            'correct' => 'nullable|boolean',
            'details' => 'nullable|array',
        ]);

        $interaction = FlashcardInteraction::create(array_merge($data, ['user_id' => $user->id]));

        // Update flashcard review state when answering
        if (! empty($data['flashcard_id']) && $data['action'] === 'answer') {
            $flashcard = Flashcard::where('id', $data['flashcard_id'])->where('user_id', $user->id)->first();
            if ($flashcard) {
                $flashcard->reviewed_at = now();
                $flashcard->accuracy = isset($data['correct']) ? ($data['correct'] ? 100 : 0) : $flashcard->accuracy;
                // Determine mastery based on recent interactions (last 3 answers)
                $recent = FlashcardInteraction::where('flashcard_id', $flashcard->id)
                    ->where('action', 'answer')
                    ->latest()
                    ->take(3)
                    ->pluck('correct')
                    ->filter()
                    ->count();
                if ($recent >= 2) {
                    $flashcard->mastered = true;
                }
                $flashcard->save();
            }
        }

        $actionLabel = ucfirst(str_replace('_', ' ', $data['action']));
        $subject = null;
        if (! empty($data['flashcard_id'])) {
            $subject = Flashcard::where('id', $data['flashcard_id'])->value('subject');
        }
        $details = $actionLabel;
        if (isset($data['correct'])) {
            $details .= $data['correct'] ? ' · Correct answer' : ' · Incorrect answer';
        }

        $user->logActivity($actionLabel . ' flashcard', $subject, $details, 'flashcard');

        return response()->json(['success' => true, 'interaction' => $interaction]);
    }

    public function analytics(Request $request, FlashcardDeck $deck = null)
    {
        $user = Auth::user();

        $deckId = $deck?->id ?? $request->input('deck_id');

        $query = FlashcardInteraction::where('user_id', $user->id);
        if ($deckId) $query->where('deck_id', $deckId);

        $since = Carbon::now()->subDays(30);
        $answers = (clone $query)->where('action', 'answer')->where('created_at', '>=', $since);
        $totalAnswers = $answers->count();
        $correctAnswers = (clone $answers)->where('correct', true)->count();

        $retention = $totalAnswers > 0 ? (int) round(100 * $correctAnswers / $totalAnswers) : 0;

        // Mastery: percent of cards in deck that are mastered
        $mastery = 0;
        $totalCards = 0;
        $masteredCount = 0;
        if ($deckId) {
            $deck = FlashcardDeck::where('id', $deckId)->where('user_id', $user->id)->withCount('flashcards')->first();
            if ($deck) {
                $totalCards = $deck->flashcards_count;
                $masteredCount = Flashcard::where('deck_id', $deckId)->where('user_id', $user->id)->where('mastered', true)->count();
                $mastery = $totalCards > 0 ? (int) round(100 * $masteredCount / $totalCards) : 0;
            }
        } else {
            $totalCards = Flashcard::where('user_id', $user->id)->count();
            $masteredCount = Flashcard::where('user_id', $user->id)->where('mastered', true)->count();
            $mastery = $totalCards > 0 ? (int) round(100 * $masteredCount / $totalCards) : 0;
        }

        // Day streak: consecutive days with activity (study session or interactions)
        $streak = 0;
        $today = Carbon::today();
        $date = $today->copy();
        while (true) {
            $exists = StudySession::where('user_id', $user->id)->where('session_date', $date->toDateString())->exists()
                || FlashcardInteraction::where('user_id', $user->id)->whereDate('created_at', $date->toDateString())->exists();
            if ($exists) {
                $streak++;
                $date->subDay();
            } else {
                break;
            }
        }

        // Reviews today
        $reviewsToday = (clone $query)->where('action', 'answer')->whereDate('created_at', Carbon::today())->count();

        // Weekly progress: counts per day for last 7 days
        $weekly = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $count = (clone $query)->where('action', 'answer')->whereDate('created_at', $d->toDateString())->count();
            $weekly[] = ['date' => $d->toDateString(), 'count' => $count];
        }

        return response()->json([
            'success' => true,
            'retention' => $retention,
            'mastery' => $mastery,
            'day_streak' => $streak,
            'reviews_today' => $reviewsToday,
            'weekly' => $weekly,
        ]);
    }

    public function resetDeckProgress(FlashcardDeck $deck)
    {
        $user = Auth::user();
        if ($deck->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Deck not found.'], 404);
        }

        Flashcard::where('deck_id', $deck->id)->update(['reviewed_at' => null, 'mastered' => false, 'accuracy' => null]);
        FlashcardInteraction::where('deck_id', $deck->id)->delete();
        $user->logActivity('Reset deck progress', $deck->title, 'Deck progress reset for review', 'flashcard');

        return response()->json(['success' => true]);
    }

    protected function canUseVisionFallback(Document $document): bool
    {
        $visionModel = config('services.groq.vision_model');
        return ! empty($visionModel) && is_string($visionModel) && str_starts_with($document->mime ?? '', 'image/');
    }

    protected function canUseAttachmentFallback(Document $document): bool
    {
        $path = storage_path('app/public/' . $document->filename);
        return file_exists($path) && is_readable($path);
    }
}
