<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin multi-provider LLM client. Tries Gemini first (free tier), then
 * OpenAI. Returns ['text' => ..., 'provider' => ...] or null.
 */
class LlmClient
{
    public function available(): bool
    {
        return (bool) (config('services.gemini.key') || config('services.openai.key'));
    }

    /**
     * @return array{text: string, provider: string}|null
     */
    public function generate(string $system, string $user, bool $json = false): ?array
    {
        foreach (['gemini', 'openai'] as $provider) {
            if (! config("services.{$provider}.key")) {
                continue;
            }
            try {
                $text = match ($provider) {
                    'gemini' => $this->callGemini($system, $user, $json),
                    'openai' => $this->callOpenAI($system, $user, $json),
                };
                if ($text !== null) {
                    return ['text' => $text, 'provider' => $provider];
                }
            } catch (\Throwable $e) {
                Log::warning("LlmClient: {$provider} falló", ['error' => $e->getMessage()]);
            }
        }

        return null;
    }

    private function callGemini(string $system, string $user, bool $json): ?string
    {
        $model = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $payload = [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [['parts' => [['text' => $user]]]],
            'generationConfig' => ['temperature' => 0.3],
        ];
        if ($json) {
            $payload['generationConfig']['response_mime_type'] = 'application/json';
        }

        // Free-tier models spike often: retry once on 429/503 before giving up
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . config('services.gemini.key'), $payload);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            if (! in_array($response->status(), [429, 503]) || $attempt === 1) {
                Log::warning('LlmClient: respuesta no exitosa de Gemini', ['status' => $response->status()]);
                return null;
            }

            sleep(3);
        }

        return null;
    }

    private function callOpenAI(string $system, string $user, bool $json): ?string
    {
        $payload = [
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'temperature' => 0.3,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];
        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (! $response->successful()) {
            Log::warning('LlmClient: respuesta no exitosa de OpenAI', ['status' => $response->status()]);
            return null;
        }

        return $response->json('choices.0.message.content');
    }
}
