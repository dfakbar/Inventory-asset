<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type'); // 'addition' (penambahan/upgrade) or 'reduction' (pengurangan/pencopotan)
            $table->string('component_name'); // e.g. RAM, SSD, Battery
            $table->string('previous_spec')->nullable(); // e.g. 8GB DDR4
            $table->string('new_spec'); // e.g. 16GB DDR4
            $table->decimal('cost', 15, 2)->nullable();
            $table->date('maintenance_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
