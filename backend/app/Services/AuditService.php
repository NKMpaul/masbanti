<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        Model $model,
        array $oldValues = [],
        array $newValues = []
    ): void {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'model'      => class_basename($model),
            'model_id'   => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}