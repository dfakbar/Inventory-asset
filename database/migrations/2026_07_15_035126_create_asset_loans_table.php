<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('borrower_name', 200);
            $table->string('borrower_email', 150)->nullable();
            $table->date('loan_date');
            $table->date('expected_return_date')->nullable();
            $table->date('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('loan_date');
            $table->index('returned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
    }
};
