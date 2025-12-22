<?php

namespace App\Services\Gemini;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $model;
    protected $apiUrl;
    protected $extension;

    public function __construct()
    {
        $this->extension = extension('gemini_ai');

        // Fallback to config if extension settings not available
        $this->apiKey = $this->extension?->settings->api_key ?? config('services.gemini.api_key');
        $this->model = $this->extension?->settings->model ?? config('services.gemini.model', 'gemini-pro');
        $this->apiUrl = config('services.gemini.api_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Check if Gemini AI is enabled and configured
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->extension &&
               $this->extension->status &&
               !empty($this->apiKey);
    }

    /**
     * Generate AI description for product
     *
     * @param string $productName
     * @param string $category
     * @param array $additionalContext
     * @return string
     */
    public function generateDescription($productName, $category = '', $additionalContext = [])
    {
        if (!$this->isEnabled()) {
            throw new Exception('Gemini AI is not enabled or configured properly');
        }

        $prompt = $this->buildDescriptionPrompt($productName, $category, $additionalContext);

        try {
            $response = $this->callGeminiApi($prompt);
            return $this->formatDescriptionForCKEditor($response);
        } catch (Exception $e) {
            Log::error('Gemini AI Description Generation Failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            throw new Exception('Failed to generate description: ' . $e->getMessage());
        }
    }

    /**
     * Suggest tags for product
     *
     * @param string $productName
     * @param string $description
     * @param string $category
     * @return array
     */
    public function suggestTags($productName, $description = '', $category = '')
    {
        if (!$this->isEnabled()) {
            throw new Exception('Gemini AI is not enabled or configured properly');
        }

        $prompt = $this->buildTagSuggestionsPrompt($productName, $description, $category);

        try {
            $response = $this->callGeminiApi($prompt);
            return $this->parseTags($response);
        } catch (Exception $e) {
            Log::error('Gemini AI Tag Suggestions Failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            throw new Exception('Failed to suggest tags: ' . $e->getMessage());
        }
    }

    /**
     * Improve existing content for SEO
     *
     * @param string $content
     * @param string $productName
     * @return string
     */
    public function improveContent($content, $productName = '')
    {
        if (!$this->isEnabled()) {
            throw new Exception('Gemini AI is not enabled or configured properly');
        }

        $prompt = $this->buildContentImprovementPrompt($content, $productName);

        try {
            $response = $this->callGeminiApi($prompt);
            return $this->formatDescriptionForCKEditor($response);
        } catch (Exception $e) {
            Log::error('Gemini AI Content Improvement Failed', [
                'error' => $e->getMessage(),
                'product' => $productName
            ]);
            throw new Exception('Failed to improve content: ' . $e->getMessage());
        }
    }

    /**
     * Call Gemini API
     *
     * @param string $prompt
     * @return string
     */
    protected function callGeminiApi($prompt)
    {
        $url = "{$this->apiUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(30)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if (!$response->successful()) {
            throw new Exception('Gemini API request failed: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid response from Gemini API');
        }

        return $data['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * Build prompt for description generation
     *
     * @param string $productName
     * @param string $category
     * @param array $additionalContext
     * @return string
     */
    protected function buildDescriptionPrompt($productName, $category, $additionalContext)
    {
        $prompt = "Generate a compelling, SEO-friendly product description for a digital marketplace item.\n\n";
        $prompt .= "Product Name: {$productName}\n";

        if ($category) {
            $prompt .= "Category: {$category}\n";
        }

        if (!empty($additionalContext['tags'])) {
            $prompt .= "Tags: " . implode(', ', $additionalContext['tags']) . "\n";
        }

        if (!empty($additionalContext['features'])) {
            $prompt .= "Key Features: " . implode(', ', $additionalContext['features']) . "\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "1. Write a detailed, engaging description (300-500 words)\n";
        $prompt .= "2. Use HTML formatting with proper semantic tags (<p>, <h3>, <ul>, <li>, <strong>, <em>)\n";
        $prompt .= "3. Include key features and benefits\n";
        $prompt .= "4. Make it SEO-optimized with relevant keywords\n";
        $prompt .= "5. Use professional, persuasive language\n";
        $prompt .= "6. Structure with clear headings and bullet points where appropriate\n";
        $prompt .= "7. DO NOT include any meta tags or styling attributes\n";
        $prompt .= "8. Start directly with content, no title needed\n\n";
        $prompt .= "Generate the description now:";

        return $prompt;
    }

    /**
     * Build prompt for tag suggestions
     *
     * @param string $productName
     * @param string $description
     * @param string $category
     * @return string
     */
    protected function buildTagSuggestionsPrompt($productName, $description, $category)
    {
        $prompt = "Suggest relevant tags for this digital marketplace product.\n\n";
        $prompt .= "Product Name: {$productName}\n";

        if ($category) {
            $prompt .= "Category: {$category}\n";
        }

        if ($description) {
            $prompt .= "Description: " . strip_tags(substr($description, 0, 500)) . "\n";
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "1. Suggest 8-12 relevant tags\n";
        $prompt .= "2. Tags should be specific and searchable\n";
        $prompt .= "3. Include both broad and specific tags\n";
        $prompt .= "4. Use lowercase, single words or short phrases\n";
        $prompt .= "5. Return only the tags, comma-separated\n";
        $prompt .= "6. No numbering, no explanations, just the tags\n\n";
        $prompt .= "Example format: wordpress, theme, responsive, ecommerce, modern, clean\n\n";
        $prompt .= "Generate tags now:";

        return $prompt;
    }

    /**
     * Build prompt for content improvement
     *
     * @param string $content
     * @param string $productName
     * @return string
     */
    protected function buildContentImprovementPrompt($content, $productName)
    {
        $prompt = "Improve this product description for better SEO and readability.\n\n";

        if ($productName) {
            $prompt .= "Product Name: {$productName}\n";
        }

        $prompt .= "Current Description:\n{$content}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "1. Enhance the description while keeping the core message\n";
        $prompt .= "2. Improve SEO with relevant keywords\n";
        $prompt .= "3. Better HTML structure with semantic tags\n";
        $prompt .= "4. Make it more engaging and professional\n";
        $prompt .= "5. Keep the same approximate length\n";
        $prompt .= "6. Use proper HTML formatting (<p>, <h3>, <ul>, <li>, <strong>)\n";
        $prompt .= "7. DO NOT include any meta tags or styling attributes\n\n";
        $prompt .= "Return the improved description:";

        return $prompt;
    }

    /**
     * Format description for CKEditor
     *
     * @param string $text
     * @return string
     */
    protected function formatDescriptionForCKEditor($text)
    {
        // Remove any markdown formatting if present
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);

        // Clean up the response
        $text = trim($text);

        // Remove any wrapping backticks or code blocks
        $text = preg_replace('/^```html\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        return $text;
    }

    /**
     * Parse tags from response
     *
     * @param string $response
     * @return array
     */
    protected function parseTags($response)
    {
        // Clean the response
        $response = trim($response);
        $response = str_replace(["\n", "\r"], '', $response);

        // Split by comma
        $tags = explode(',', $response);

        // Clean each tag
        $tags = array_map(function($tag) {
            return trim(strtolower($tag));
        }, $tags);

        // Remove empty tags and duplicates
        $tags = array_filter($tags);
        $tags = array_unique($tags);

        return array_values($tags);
    }
}
