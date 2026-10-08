<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('creator_id')->nullable()->after('dealer_id')->constrained('users')->onDelete('set null');
            $table->string('order_creator')->nullable()->after('creator_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn(['creator_id', 'order_creator']);
        });
    }
};
