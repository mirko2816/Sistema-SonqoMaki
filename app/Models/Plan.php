<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = ['patient_id', 'name', 'starts_on', 'ends_on', 'status'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'deleted_at' => 'datetime'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class)->orderBy('starts_on')->orderBy('id');
    }

    public function publicLinks(): HasMany
    {
        return $this->hasMany(PublicLink::class);
    }

    public function currentPublicLink(): HasOne
    {
        return $this->hasOne(PublicLink::class)->whereNull('revoked_at');
    }
}
