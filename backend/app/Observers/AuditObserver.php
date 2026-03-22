<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        AuditService::log('CREATE', $model, [], $model->toArray());
    }

    public function updated(Model $model): void
    {
        AuditService::log(
            'UPDATE',
            $model,
            $model->getOriginal(),
            $model->getDirty()
        );
    }

    public function deleted(Model $model): void
    {
        AuditService::log('DELETE', $model, $model->toArray(), []);
    }
}