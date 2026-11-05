<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function __construct(private readonly RecurringTransactionService $recurringService) {}

    public function index(): View
    {
        $this->authorize('viewAny', RecurringTransaction::class);

        $recurringTransactions = $this->recurringService->paginate();

        return view('recurring.index', compact('recurringTransactions'));
    }

    public function create(): View
    {
        $this->authorize('create', RecurringTransaction::class);

        $accounts = Account::active()->orderBy('account_name')->get();
        $categories = Category::active()->with(['parent', 'categoryType'])->orderBy('name')->get();

        return view('recurring.create', compact('accounts', 'categories'));
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->recurringService->create($request->validated(), $request->user());

        return redirect()->route('recurring-transactions.index')->with('success', 'Transaksi berulang berhasil dijadwalkan.');
    }

    public function edit(RecurringTransaction $recurringTransaction): View
    {
        $this->authorize('update', $recurringTransaction);

        $accounts = Account::active()->orderBy('account_name')->get();
        $categories = Category::active()->with(['parent', 'categoryType'])->orderBy('name')->get();

        return view('recurring.edit', ['recurring' => $recurringTransaction, 'accounts' => $accounts, 'categories' => $categories]);
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->recurringService->update($recurringTransaction, $request->validated());

        return redirect()->route('recurring-transactions.index')->with('success', 'Transaksi berulang berhasil diperbarui.');
    }

    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('delete', $recurringTransaction);

        $this->recurringService->delete($recurringTransaction);

        return redirect()->route('recurring-transactions.index')->with('success', 'Transaksi berulang berhasil dihapus.');
    }
}
