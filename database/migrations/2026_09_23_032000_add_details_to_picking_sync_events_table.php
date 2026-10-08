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
        Schema::table('picking_sync_events', function (Blueprint $table) {
            $table->string('event_type')->default('ITEM_UPDATE')->after('performer_name');
            $table->string('assigned_staff')->nullable()->after('event_type');
            $table->text('assigned_staff_list')->nullable()->after('assigned_staff');
            $table->string('order_status')->nullable()->after('assigned_staff_list');
            $table->boolean('shortage_reported')->default(false)->after('order_status');
            $table->json('extra_data')->nullable()->after('shortage_reported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('picking_sync_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_type',
                'assigned_staff',
                'assigned_staff_list',
                'order_status',
                'shortage_reported',
                'extra_data'
            ]);
        });
    }
};
