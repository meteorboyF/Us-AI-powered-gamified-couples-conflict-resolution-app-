<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WorldController;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DashboardUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();
        $world = null;
        $statusCode = null;
        $statusMessage = null;

        try {
            $response = app(WorldController::class)->index($request, $context);
            $world = $response->getData(true);
        } catch (HttpException $exception) {
            $statusCode = $exception->getStatusCode();
            $statusMessage = $exception->getMessage();
        }

        return view('dashboard-ui.page', [
            'currentCoupleId' => $couple?->id ?? $request->user()?->current_couple_id,
            'world' => $world,
            'statusCode' => $statusCode,
            'statusMessage' => $statusMessage,
        ]);
    }
}
