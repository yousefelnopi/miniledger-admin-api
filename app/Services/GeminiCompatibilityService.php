<?php

namespace App\Services;

use App\Services\Contracts\AICompatibilityServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCompatibilityService implements AICompatibilityServiceInterface
{
    public function generate(string $query): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            Log::warning('Gemini API key missing.');
            return [];
        }

        $prompt = "You are an auto parts expert. Return ONLY a valid JSON object containing a key 'models' which holds an array of compatible cars for part: {$query}. Keys inside the array must be: 'brand', 'model', 'year' (integer), 'engine' (string/null). No markdown, no explanations.";

        try {

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])
                ->timeout(20)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->successful()) {

                $text = $response->json('candidates.0.content.parts.0.text');

                Log::info('Gemini RAW response', [
                    'text' => $text
                ]);
                if (!$text) {
                    return [];
                }

                $decoded = json_decode($text, true);

                if (!$decoded) {
                    Log::warning('Gemini returned invalid JSON', ['text' => $text]);
                    return [];
                }

                return isset($decoded[0]) ? $decoded : [$decoded];
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {

            Log::error('Gemini Exception', [
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }
}
