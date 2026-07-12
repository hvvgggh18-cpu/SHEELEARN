<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GroqService implements AIServiceInterface
{
    public function chat(array $messages, string $model, string $mode, ?array $attachment = null): string
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            Log::error('Groq API key is missing.');
            throw new RuntimeException('Groq API key is not configured. Set GROQ_API_KEY in your .env file.');
        }

        $payload = $this->buildRequestPayload($messages, $model, $attachment);

        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(60);

        try {
            Log::debug('Groq request payload (sanitized):', $this->sanitizeRequestForLogging($payload));
            Log::debug('Groq request token estimates', [
                'message_count' => count($payload['messages']),
                'prompt_tokens' => array_sum(array_map(fn(array $message) => $this->estimateMessageTokens($message), $payload['messages'])),
                'max_tokens' => $payload['max_tokens'],
                'model' => $payload['model'],
            ]);
            $response = $request->post($this->getBaseUrl() . '/chat/completions', $payload);
            $response->throw();

            $json = $response->json();
            Log::info('Groq request succeeded', ['model' => $payload['model'], 'response' => $this->sanitizeResponseForLogging($json)]);

            return $this->extractResponseText($json);
        } catch (RequestException $exception) {
            $json = $exception->response?->json() ?? [];
            $status = $exception->response?->status();
            $message = $json['error']['message'] ?? $exception->getMessage();

            if ($this->shouldRetryWithFallback($message, $status, $payload['model'])) {
                $fallbackModel = $this->getConfiguredFallbackModel($payload['model']);
                if ($fallbackModel && $fallbackModel !== $payload['model']) {
                    Log::warning('Retrying Groq request with fallback model', ['original_model' => $payload['model'], 'fallback_model' => $fallbackModel, 'reason' => $message]);
                    $payload['model'] = $fallbackModel;
                    $response = $request->post($this->getBaseUrl() . '/chat/completions', $payload);
                    $response->throw();
                    $json = $response->json();
                    Log::info('Groq request succeeded with fallback model', ['model' => $payload['model'], 'response' => $this->sanitizeResponseForLogging($json)]);
                    return $this->extractResponseText($json);
                }
            }

            Log::error('Groq request failed: ' . $message, [
                'status' => $status,
                'payload' => $this->sanitizeRequestForLogging($payload),
                'response' => $json,
                'exception' => $exception,
            ]);

            if ($status === 401 || $status === 403) {
                throw new RuntimeException('Invalid Groq API key or unauthorized access.');
            }

            if ($status === 429) {
                throw new RuntimeException('Rate limit exceeded. Please wait a moment and try again.');
            }

            throw new RuntimeException($message ?: 'AI service returned an error. Please try again later.');
        } catch (ConnectionException $exception) {
            Log::error('Groq connection failed: ' . $exception->getMessage(), ['exception' => $exception]);
            throw new RuntimeException('Unable to connect to the AI service. Please try again later.');
        } catch (RequestException $exception) {
            $json = $exception->response?->json() ?? [];
            $status = $exception->response?->status();
            $message = $json['error']['message'] ?? $exception->getMessage();

            Log::error('Groq request failed: ' . $message, [
                'status' => $status,
                'payload' => $this->sanitizeRequestForLogging($payload),
                'response' => $json,
                'exception' => $exception,
            ]);

            if ($status === 401 || $status === 403) {
                throw new RuntimeException('Invalid Groq API key or unauthorized access.');
            }

            if ($status === 429) {
                throw new RuntimeException('Rate limit exceeded. Please wait a moment and try again.');
            }

            throw new RuntimeException($message ?: 'AI service returned an error. Please try again later.');
        } catch (\Throwable $exception) {
            Log::error('Groq unexpected error: ' . $exception->getMessage(), ['exception' => $exception]);
            throw new RuntimeException('An unexpected AI error occurred. Please try again later.');
        }
    }

    protected function buildRequestPayload(array $messages, string $model, ?array $attachment = null): array
    {
        $content = $this->formatMessages($messages);

        if ($attachment && isset($attachment['path'])) {
            $attachmentContent = $this->buildAttachmentTextMessage($attachment);
            if ($attachmentContent) {
                array_unshift($content, $attachmentContent);
            }
        }

        $content = $this->trimMessagesToFitTokenBudget($content);
        $promptTokens = array_sum(array_map(fn(array $message) => $this->estimateMessageTokens($message), $content));
        $maxTokens = $this->getDynamicMaxTokens($promptTokens);

        if ($promptTokens + $maxTokens + $this->getTokenSafetyMargin() > $this->getContextTokens()) {
            $maxTokens = max(1, $this->getContextTokens() - $promptTokens - $this->getTokenSafetyMargin());
        }

        return [
            'model' => $this->getFallbackModel($model, $promptTokens),
            'temperature' => 0.25,
            'max_tokens' => $maxTokens,
            'messages' => $content,
        ];
    }

    protected function getMaxTokens(): int
    {
        return max(1, config('services.groq.max_tokens', 2000));
    }

    protected function getCompletionTokens(): int
    {
        return max(1, config('services.groq.completion_tokens', 1600));
    }

    protected function getContextTokens(): int
    {
        return max(1, config('services.groq.context_tokens', 12000));
    }

    protected function getDynamicMaxTokens(int $promptTokens): int
    {
        $desired = $this->getMaxTokens();
        $completionCap = min($desired, $this->getCompletionTokens());
        $available = max(1, $this->getContextTokens() - $promptTokens - 2 - $this->getTokenSafetyMargin());
        return max(1, min($completionCap, $available));
    }

    protected function getConfiguredFallbackModel(string $model): ?string
    {
        $fallback = config('services.groq.fallback_model');
        return is_string($fallback) && trim($fallback) !== '' ? trim($fallback) : null;
    }

    protected function shouldRetryWithFallback(string $message, ?int $status, string $model): bool
    {
        if ($model === $this->getConfiguredFallbackModel($model)) {
            return false;
        }

        if ($status === 429) {
            return true;
        }

        $normalized = strtolower($message);
        return str_contains($normalized, 'request too large')
            || str_contains($normalized, 'too many tokens')
            || str_contains($normalized, 'token limit');
    }

    protected function getFallbackModel(string $requestedModel, int $promptTokens): string
    {
        $visionModel = config('services.groq.vision_model');
        $default = $requestedModel;
        $fallback = config('services.groq.fallback_model', 'llama-3.2-70b');

        if (! empty($visionModel) && str_contains($requestedModel, 'vision') === false) {
            return $default;
        }

        if ($promptTokens > $this->getContextTokens()) {
            return $fallback;
        }

        return $default;
    }

    protected function trimMessagesToFitTokenBudget(array $messages): array
    {
        $promptBudget = max(1, $this->getContextTokens() - min($this->getMaxTokens(), $this->getCompletionTokens()) - $this->getTokenSafetyMargin());
        $tokens = array_map(fn(array $message) => $this->estimateMessageTokens($message), $messages);
        $total = array_sum($tokens);

        if ($total <= $promptBudget) {
            return $messages;
        }

        $systemMessages = [];
        $otherMessages = [];
        $otherTokens = [];

        foreach ($messages as $index => $message) {
            if ($message['role'] === 'system') {
                $systemMessages[] = $message;
            } else {
                $otherMessages[] = $message;
                $otherTokens[] = $tokens[$index];
            }
        }

        $systemTokenCount = array_sum(array_map(fn(array $message) => $this->estimateMessageTokens($message), $systemMessages));
        $remainingBudget = max(1, $promptBudget - $systemTokenCount);
        $trimmed = $systemMessages;

        if ($systemTokenCount >= $promptBudget) {
            $systemBudget = max(1, (int) floor($promptBudget * 0.4));
            $trimmedSystem = [];
            $usedSystemTokens = 0;

            foreach ($systemMessages as $systemMessage) {
                $messageTokens = $this->estimateTokens($systemMessage['role'] . ' ' . $systemMessage['content']);
                if ($usedSystemTokens + $messageTokens <= $systemBudget) {
                    $trimmedSystem[] = $systemMessage;
                    $usedSystemTokens += $messageTokens;
                } else {
                    $trimmedSystem[] = [
                        'role' => $systemMessage['role'],
                        'content' => $this->truncateTextToFitBudget($systemMessage['content'], $systemBudget - $usedSystemTokens),
                    ];
                    $usedSystemTokens = $systemBudget;
                    break;
                }
            }

            $remainingBudget = max(1, $promptBudget - $usedSystemTokens);
            $trimmed = $trimmedSystem;

            if (! empty($otherMessages)) {
                $lastMessage = $otherMessages[array_key_last($otherMessages)];
                $trimmed[] = [
                    'role' => $lastMessage['role'],
                    'content' => $this->truncateTextToFitBudget($lastMessage['content'], $remainingBudget),
                ];
            }

            return $trimmed;
        }

        $collected = [];
        for ($i = count($otherMessages) - 1; $i >= 0; $i--) {
            $message = $otherMessages[$i];
            $messageTokens = $otherTokens[$i];
            if ($messageTokens <= $remainingBudget) {
                array_unshift($collected, $message);
                $remainingBudget -= $messageTokens;
            } else {
                break;
            }
        }

        if (empty($collected) && ! empty($otherMessages)) {
            $lastMessage = $otherMessages[array_key_last($otherMessages)];
            $trimmed[] = [
                'role' => $lastMessage['role'],
                'content' => $this->truncateTextToFitBudget($lastMessage['content'], $remainingBudget),
            ];
            return $trimmed;
        }

        if (count($collected) < count($otherMessages)) {
            $trimmed[] = [
                'role' => 'system',
                'content' => '[Conversation history has been shortened to fit within token limits.]',
            ];
        }

        return array_merge($trimmed, $collected);
    }

    protected function estimateMessageTokens(array $message): int
    {
        return $this->estimateTokens($message['role'] . ' ' . $this->getMessageContentAsString($message['content']));
    }

    protected function getMessageContentAsString(mixed $content): string
    {
        if (is_array($content)) {
            return $this->normalizeStructuredContentForTokenEstimation($content);
        }

        return (string) $content;
    }

    protected function normalizeStructuredContentForTokenEstimation(array $content): string
    {
        $result = '';

        foreach ($content as $item) {
            if (is_string($item)) {
                $result .= ' ' . $item;
                continue;
            }

            if (! is_array($item)) {
                $result .= ' ' . json_encode($item);
                continue;
            }

            $type = $item['type'] ?? null;
            if ($type === 'text' && isset($item['text'])) {
                $result .= ' ' . $item['text'];
                continue;
            }

            if ($type === 'image_url' && isset($item['image_url']['url'])) {
                $result .= ' [image content]';
                continue;
            }

            $result .= ' ' . json_encode($item);
        }

        return trim($result);
    }

    protected function isImageAttachment(array $attachment): bool
    {
        return isset($attachment['mime']) && str_starts_with($attachment['mime'], 'image/');
    }

    protected function attachImageToMessages(array $messages, array $attachment): array
    {
        $imageContent = $this->buildImageAttachmentContent($attachment);
        if (! $imageContent) {
            return $messages;
        }

        $lastIndex = array_key_last($messages);
        if ($lastIndex !== null && $messages[$lastIndex]['role'] === 'user') {
            $existingContent = $messages[$lastIndex]['content'];
            $contentArray = is_array($existingContent) ? $existingContent : [['type' => 'text', 'text' => $existingContent]];
            $contentArray[] = $imageContent;
            $messages[$lastIndex]['content'] = $contentArray;
            return $messages;
        }

        $messages[] = [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'Please analyze the attached image.'],
                $imageContent,
            ],
        ];

        return $messages;
    }

    protected function buildAttachmentTextMessage(array $attachment): ?array
    {
        $attachmentContent = $this->buildAttachmentContent($attachment);
        if (! $attachmentContent) {
            throw new RuntimeException('The attached file is too large to include in the AI request. Please attach a smaller file or provide text-based content.');
        }

        return ['role' => 'system', 'content' => $attachmentContent];
    }

    protected function buildImageAttachmentContent(array $attachment): ?array
    {
        $path = $attachment['path'];
        if (! file_exists($path) || ! is_readable($path)) {
            Log::warning('Groq image attachment not readable.', ['attachment' => $attachment]);
            return null;
        }

        $mime = $attachment['mime'] ?? 'image/jpeg';
        $data = file_get_contents($path);
        if ($data === false) {
            Log::warning('Failed to read image attachment for Groq request.', ['attachment' => $attachment]);
            return null;
        }

        $maxBytes = $this->getAttachmentMaxBytes();
        if (strlen($data) > $maxBytes) {
            $compressed = $this->compressImageToMaxBytes($path, $maxBytes);
            if ($compressed !== null) {
                $data = $compressed;
            } else {
                Log::warning('Groq image attachment is too large after compression.', ['attachment' => $attachment, 'max_bytes' => $maxBytes]);
                return null;
            }
        }

        $encoded = base64_encode($data);
        if (strlen($encoded) > 4 * 1024 * 1024) {
            Log::warning('Groq image attachment base64 payload exceeds 4MB.', ['attachment' => $attachment, 'encoded_bytes' => strlen($encoded)]);
            return null;
        }

        return [
            'type' => 'image_url',
            'image_url' => [
                'url' => "data:{$mime};base64,{$encoded}",
            ],
        ];
    }

    protected function truncateTextToFitBudget(mixed $content, int $budget): mixed
    {
        if (is_string($content)) {
            $suffix = "\n\n[Content truncated due to token limit.]";
            $maxChars = max(1, $budget * 4) - strlen($suffix);
            if ($maxChars <= 0) {
                return $suffix;
            }

            if (strlen($content) <= $maxChars) {
                return $content;
            }

            return rtrim(substr($content, 0, $maxChars)) . $suffix;
        }

        if (is_array($content)) {
            $result = [];
            $remainingBudget = $budget;

            foreach ($content as $item) {
                if (! is_array($item) || ($item['type'] ?? null) !== 'text' || ! isset($item['text'])) {
                    $result[] = $item;
                    continue;
                }

                $truncatedText = $this->truncateTextToFitBudget($item['text'], $remainingBudget);
                $result[] = ['type' => 'text', 'text' => $truncatedText];
                $remainingBudget = max(0, $remainingBudget - $this->estimateTokens($truncatedText));

                if ($remainingBudget <= 0) {
                    break;
                }
            }

            return $result;
        }

        return $this->truncateTextToFitBudget((string) $content, $budget);
    }

    protected function estimateTokens(string $text): int
    {
        return max(1, (int) ceil(strlen($text) / 4));
    }

    protected function formatMessages(array $messages): array
    {
        return array_values(array_map(function (array $message) {
            return [
                'role' => $message['role'],
                'content' => $this->getMessageContentAsString($message['content']),
            ];
        }, $messages));
    }

    protected function buildAttachmentContent(array $attachment): ?string
    {
        $path = $attachment['path'];
        if (! file_exists($path) || ! is_readable($path)) {
            Log::warning('Groq attachment not readable.', ['attachment' => $attachment]);
            return null;
        }

        $mime = $attachment['mime'] ?? 'application/octet-stream';
        $name = $attachment['name'] ?? basename($path);
        $data = file_get_contents($path);
        if ($data === false) {
            Log::warning('Failed to read attachment for Groq request.', ['attachment' => $attachment]);
            return null;
        }

        $maxBytes = $this->getAttachmentMaxBytes();
        if (str_starts_with($mime, 'image/')) {
            if (strlen($data) > $maxBytes) {
                $compressed = $this->compressImageToMaxBytes($path, $maxBytes);
                if ($compressed !== null) {
                    $data = $compressed;
                } else {
                    Log::warning('Groq image attachment is too large after compression.', ['attachment' => $attachment, 'max_bytes' => $maxBytes]);
                    return null;
                }
            }
        } elseif (strlen($data) > $maxBytes) {
            Log::warning('Groq file attachment is too large to include.', ['attachment' => $attachment, 'max_bytes' => $maxBytes]);
            return null;
        }

        $encoded = base64_encode($data);

        if (str_starts_with($mime, 'image/')) {
            return "Attached image file: {$name} ({$mime}). The following content is the complete image encoded in base64. Use the image contents to analyze the file visually and answer based on the image itself, not the filename or metadata.\n\n[IMAGE_BASE64]\n{$encoded}";
        }

        return "Attached file: {$name} ({$mime}). The following content is the complete file encoded in base64. Use the file contents as the primary source of truth and answer based on it, not on the filename or metadata. If the file content is not directly readable, extract and analyze it from the encoded data.\n\n[FILE_BASE64]\n{$encoded}";
    }

    protected function compressImageToMaxBytes(string $path, int $maxBytes): ?string
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $image = @imagecreatefromstring($raw);
        if (! $image) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $quality = 85;
        $data = null;

        for ($scale = 1.0; $scale >= 0.25; $scale -= 0.15) {
            $newWidth = max(1, (int) floor($width * $scale));
            $newHeight = max(1, (int) floor($height * $scale));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            for ($tryQuality = $quality; $tryQuality >= 20; $tryQuality -= 15) {
                ob_start();
                imagejpeg($resized, null, $tryQuality);
                $compressed = ob_get_clean();
                if ($compressed !== false && strlen($compressed) <= $maxBytes) {
                    imagedestroy($resized);
                    imagedestroy($image);
                    return $compressed;
                }
            }

            imagedestroy($resized);
        }

        imagedestroy($image);
        return null;
    }

    protected function extractResponseText(array $json): string
    {
        if (isset($json['choices'][0]['message']['content'])) {
            return trim($json['choices'][0]['message']['content']);
        }

        if (isset($json['choices'][0]['text'])) {
            return trim($json['choices'][0]['text']);
        }

        Log::warning('Groq response could not be parsed into text.', ['response' => $json]);
        throw new RuntimeException('Unable to parse AI response. Please try again later.');
    }

    protected function sanitizeRequestForLogging(array $payload): array
    {
        $sanitized = $payload;
        if (isset($sanitized['messages']) && is_array($sanitized['messages'])) {
            foreach ($sanitized['messages'] as $index => $message) {
                if (is_string($message['content'] ?? null) && (str_contains($message['content'], '[IMAGE_BASE64]') || str_contains($message['content'], '[FILE_BASE64]'))) {
                    $sanitized['messages'][$index]['content'] = '[REDACTED_FILE_CONTENT]';
                    continue;
                }

                if (is_array($message['content'])) {
                    foreach ($message['content'] as $item) {
                        if (is_array($item) && ($item['type'] ?? null) === 'image_url') {
                            $sanitized['messages'][$index]['content'] = '[REDACTED_IMAGE_CONTENT]';
                            break;
                        }
                    }
                }
            }
        }
        return $sanitized;
    }

    protected function sanitizeResponseForLogging(array $response): array
    {
        $sanitized = $response;
        if (isset($sanitized['choices']) && is_array($sanitized['choices'])) {
            foreach ($sanitized['choices'] as $index => $choice) {
                if (isset($choice['message']['content'])) {
                    $sanitized['choices'][$index]['message']['content'] = '[REDACTED]';
                }
            }
        }
        return $sanitized;
    }

    protected function getBaseUrl(): string
    {
        return rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
    }

    protected function getTokenSafetyMargin(): int
    {
        return max(1, config('services.groq.token_safety_margin', 50));
    }

    protected function getAttachmentMaxBytes(): int
    {
        return max(1, config('services.groq.max_attachment_bytes', 32768));
    }

    protected function getApiKey(): ?string
    {
        return config('services.groq.api_key');
    }
}
