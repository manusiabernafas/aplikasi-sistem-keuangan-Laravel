<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\InsightService;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function index(InsightService $insightService): View
    {
        $this->authorize('viewAny', Transaction::class);

        $insights = $insightService->generate();

        return view('insights.index', compact('insights'));
    }
}
