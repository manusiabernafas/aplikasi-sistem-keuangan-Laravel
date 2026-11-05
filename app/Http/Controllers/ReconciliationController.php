<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartReconciliationRequest;
use App\Models\Account;
use App\Models\Reconciliation;
use App\Models\Transaction;
use App\Services\ReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ReconciliationController extends Controller
{
    public function __construct(private readonly ReconciliationService $reconciliationService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Reconciliation::class);

        $reconciliations = $this->reconciliationService->paginate();

        return view('reconciliations.index', compact('reconciliations'));
    }

    public function create(): View
    {
        $this->authorize('create', Reconciliation::class);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('reconciliations.create', compact('accounts'));
    }

    public function store(StartReconciliationRequest $request): RedirectResponse
    {
        $reconciliation = $this->reconciliationService->start($request->validated(), $request->user());

        return redirect()->route('reconciliations.show', $reconciliation);
    }

    public function show(Reconciliation $reconciliation): View
    {
        $this->authorize('view', $reconciliation);

        $reconciliation->load('account');
        $transactions = $this->reconciliationService->candidateTransactions($reconciliation);
        $matchedCount = $transactions->where('is_matched_here', true)->count();

        return view('reconciliations.show', compact('reconciliation', 'transactions', 'matchedCount'));
    }

    public function toggleMatch(Reconciliation $reconciliation, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $reconciliation);

        try {
            $this->reconciliationService->toggleMatch($reconciliation, $transaction);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back();
    }

    public function complete(Reconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('update', $reconciliation);

        $this->reconciliationService->complete($reconciliation);

        return redirect()->route('reconciliations.index')->with('success', 'Rekonsiliasi berhasil diselesaikan.');
    }
}
