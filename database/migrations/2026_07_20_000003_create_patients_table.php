<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_names', 120);
            $table->string('last_names', 120);
            $table->string('dni', 8)->nullable()->unique();
            $table->string('whatsapp_phone', 12)->unique();
            $table->string('status', 20)->default('active')->index();
            $table->date('whatsapp_consented_on')->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz()->index();
            $table->index('last_names');
            $table->index('first_names');
        });

        DB::statement("ALTER TABLE patients ADD CONSTRAINT patients_first_names_not_blank CHECK (btrim(first_names) <> '')");
        DB::statement("ALTER TABLE patients ADD CONSTRAINT patients_last_names_not_blank CHECK (btrim(last_names) <> '')");
        DB::statement("ALTER TABLE patients ADD CONSTRAINT patients_dni_format_check CHECK (dni IS NULL OR dni ~ '^[0-9]{8}$')");
        DB::statement("ALTER TABLE patients ADD CONSTRAINT patients_whatsapp_phone_format_check CHECK (whatsapp_phone ~ '^\\+51[0-9]{9}$')");
        DB::statement("ALTER TABLE patients ADD CONSTRAINT patients_status_check CHECK (status IN ('active', 'inactive'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
