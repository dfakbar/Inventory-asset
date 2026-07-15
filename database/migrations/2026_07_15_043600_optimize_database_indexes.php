<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('asset_locations');

        Schema::table('assets', function (Blueprint $table) {
            $table->index('location_id', 'idx_assets_location_id');
            $table->index('vendor_id', 'idx_assets_vendor_id');
            $table->index('brand_id', 'idx_assets_brand_id');
            $table->index('assigned_to', 'idx_assets_assigned_to');
        });

        Schema::table('asset_loans', function (Blueprint $table) {
            $table->index('asset_id', 'idx_asset_loans_asset_id');
            $table->index('created_by', 'idx_asset_loans_created_by');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_assets_location_id');
            $table->dropIndex('idx_assets_vendor_id');
            $table->dropIndex('idx_assets_brand_id');
            $table->dropIndex('idx_assets_assigned_to');
        });

        Schema::table('asset_loans', function (Blueprint $table) {
            $table->dropIndex('idx_asset_loans_asset_id');
            $table->dropIndex('idx_asset_loans_created_by');
        });
    }
};
