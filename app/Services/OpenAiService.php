<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected $model = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * Send a request to the OpenAI API.
     */
    public function chat(array $messages, string $mode = 'vent'): string
    {
        // Add system instruction based on mode
        $systemMessage = [
            'role' => 'system',
            'content' => $this->getSystemPrompt($mode),
        ];

        // Prepend system message
        array_unshift($messages, $systemMessage);

        if (empty($this->apiKey)) {
            return $this->mockResponse($mode, end($messages)['content']);
        }

        try {
            $response = Http::withToken($this->apiKey)->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            } else {
                Log::error('OpenAI API Error: ' . $response->body());
                return "I'm having trouble connecting to my brain right now. Please try again later. (Error: " . $response->status() . ")";
            }
        } catch (\Exception $e) {
            Log::error('OpenAI Connection Exception: ' . $e->getMessage());
            return "Connection error. Please check your internet connection and try again.";
        }
    }

    /**
     * Get the system prompt customization based on the mode.
     */
    protected function getSystemPrompt(string $mode): string
    {
        if ($mode === 'vent') {
            return "You are an empathetic, non-judgmental relationship coach. 
            Your goal is to provide a safe space for the user to express their frustrations. 
            Validate their feelings using techniques like reflective listening. 
            Do NOT try to solve the problem immediately. 
            Do NOT take sides. 
            Ask 1-2 open-ended clarifying questions to help them explore their emotions deeper. 
            Keep responses concise (max 3 sentences) and warm.";
        }

        // Bridge mode = helping reformulate
        return "You are a communication expert specializing in conflict resolution. 
        Your goal is to help the user translate their complaints or frustrations into constructive 'I' statements. 
        Guide them to express: 'I feel [emotion] when [situation] because [impact/need], and I would appreciate [request]'. 
        Remove blame, criticism, and 'you' statements. 
        If the user is very angry, first help them calm down before suggesting phrasing.
        Keep responses concise and actionable.";
    }

    /**
     * Mock response for testing without API key.
     */
    protected function mockResponse(string $mode, string $lastUserMessage): string
    {
        if ($mode === 'vent') {
            return "I hear you. It sounds really tough to deal with that. (This is a MOCK response because no API key was found. Please add OPENAI_API_KEY to .env)";
        }
        return "Try saying: 'I feel overwhelmed when tasks stack up...' (This is a MOCK response because no API key was found).";
    }
}
