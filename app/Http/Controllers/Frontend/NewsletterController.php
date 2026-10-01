<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\Frontend\SubscribeNewsletterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\SubscribeNewsletterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email to the newsletter.
     */
    public function subscribe(
        SubscribeNewsletterRequest $request,
        SubscribeNewsletterAction $action
    ): JsonResponse {
        try {
            $result = $action->execute(
                (string) $request->validated('email'),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Newsletter subscription failed: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'কিছু সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।',
            ], 500);
        }
    }
}
