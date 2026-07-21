<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'first_names',
        'last_names',
        'dni',
        'whatsapp_phone',
        'whatsapp_consented_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_consented_on' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => "$this->first_names $this->last_names");
    }
}
