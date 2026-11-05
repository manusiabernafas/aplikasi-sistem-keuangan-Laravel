<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Account;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function edit(): View
    {
        $settings = $this->settingService->all();
        $accounts = Account::orderBy('account_name')->get();

        return view('settings.edit', compact('settings', 'accounts'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['app.notifications_enabled'] = $request->boolean('app.notifications_enabled') ? '1' : '0';

        $this->settingService->updateMany($data);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
