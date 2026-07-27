<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peripheral_issuances', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('issued_by')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peripheral_issuances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
