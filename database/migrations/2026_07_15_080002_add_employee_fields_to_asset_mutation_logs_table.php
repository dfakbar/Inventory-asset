<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_mutation_logs', function (Blueprint $table) {
            $table->foreignId('from_employee_id')
                  ->nullable()
                  ->after('to_assigned_to')
                  ->constrained('employees')
                  ->nullOnDelete()
                  ->comment('Employee pengguna sebelum mutasi');

            $table->foreignId('to_employee_id')
                  ->nullable()
                  ->after('from_employee_id')
                  ->constrained('employees')
                  ->nullOnDelete()
                  ->comment('Employee pengguna setelah mutasi');
        });
    }

    public function down(): void
    {
        Schema::table('asset_mutation_logs', function (Blueprint $table) {
            $table->dropForeign(['from_employee_id']);
            $table->dropForeign(['to_employee_id']);
            $table->dropColumn(['from_employee_id', 'to_employee_id']);
        });
    }
};
