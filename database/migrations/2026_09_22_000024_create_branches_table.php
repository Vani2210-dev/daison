<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable()->default('Chi nhánh bán hàng');
            $table->string('manager')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial default branches
        $now = now();
        DB::table('branches')->insertOrIgnore([
            [
                'code' => 'MQ',
                'name' => 'Công Ty Chính Mê Linh (MQ)',
                'type' => 'Công ty chính',
                'manager' => 'Nguyễn Văn Hùng',
                'phone' => '0988 123 456',
                'address' => 'KCN Quang Minh, Mê Linh, Hà Nội',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MVQ',
                'name' => 'Chi Nhánh Vân Quần (MVQ)',
                'type' => 'Chi nhánh bán hàng',
                'manager' => 'Nguyễn Văn Định',
                'phone' => '0981 222 333',
                'address' => 'Vân Quần, Mê Linh, Hà Nội',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'DXH',
                'name' => 'Chi Nhánh Đông Anh (DXH)',
                'type' => 'Chi nhánh bán hàng',
                'manager' => 'Trần Văn Mạnh',
                'phone' => '0915 444 555',
                'address' => 'Thị Trấn Đông Anh, Hà Nội',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'VNH',
                'name' => 'Trụ sở Chính - Vinh',
                'type' => 'Công ty chính',
                'manager' => 'Trần Đại Sơn',
                'phone' => '0977 888 999',
                'address' => 'TP. Vinh, Nghệ An',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'NPH',
                'name' => 'Tổng Kho Vật Tư Nghi Phú',
                'type' => 'Kho trung chuyển',
                'manager' => 'Lê Văn Tài',
                'phone' => '0912 345 678',
                'address' => 'Nghi Phú, TP. Vinh, Nghệ An',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CLO',
                'name' => 'Chi Nhánh Cửa Lò',
                'type' => 'Chi nhánh bán hàng',
                'manager' => 'Hoàng Minh Tuấn',
                'phone' => '0934 567 890',
                'address' => 'Thị xã Cửa Lò, Nghệ An',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'HTH',
                'name' => 'Showroom Hà Tĩnh',
                'type' => 'Chi nhánh bán hàng',
                'manager' => 'Nguyễn Đình Phúc',
                'phone' => '0945 678 901',
                'address' => 'TP. Hà Tĩnh, Hà Tĩnh',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PVC',
                'name' => 'Nhà Máy Sản Xuất Ống PVC',
                'type' => 'Kho trung chuyển',
                'manager' => 'Đặng Vũ Nam',
                'phone' => '0967 890 123',
                'address' => 'KCN Bắc Vinh, Nghệ An',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
