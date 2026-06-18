<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MeterReading;
use App\Models\AdminActivityLog;

class ScheduleMeterAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $readingId;

    public function __construct($readingId)
    {
        $this->readingId = $readingId;
    }

    public function handle()
    {
        $reading = MeterReading::find($this->readingId);
        if (! $reading) return;

        // For now, just log an admin activity indicating audit should be performed.
        AdminActivityLog::create([
            'user_id' => null,
            'module' => 'meter_reading',
            'action' => 'audit_scheduled',
            'description' => 'Audit scheduled for MeterReading #' . $reading->id,
            'metadata' => ['reading_id' => $reading->id],
        ]);

        // In future: notify petugas via WhatsApp/email and set audit scheduling fields.
    }
}
