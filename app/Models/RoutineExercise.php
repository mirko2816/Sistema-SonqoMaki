<?php

namespace App\Models;

use App\Modules\Exercises\Support\ExerciseData;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutineExercise extends Model
{
    use SoftDeletes;

    protected $fillable = ['routine_id', 'source_exercise_id', 'position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url'];

    protected function casts(): array
    {
        return ['position' => 'integer', 'duration_seconds' => 'integer', 'sets' => 'integer', 'repetitions' => 'integer', 'deleted_at' => 'datetime'];
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function sourceExercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'source_exercise_id')->withTrashed();
    }

    protected function formattedDuration(): Attribute
    {
        return Attribute::get(fn (): string => ExerciseData::formatDuration($this->duration_seconds));
    }
}
