<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChatUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();

        return view('chat.page', [
            'coupleId' => $couple?->id,
            'currentUserId' => $request->user()->id,
        ]);
    }
}
