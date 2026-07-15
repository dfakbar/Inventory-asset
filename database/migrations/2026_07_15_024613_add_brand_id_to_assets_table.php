<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('brand_id')
                  ->nullable()
                  ->after('asset_category_id')
                  ->constrained('brands')
                  ->nullOnDelete();
        });

        // Migrasi data: isi brand_id berdasarkan nama brand
        DB::statement('UPDATE assets SET brand_id = (SELECT id FROM brands WHERE brands.name = assets.brand) WHERE brand IS NOT NULL');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('asset_category_id');
        });

        // Migrasi balik data
        DB::statement('UPDATE assets SET brand = (SELECT name FROM brands WHERE brands.id = assets.brand_id) WHERE brand_id IS NOT NULL');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
