<?php

namespace App\Actions\ActivityLog;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RecordActivity
{
    public function handle(string $action, ?Model $model = null, ?string $description = null, ?Request $request = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $request?->user()?->id ?? auth()->id(),
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'ip_address' => $request?->ip() ?? request()?->ip(),
        ]);
    }
}
