<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Transaction;
use App\Services\AttachmentService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly AttachmentService $attachmentService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Transaction::class);

        $transactions = $this->transactionService->paginate([
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: null,
            'account_id' => $request->integer('account_id') ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'amount_min' => $request->input('amount_min') ?: null,
            'amount_max' => $request->input('amount_max') ?: null,
            'sort' => $request->string('sort')->toString() ?: null,
            'direction' => $request->string('direction')->toString() ?: null,
        ]);

        $accounts = Account::orderBy('account_name')->get(['id', 'account_name']);
        $categories = Category::with('parent')->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    public function createIncome(): View
    {
        $this->authorize('create', Transaction::class);

        $accounts = Account::active()->orderBy('account_name')->get();
        $categories = Category::ofType('INCOME')->active()->with('parent')->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('transactions.income.create', compact('accounts', 'categories', 'tags'));
    }

    public function storeIncome(StoreIncomeRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->transactionService->createIncome($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($request->hasFile('attachment')) {
            $this->attachmentService->store($request->file('attachment'), $transaction, $request->user());
        }

        return redirect()->route('transactions.index')->with('success', "Pemasukan {$transaction->transaction_number} berhasil dicatat.");
    }

    public function createExpense(): View
    {
        $this->authorize('create', Transaction::class);

        $accounts = Account::active()->orderBy('account_name')->get();
        $categories = Category::ofType('EXPENSE')->active()->with('parent')->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('transactions.expense.create', compact('accounts', 'categories', 'tags'));
    }

    public function storeExpense(StoreExpenseRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->transactionService->createExpense($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($request->hasFile('attachment')) {
            $this->attachmentService->store($request->file('attachment'), $transaction, $request->user());
        }

        return redirect()->route('transactions.index')->with('success', "Pengeluaran {$transaction->transaction_number} berhasil dicatat.");
    }

    public function createTransfer(): View
    {
        $this->authorize('create', Transaction::class);

        $accounts = Account::active()->orderBy('account_name')->get();

        return view('transactions.transfer.create', compact('accounts'));
    }

    public function storeTransfer(StoreTransferRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->transactionService->createTransfer($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($request->hasFile('attachment')) {
            $this->attachmentService->store($request->file('attachment'), $transaction, $request->user());
        }

        return redirect()->route('transactions.index')->with('success', "Transfer {$transaction->transaction_number} berhasil dicatat.");
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $accounts = Account::active()->orderBy('account_name')->get();

        if ($transaction->transaction_type === Transaction::TYPE_TRANSFER) {
            return view('transactions.transfer.edit', compact('transaction', 'accounts'));
        }

        $typeCode = $transaction->transaction_type === Transaction::TYPE_INCOME ? 'INCOME' : 'EXPENSE';
        $categories = Category::ofType($typeCode)->active()->with('parent')->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $transaction->load('tags:id');

        return view('transactions.simple-edit', compact('transaction', 'accounts', 'categories', 'tags'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        try {
            $this->transactionService->update($transaction, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        try {
            $this->transactionService->delete($transaction, $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $this->authorize('create', Transaction::class);

        $data = $request->validate([
            'transaction_type' => ['required', 'in:INCOME,EXPENSE'],
            'transaction_date' => ['required', 'date'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $transaction = $data['transaction_type'] === 'INCOME'
                ? $this->transactionService->createIncome($data, $request->user())
                : $this->transactionService->createExpense($data, $request->user());
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => "Transaksi {$transaction->transaction_number} berhasil disimpan.",
            'transaction' => $transaction->only(['id', 'transaction_number', 'amount', 'transaction_type']),
        ]);
    }
}
