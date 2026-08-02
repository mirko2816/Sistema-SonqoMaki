<?php

namespace App\Models;

use App\Modules\Reminders\Enums\ReminderOutcome;
use App\Modules\Reminders\Enums\ReminderReasonCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderExecution extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scheduled_local_date' => 'date',
            'scheduled_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'outcome' => ReminderOutcome::class,
            'reason_code' => ReminderReasonCode::class,
            'provider_http_status' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ReminderSchedule::class, 'reminder_schedule_id')->withTrashed();
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class)->withTrashed();
    }

    public function publicLink(): BelongsTo
    {
        return $this->belongsTo(PublicLink::class, 'public_link_id');
    }
}
