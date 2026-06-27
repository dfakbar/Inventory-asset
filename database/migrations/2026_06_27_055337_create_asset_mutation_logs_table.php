<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel log riwayat mutasi (perpindahan) aset.
 * Setiap kali location_id atau mutation_date aset diubah, satu baris baru dicatat di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_mutation_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                  ->constrained('assets')
                  ->cascadeOnDelete()
                  ->comment('Aset yang dimutasi');

            $table->foreignId('performed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('User yang mencatat mutasi');

            // Lokasi asal & tujuan
            $table->foreignId('from_location_id')
                  ->nullable()
                  ->constrained('locations')
                  ->nullOnDelete()
                  ->comment('Lokasi asal (sebelum mutasi)');

            $table->foreignId('to_location_id')
                  ->nullable()
                  ->constrained('locations')
                  ->nullOnDelete()
                  ->comment('Lokasi tujuan (setelah mutasi)');

            // User penanggung jawab aset sebelum & sesudah
            $table->foreignId('from_assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('PIC aset sebelum mutasi');

            $table->foreignId('to_assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('PIC aset setelah mutasi');

            // Status sebelum & sesudah
            $table->string('from_status', 30)->nullable()->comment('Status aset sebelum mutasi');
            $table->string('to_status', 30)->nullable()->comment('Status aset setelah mutasi');

            $table->date('mutation_date')->nullable()->comment('Tanggal aktual perpindahan');
            $table->text('notes')->nullable()->comment('Catatan tambahan mutasi');

            $table->timestamps();

            // Index untuk performa
            $table->index('asset_id');
            $table->index('performed_by');
            $table->index('mutation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_mutation_logs');
    }
};
