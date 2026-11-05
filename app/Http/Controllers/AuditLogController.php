<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user:id,name')
            ->when($request->string('module')->toString(), fn (Builder $q, $m) => $q->where('module', $m))
            ->when($request->string('action')->toString(), fn (Builder $q, $a) => $q->where('action', $a))
            ->when($request->integer('user_id') ?: null, fn (Builder $q, $u) => $q->where('user_id', $u))
            ->when($request->string('date_from')->toString(), fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->string('date_to')->toString(), fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $modules = AuditLog::query()->distinct()->orderBy('module')->pluck('module');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('audit-logs.index', compact('logs', 'modules', 'users'));
    }
}
