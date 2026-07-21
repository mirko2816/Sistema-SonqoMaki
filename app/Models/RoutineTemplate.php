<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutineTemplate extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['name', 'status'];

    protected function casts(): array
    {
        return ['deleted_at' => 'datetime'];
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(RoutineTemplateExercise::class)->orderBy('position');
    }
}
