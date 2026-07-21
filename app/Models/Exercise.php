<?php

namespace App\Models;

use App\Modules\Exercises\Support\ExerciseData;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_seconds',
        'sets',
        'repetitions',
        'material_url',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'sets' => 'integer',
            'repetitions' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    protected function formattedDuration(): Attribute
    {
        return Attribute::get(fn (): string => ExerciseData::formatDuration($this->duration_seconds));
    }
}
