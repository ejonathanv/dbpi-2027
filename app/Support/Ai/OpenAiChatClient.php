<?php

namespace App\Support\Ai;

use OpenAI;
use OpenAI\Client;

class OpenAiChatClient
{
    public function client(): Client
    {
        $apiKey = config('ai-assistant.openai.api_key');

        if (blank($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY no está configurada.');
        }

        return OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient(new \GuzzleHttp\Client([
                'timeout' => config('ai-assistant.openai.timeout', 90),
            ]))
            ->make();
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<int, array<string, mixed>>  $tools
     * @return array<string, mixed>
     */
    public function chat(array $messages, array $tools = []): array
    {
        $payload = [
            'model' => config('ai-assistant.openai.model'),
            'messages' => $messages,
            'temperature' => 0.35,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $response = $this->client()->chat()->create($payload);

        return $response->toArray();
    }
}
