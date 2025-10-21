<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCustomerScalesTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_scales', function (Blueprint $table) {
            // 检查字段是否存在再删除
            if (Schema::hasColumn('customer_scales', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('customer_scales', 'min_employees')) {
                $table->dropColumn('min_employees');
            }
            if (Schema::hasColumn('customer_scales', 'max_employees')) {
                $table->dropColumn('max_employees');
            }
            if (Schema::hasColumn('customer_scales', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('customer_scales', function (Blueprint $table) {
            // 重命名字段以匹配前端
            if (Schema::hasColumn('customer_scales', 'name')) {
                $table->renameColumn('name', 'scale_name');
            }
            if (Schema::hasColumn('customer_scales', 'status')) {
                $table->renameColumn('status', 'is_valid');
            }
            if (Schema::hasColumn('customer_scales', 'sort_order')) {
                $table->renameColumn('sort_order', 'sort');
            }
        });

        // 使用原生SQL进行类型转换（PostgreSQL）
        DB::statement('ALTER TABLE customer_scales ALTER COLUMN is_valid DROP DEFAULT');
        DB::statement('ALTER TABLE customer_scales ALTER COLUMN is_valid TYPE BOOLEAN USING (is_valid = 1)');
        DB::statement('ALTER TABLE customer_scales ALTER COLUMN is_valid SET DEFAULT true');
        DB::statement('ALTER TABLE customer_scales ALTER COLUMN sort SET DEFAULT 1');

        Schema::table('customer_scales', function (Blueprint $table) {
            // 添加用户跟踪字段
            if (!Schema::hasColumn('customer_scales', 'updated_by')) {
                $table->string('updated_by')->nullable()->comment('更新人');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_scales', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('scale_name', 'name');
            $table->renameColumn('is_valid', 'status');
            $table->renameColumn('sort', 'sort_order');
            
            $table->tinyInteger('status')->default(1)->change();
            $table->integer('sort_order')->default(0)->change();
            
            $table->string('code', 50)->unique()->comment('规模编码');
            $table->integer('min_employees')->default(0)->comment('最小员工数');
            $table->integer('max_employees')->nullable()->comment('最大员工数');
            $table->text('description')->nullable()->comment('描述');
            
            $table->dropColumn('updated_by');
        });
    }
}
