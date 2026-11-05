<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\AccountType;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);

        $accounts = $this->accountService->paginate([
            'search' => $request->string('search')->toString() ?: null,
            'account_type_id' => $request->integer('account_type_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ]);

        $accountTypes = AccountType::orderBy('name')->get();
        $totalBalance = Account::active()->sum('current_balance');

        return view('accounts.index', compact('accounts', 'accountTypes', 'totalBalance'));
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        $accountTypes = AccountType::where('is_active', true)->orderBy('name')->get();

        return view('accounts.create', compact('accountTypes'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->validated(), $request->user());

        return redirect()->route('accounts.index')->with('success', 'Account berhasil ditambahkan.');
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        $accountTypes = AccountType::orderBy('name')->get();

        return view('accounts.edit', compact('account', 'accountTypes'));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->accountService->update($account, $request->validated(), $request->user());

        return redirect()->route('accounts.index')->with('success', 'Account berhasil diperbarui.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        try {
            $this->accountService->delete($account);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('accounts.index')->with('success', 'Account berhasil dihapus.');
    }
}
