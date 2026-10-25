<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    protected ?string $apiKey;

    protected string $model;

    protected int $timeout;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-3.8-flash');
        $this->timeout = (int) config('services.gemini.timeout', 15);
        $this->baseUrl = (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * @param  array<int, array{role: string, parts: array<int, array{text: string}>}>  $history
     */
    public function generateResponse(string $systemInstruction, array $history, string $userMessage): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $contents = $this->buildContents($history, $userMessage);

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => $this->buildGenerationConfig(),
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE',
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE',
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE',
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE',
                ],
            ],
        ];

        foreach ($this->candidateModels() as $model) {
            $text = $this->requestModel($model, $payload);

            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    /**
     * Gemini 3.x models spend part of maxOutputTokens on internal thinking,
     * so a low thinking level keeps most of the budget for the visible answer.
     *
     * @return array<string, mixed>
     */
    protected function buildGenerationConfig(): array
    {
        $config = [
            'temperature' => 0.4,
            'maxOutputTokens' => (int) config('ai.chat.max_output_tokens', 2048),
        ];

        $thinkingLevel = (string) config('ai.chat.thinking_level', '');
        if ($thinkingLevel !== '') {
            $config['thinkingConfig'] = ['thinkingLevel' => $thinkingLevel];
        }

        return $config;
    }

    /**
     * @return array<int, string>
     */
    protected function candidateModels(): array
    {
        $fallbackModel = (string) config('services.gemini.fallback_model', '');

        return array_values(array_unique(array_filter([$this->model, $fallbackModel])));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function requestModel(string $model, array $payload): ?string
    {
        $endpoint = rtrim($this->baseUrl, '/').'/models/'.$model.':generateContent?key='.$this->apiKey;

        try {
            $response = Http::withOptions([
                'version' => 1.1,
            ])
                ->timeout($this->timeout)
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                Log::warning('Gemini API returned unsuccessful status', [
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $candidate = $response->json('candidates.0');
            if (! $candidate) {
                return null;
            }

            $finishReason = $candidate['finishReason'] ?? null;
            if ($finishReason === 'SAFETY') {
                Log::info('Gemini response blocked by safety filter', ['model' => $model]);

                return null;
            }

            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('Gemini response truncated by MAX_TOKENS', ['model' => $model]);

                return null;
            }

            $text = $candidate['content']['parts'][0]['text'] ?? null;
            if (empty($text)) {
                return null;
            }

            return $this->cleanResponseText((string) $text);
        } catch (Throwable $e) {
            Log::error('GeminiService exception: '.$e->getMessage(), ['model' => $model]);

            return null;
        }
    }

    /**
     * @param  array<int, array{role: string, parts: array<int, array{text: string}>}>  $history
     * @return array<int, array{role: string, parts: array<int, array{text: string}>}>
     */
    protected function buildContents(array $history, string $userMessage): array
    {
        $turns = [];
        foreach ($history as $turn) {
            if (empty($turn['parts'][0]['text'] ?? '')) {
                continue;
            }
            $role = ($turn['role'] ?? 'user') === 'user' ? 'user' : 'model';

            if (! empty($turns) && end($turns)['role'] === $role) {
                $lastIndex = count($turns) - 1;
                $turns[$lastIndex]['parts'][0]['text'] .= "\n".$turn['parts'][0]['text'];
            } else {
                $turns[] = [
                    'role' => $role,
                    'parts' => [['text' => (string) $turn['parts'][0]['text']]],
                ];
            }
        }

        while (! empty($turns) && $turns[0]['role'] !== 'user') {
            array_shift($turns);
        }

        if (! empty($turns) && end($turns)['role'] === 'user') {
            $lastIndex = count($turns) - 1;
            $turns[$lastIndex]['parts'][0]['text'] .= "\n".$userMessage;
        } else {
            $turns[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]],
            ];
        }

        return $turns;
    }

    protected function cleanResponseText(string $rawText): ?string
    {
        $clean = preg_replace('/https?:\/\/[^\s]+/', '', $rawText);
        $clean = preg_replace('/[*#_`~]/', '', (string) $clean);
        $clean = trim(preg_replace('/\s+/', ' ', (string) $clean));

        return empty($clean) ? null : $clean;
    }
}
