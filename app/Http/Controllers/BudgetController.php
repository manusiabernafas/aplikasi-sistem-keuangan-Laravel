<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetService $budgetService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Budget::class);

        $budgets = $this->budgetService->paginate($request->string('status')->toString() ?: null);
        $budgets->getCollection()->transform(function (Budget $budget) {
            $budget->setAttribute('progress', $this->budgetService->progress($budget));

            return $budget;
        });

        return view('budgets.index', compact('budgets'));
    }

    public function create(): View
    {
        $this->authorize('create', Budget::class);

        $categories = Category::ofType('EXPENSE')->active()->with('parent')->orderBy('name')->get();
        $accounts = Account::active()->orderBy('account_name')->get();

        return view('budgets.create', compact('categories', 'accounts'));
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->budgetService->create($request->validated(), $request->user());

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil ditambahkan.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);

        $categories = Category::ofType('EXPENSE')->active()->with('parent')->orderBy('name')->get();
        $accounts = Account::active()->orderBy('account_name')->get();
        $progress = $this->budgetService->progress($budget);

        return view('budgets.edit', compact('budget', 'categories', 'accounts', 'progress'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->budgetService->update($budget, $request->validated());

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        $this->budgetService->delete($budget);

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil dihapus.');
    }
}
