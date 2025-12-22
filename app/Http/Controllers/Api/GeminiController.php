<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gemini\GeminiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeminiController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Generate product description
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            if (!$this->geminiService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Gemini AI is not enabled. Please enable it in admin settings.'),
                ], 403);
            }

            $description = $this->geminiService->generateDescription(
                $request->product_name,
                $request->category ?? '',
                [
                    'tags' => $request->tags ?? [],
                    'features' => $request->features ?? [],
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'description' => $description,
                ],
                'message' => translate('Description generated successfully'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to generate description: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suggest tags for product
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            if (!$this->geminiService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Gemini AI is not enabled. Please enable it in admin settings.'),
                ], 403);
            }

            $tags = $this->geminiService->suggestTags(
                $request->product_name,
                $request->description ?? '',
                $request->category ?? ''
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'tags' => $tags,
                ],
                'message' => translate('Tags suggested successfully'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to suggest tags: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Improve existing content
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function improveContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'product_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            if (!$this->geminiService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Gemini AI is not enabled. Please enable it in admin settings.'),
                ], 403);
            }

            $improvedContent = $this->geminiService->improveContent(
                $request->content,
                $request->product_name ?? ''
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $improvedContent,
                ],
                'message' => translate('Content improved successfully'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to improve content: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if Gemini AI is enabled
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus()
    {
        try {
            $isEnabled = $this->geminiService->isEnabled();

            return response()->json([
                'success' => true,
                'data' => [
                    'enabled' => $isEnabled,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
