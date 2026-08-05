<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('document_number')->unique();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('mutation_log_id')->nullable();
            $table->unsignedBigInteger('recipient_employee_id')->nullable();
            $table->date('document_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('data')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('document_type');
            $table->foreign('asset_id')->references('id')->on('assets')->nullOnDelete();
            $table->foreign('mutation_log_id')->references('id')->on('asset_mutation_logs')->nullOnDelete();
            $table->foreign('recipient_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_documents');
    }
};
