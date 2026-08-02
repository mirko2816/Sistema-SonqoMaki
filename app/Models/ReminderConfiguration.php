<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderConfiguration extends Model
{
    protected $fillable = ['plan_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReminderSchedule::class)->orderBy('weekday')->orderBy('send_at')->orderBy('id');
    }
}
