<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(string $action, string $module, ?int $recordId, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }

    public function created(Model $model, string $module): void
    {
        $this->log('created', $module, $model->getKey(), null, $this->sanitize($model->getAttributes()));
    }

    public function updated(Model $model, string $module, array $originalValues): void
    {
        $changed = array_intersect_key($model->getAttributes(), $model->getChanges());

        if (empty($changed)) {
            return;
        }

        $this->log(
            'updated',
            $module,
            $model->getKey(),
            $this->sanitize(array_intersect_key($originalValues, $changed)),
            $this->sanitize($changed)
        );
    }

    public function deleted(Model $model, string $module): void
    {
        $this->log('deleted', $module, $model->getKey(), $this->sanitize($model->getAttributes()), null);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitize(array $values): array
    {
        unset($values['password'], $values['remember_token']);

        return $values;
    }
}
