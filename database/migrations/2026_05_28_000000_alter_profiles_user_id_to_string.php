<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profiles') || ! Schema::hasColumn('profiles', 'user_id')) {
            return;
        }

        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            $column = DB::selectOne(
                "SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'profiles' AND column_name = 'user_id'"
            );

            if ($column && in_array($column->data_type, ['character varying', 'varchar', 'text'], true)) {
                return;
            }

            DB::statement('ALTER TABLE profiles ALTER COLUMN user_id TYPE VARCHAR(36) USING user_id::text');

            return;
        }

        if ($connection->getDriverName() === 'sqlite') {
            $column = collect(DB::select('PRAGMA table_info(profiles)'))
                ->firstWhere('name', 'user_id');

            if ($column && str_contains(strtolower((string) $column->type), 'char')) {
                return;
            }
        }
    }

    public function down(): void
    {
        // Sem reversão automática — evita perda de UUIDs já persistidos.
    }
};
