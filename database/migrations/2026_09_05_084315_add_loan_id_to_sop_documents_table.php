<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_id')->nullable()->after('mutation_log_id');
            $table->index('loan_id');
            $table->foreign('loan_id')->references('id')->on('asset_loans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->dropForeign(['loan_id']);
            $table->dropIndex(['loan_id']);
            $table->dropColumn('loan_id');
        });
    }
};
