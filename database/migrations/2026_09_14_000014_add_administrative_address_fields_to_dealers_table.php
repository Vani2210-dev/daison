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
        Schema::table('dealers', function (Blueprint $table) {
            // Các trường địa giới hành chính Cũ & Mới theo yêu cầu
            $table->string('old_ward')->nullable()->after('address');       // Xã/Phường cũ
            $table->string('old_district')->nullable()->after('old_ward');   // Quận/Huyện cũ
            $table->string('old_city')->nullable()->after('old_district');   // Tỉnh/TP cũ
            
            $table->string('new_ward')->nullable()->after('old_city');       // Xã/Phường mới
            $table->string('new_district')->nullable()->after('new_ward');   // Quận/Huyện mới
            $table->string('new_city')->nullable()->after('new_district');   // Tỉnh/TP mới

            // Cho phép lat, lng nullable (bỏ định vị gps bắt buộc)
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn([
                'old_ward',
                'old_district',
                'old_city',
                'new_ward',
                'new_district',
                'new_city',
            ]);
        });
    }
};
