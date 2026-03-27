<?php

namespace App\Services;

use App\Services\Contracts\AICompatibilityServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqCompatibilityService implements AICompatibilityServiceInterface
{
    public function generate(string $query): array
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            Log::warning('Groq API key missing.');
            return [];
        }

        // Prompt محسّن لتقليل فشل النموذج
        $prompt = "You are an auto parts expert. Return ONLY a valid JSON object with a key 'models' containing an array of compatible cars for part: \"{$query}\". ".
                  "Each array item must have keys: 'brand', 'model', 'year' (integer), 'engine' (string or null). ".
                  "Do not use markdown, code blocks, or explanations. Be brief.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(25)
              ->post('https://api.groq.com/openai/v1/chat/completions', [
                  'model' => 'llama-3.1-8b-instant', // نموذج مجاني وآمن للتجربة
                  'messages' => [
                      ['role' => 'system', 'content' => 'You are a helpful auto parts expert.'],
                      ['role' => 'user', 'content' => $prompt],
                  ],
                  'temperature' => 0.5,
                  'max_tokens' => 1000,
              ]);

            if (!$response->successful()) {
                Log::error('Groq API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $text = $response->json('choices.0.message.content') ?? '';

            if (!$text) {
                Log::warning('Groq returned empty content', ['query' => $query]);
                return [];
            }

            // تنظيف النص من أي علامات قد تمنع json_decode
            $text = trim($text);
            $text = preg_replace('/^```json|```$/', '', $text);

            $decoded = json_decode($text, true);

            if (!$decoded || !isset($decoded['models'])) {
                Log::warning('Groq returned invalid JSON or missing models key', [
                    'text' => $text,
                    'query' => $query
                ]);
                return [];
            }

            return is_array($decoded['models']) ? $decoded['models'] : [];
        } catch (\Exception $e) {
            Log::error('Groq Exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            return [];
        }
    }
}
