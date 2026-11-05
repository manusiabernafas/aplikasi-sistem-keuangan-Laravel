<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtPaymentRequest;
use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Models\Account;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class DebtController extends Controller
{
    public function __construct(private readonly DebtService $debtService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Debt::class);

        $debts = $this->debtService->paginate($request->string('status')->toString() ?: null);
        $accounts = Account::active()->orderBy('account_name')->get();

        return view('debts.index', compact('debts', 'accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', Debt::class);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('debts.create', compact('accounts'));
    }

    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->debtService->create($request->validated(), $request->user());

        return redirect()->route('debts.index')->with('success', 'Hutang berhasil dicatat.');
    }

    public function edit(Debt $debt): View
    {
        $this->authorize('update', $debt);

        $accounts = Account::active()->orderBy('account_name')->get();
        $debt->load('payments');

        return view('debts.edit', compact('debt', 'accounts'));
    }

    public function update(UpdateDebtRequest $request, Debt $debt): RedirectResponse
    {
        $this->debtService->update($debt, $request->validated());

        return redirect()->route('debts.index')->with('success', 'Hutang berhasil diperbarui.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);

        try {
            $this->debtService->delete($debt);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('debts.index')->with('success', 'Hutang berhasil dihapus.');
    }

    public function pay(StoreDebtPaymentRequest $request, Debt $debt): RedirectResponse
    {
        try {
            $this->debtService->recordPayment($debt, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('debts.edit', $debt)->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
