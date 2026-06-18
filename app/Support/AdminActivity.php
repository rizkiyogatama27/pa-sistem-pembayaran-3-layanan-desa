<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;

class AdminActivity
{
    public static function log(string $module, string $action, ?string $description = null, array $metadata = []): void
    {
        if (! Auth::check() || Auth::user()?->role !== 'admin') {
            return;
        }

        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
