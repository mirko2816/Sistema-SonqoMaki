<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReminderSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = ['reminder_configuration_id', 'weekday', 'send_at'];

    protected function casts(): array
    {
        return ['weekday' => 'integer', 'deleted_at' => 'datetime'];
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ReminderConfiguration::class, 'reminder_configuration_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ReminderExecution::class);
    }
}
