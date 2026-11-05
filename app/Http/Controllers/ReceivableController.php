<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Account;
use App\Models\Receivable;
use App\Services\ReceivableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ReceivableController extends Controller
{
    public function __construct(private readonly ReceivableService $receivableService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Receivable::class);

        $receivables = $this->receivableService->paginate($request->string('status')->toString() ?: null);
        $accounts = Account::active()->orderBy('account_name')->get();

        return view('receivables.index', compact('receivables', 'accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', Receivable::class);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('receivables.create', compact('accounts'));
    }

    public function store(StoreReceivableRequest $request): RedirectResponse
    {
        $this->receivableService->create($request->validated(), $request->user());

        return redirect()->route('receivables.index')->with('success', 'Piutang berhasil dicatat.');
    }

    public function edit(Receivable $receivable): View
    {
        $this->authorize('update', $receivable);

        $accounts = Account::active()->orderBy('account_name')->get();
        $receivable->load('payments');

        return view('receivables.edit', compact('receivable', 'accounts'));
    }

    public function update(UpdateReceivableRequest $request, Receivable $receivable): RedirectResponse
    {
        $this->receivableService->update($receivable, $request->validated());

        return redirect()->route('receivables.index')->with('success', 'Piutang berhasil diperbarui.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        $this->authorize('delete', $receivable);

        try {
            $this->receivableService->delete($receivable);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('receivables.index')->with('success', 'Piutang berhasil dihapus.');
    }

    public function pay(StoreReceivablePaymentRequest $request, Receivable $receivable): RedirectResponse
    {
        try {
            $this->receivableService->recordPayment($receivable, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('receivables.edit', $receivable)->with('success', 'Penerimaan piutang berhasil dicatat.');
    }
}
