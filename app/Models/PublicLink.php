<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicLink extends Model
{
    protected $fillable = ['token_hash', 'token_ciphertext', 'token_prefix', 'revoked_at'];

    protected $hidden = ['token_hash', 'token_ciphertext'];

    protected function casts(): array
    {
        return ['revoked_at' => 'datetime'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
