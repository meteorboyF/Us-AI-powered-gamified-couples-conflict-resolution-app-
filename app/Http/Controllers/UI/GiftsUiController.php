<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Models\GiftRequest;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GiftsUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();

        if (! $couple) {
            return view('gifts.page', [
                'coupleId' => null,
                'requests' => collect(),
                'selectedRequest' => null,
                'suggestions' => collect(),
            ]);
        }

        $requests = GiftRequest::query()
            ->where('couple_id', $couple->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $selectedRequest = null;
        $selectedRequestId = $request->integer('request_id');
        if ($selectedRequestId) {
            $selectedRequest = $requests->firstWhere('id', $selectedRequestId);
            if ($selectedRequest) {
                $this->authorize('view', $selectedRequest);
            }
        }

        $suggestions = $selectedRequest
            ? $selectedRequest->suggestions()->orderBy('id')->get()
            : collect();

        return view('gifts.page', [
            'coupleId' => (int) $couple->id,
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'suggestions' => $suggestions,
        ]);
    }
}
