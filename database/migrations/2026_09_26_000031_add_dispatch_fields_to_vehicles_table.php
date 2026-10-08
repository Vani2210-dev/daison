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
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'dispatch_status')) {
                $table->string('dispatch_status')->default('Rảnh')->after('status');
            }
            if (!Schema::hasColumn('vehicles', 'departure_time')) {
                $table->string('departure_time')->nullable()->after('dispatch_status');
            }
            if (!Schema::hasColumn('vehicles', 'current_route_name')) {
                $table->string('current_route_name')->nullable()->after('departure_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'current_route_name')) {
                $table->dropColumn('current_route_name');
            }
            if (Schema::hasColumn('vehicles', 'departure_time')) {
                $table->dropColumn('departure_time');
            }
            if (Schema::hasColumn('vehicles', 'dispatch_status')) {
                $table->dropColumn('dispatch_status');
            }
        });
    }
};
