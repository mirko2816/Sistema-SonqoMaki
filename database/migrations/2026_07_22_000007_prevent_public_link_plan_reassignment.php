<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION prevent_public_link_plan_reassignment() RETURNS trigger AS $$
            BEGIN
                IF NEW.plan_id <> OLD.plan_id THEN
                    RAISE EXCEPTION 'public_links.plan_id is immutable' USING ERRCODE = '23514';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        SQL);
        DB::statement('CREATE TRIGGER public_links_plan_id_immutable BEFORE UPDATE OF plan_id ON public_links FOR EACH ROW EXECUTE FUNCTION prevent_public_link_plan_reassignment()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS public_links_plan_id_immutable ON public_links');
        DB::statement('DROP FUNCTION IF EXISTS prevent_public_link_plan_reassignment()');
    }
};
