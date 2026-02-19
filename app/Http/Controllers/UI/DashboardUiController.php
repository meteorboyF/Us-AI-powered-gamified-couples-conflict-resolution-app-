<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();

        return view('dashboard-ui.page', [
            'currentCoupleId' => $couple?->id ?? $request->user()?->current_couple_id,
        ]);
    }
}
