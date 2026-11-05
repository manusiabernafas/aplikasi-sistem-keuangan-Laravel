<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContributeFinancialGoalRequest;
use App\Http\Requests\StoreFinancialGoalRequest;
use App\Http\Requests\UpdateFinancialGoalRequest;
use App\Models\Account;
use App\Models\FinancialGoal;
use App\Services\FinancialGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinancialGoalController extends Controller
{
    public function __construct(private readonly FinancialGoalService $goalService) {}

    public function index(): View
    {
        $this->authorize('viewAny', FinancialGoal::class);

        $goals = $this->goalService->paginate();

        return view('goals.index', compact('goals'));
    }

    public function create(): View
    {
        $this->authorize('create', FinancialGoal::class);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('goals.create', compact('accounts'));
    }

    public function store(StoreFinancialGoalRequest $request): RedirectResponse
    {
        $this->goalService->create($request->validated(), $request->user());

        return redirect()->route('goals.index')->with('success', 'Target keuangan berhasil ditambahkan.');
    }

    public function edit(FinancialGoal $goal): View
    {
        $this->authorize('update', $goal);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('goals.edit', compact('goal', 'accounts'));
    }

    public function update(UpdateFinancialGoalRequest $request, FinancialGoal $goal): RedirectResponse
    {
        $this->goalService->update($goal, $request->validated());

        return redirect()->route('goals.index')->with('success', 'Target keuangan berhasil diperbarui.');
    }

    public function contribute(ContributeFinancialGoalRequest $request, FinancialGoal $financialGoal): RedirectResponse
    {
        $this->goalService->addContribution($financialGoal, (float) $request->validated()['amount']);

        return redirect()->route('goals.index')->with('success', 'Kontribusi berhasil ditambahkan.');
    }

    public function destroy(FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);

        $this->goalService->delete($goal);

        return redirect()->route('goals.index')->with('success', 'Target keuangan berhasil dihapus.');
    }
}
