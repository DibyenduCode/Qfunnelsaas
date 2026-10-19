<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\QuizEngineService;
use App\Services\FunnelService;

class QuizController
{
    public function show(Request $request, string $slug): Response
    {
        $funnel = FunnelService::getPublishedFunnelBySlug($slug);
        if (!$funnel) {
            return Response::html("
                <!DOCTYPE html>
                <html lang='en'>
                <head><meta charset='UTF-8'><title>Quiz Unavailable</title><script src='https://cdn.tailwindcss.com'></script></head>
                <body class='bg-[#F7F8FA] min-h-screen flex items-center justify-center p-6'>
                    <div class='max-w-md w-full bg-white border border-[#E4E7EC] rounded-xl p-8 text-center shadow-sm'>
                        <h2 class='text-xl font-bold text-[#171A21] mb-2'>Quiz Unavailable</h2>
                        <p class='text-[#687080] text-sm mb-6'>This funnel is currently in draft mode or unpublished.</p>
                    </div>
                </body>
                </html>
            ", 404);
        }

        // If the workspace is suspended, display a polite and professional pause notice
        if (($funnel['tenant_status'] ?? 'active') === 'suspended') {
            return Response::html("
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <title>" . htmlspecialchars($funnel['name']) . " — Temporarily Paused</title>
                    <script src='https://cdn.tailwindcss.com'></script>
                </head>
                <body class='bg-[#F8FAFC] min-h-screen flex items-center justify-center p-6 font-sans antialiased text-slate-800'>
                    <div class='max-w-md w-full bg-white border border-amber-200 rounded-2xl p-8 text-center shadow-lg'>
                        <div class='w-16 h-16 bg-amber-50 border border-amber-200 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4'>
                            <svg class='w-8 h-8' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                            </svg>
                        </div>
                        <h2 class='text-xl font-extrabold text-slate-900 mb-2'>" . htmlspecialchars($funnel['name']) . "</h2>
                        <span class='inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full mb-4'>Temporarily Paused</span>
                        <p class='text-slate-600 text-sm leading-relaxed mb-6'>
                            This quiz funnel is temporarily unavailable while the organizer updates their workspace account. Please check back shortly.
                        </p>
                        <div class='text-xs text-slate-400 border-t border-slate-100 pt-4'>
                            Powered by QuizFunnel SaaS
                        </div>
                    </div>
                </body>
                </html>
            ", 503);
        }

        $settings = json_decode($funnel['settings_snapshot_json'] ?? '{}', true) ?: [];

        return view('quiz.runner', [
            'title' => $funnel['name'],
            'funnel' => $funnel,
            'settings' => $settings,
            'slug' => $slug,
        ], null); // Public custom layout
    }

    public function apiInit(Request $request, string $slug): Response
    {
        $funnel = FunnelService::getPublishedFunnelBySlug($slug);
        if ($funnel && ($funnel['tenant_status'] ?? 'active') === 'suspended') {
            return Response::error("This quiz is temporarily paused by the organizer.", 'QUIZ_PAUSED', null, 503);
        }

        try {
            $data = QuizEngineService::initializeSession($slug);
            return Response::success($data);
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'INIT_FAILED', null, 400);
        }
    }

    public function apiStep(Request $request): Response
    {
        $token = $request->header('x-quiz-session') ?? $request->query('session_token');
        if (empty($token)) {
            return Response::error("Missing quiz session token.", "UNAUTHORIZED", null, 401);
        }

        try {
            $step = QuizEngineService::getCurrentStep($token);
            return Response::success($step);
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'STEP_ERROR', null, 400);
        }
    }

    public function apiSubmitForm(Request $request): Response
    {
        $token = $request->header('x-quiz-session') ?? $request->input('session_token');
        if (empty($token)) {
            return Response::error("Missing quiz session token.", "UNAUTHORIZED", null, 401);
        }

        $formData = $request->input('form_data', []);

        try {
            $res = QuizEngineService::submitForm($token, $formData);
            if (isset($res['success']) && $res['success'] === false) {
                return Response::error("Form validation failed", "VALIDATION_FAILED", $res['errors'] ?? [], 422);
            }
            return Response::success($res, 'Form submitted successfully.');
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'FORM_ERROR', null, 400);
        }
    }

    public function apiSubmitAnswer(Request $request): Response
    {
        $token = $request->header('x-quiz-session') ?? $request->input('session_token');
        if (empty($token)) {
            return Response::error("Missing quiz session token.", "UNAUTHORIZED", null, 401);
        }

        $nodeId = (int)$request->input('node_id');
        $answer = $request->input('answer', []);

        try {
            $step = QuizEngineService::submitAnswer($token, $nodeId, $answer);
            return Response::success($step);
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'ANSWER_ERROR', null, 400);
        }
    }

    public function apiBack(Request $request): Response
    {
        $token = $request->header('x-quiz-session') ?? $request->input('session_token');
        if (empty($token)) {
            return Response::error("Missing quiz session token.", "UNAUTHORIZED", null, 401);
        }

        try {
            $step = QuizEngineService::navigateBack($token);
            return Response::success($step);
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'BACK_ERROR', null, 400);
        }
    }

    public function result(Request $request, string $slug, string $token): Response
    {
        $result = QuizEngineService::getPublicResult($token);
        if (!$result) {
            return Response::html("<h1>Result Not Found</h1><p>The requested result token is invalid or expired.</p>", 404);
        }

        return view('quiz.result', [
            'title' => 'Your Quiz Summary',
            'result' => $result,
            'slug' => $slug,
        ], null);
    }
}
