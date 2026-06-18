<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $module = trim((string) $request->query('module', ''));

        $logs = AdminActivityLog::query()
            ->with('user')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('action', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($module !== '', fn ($query) => $query->where('module', $module))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $moduleOptions = AdminActivityLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        return view('admin.activity.index', compact('logs', 'q', 'module', 'moduleOptions'));
    }
}
