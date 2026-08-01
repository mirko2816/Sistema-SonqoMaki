<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 320);
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();
        });

        DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_lowercase_check CHECK (email = lower(email))');
        DB::statement('CREATE UNIQUE INDEX users_email_lower_unique ON users (lower(email))');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
