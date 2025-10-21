<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProcessInformationsTableForMultipleSelection extends Migration
{
    /**
     * Run the migrations.
     * 更新处理事项信息表，支持多选字段
     * @return void
     */
    public function up()
    {
        Schema::table('process_informations', function (Blueprint $table) {
            // 添加新的 JSON 字段
            $table->json('business_type_new')->nullable()->comment('业务类型(多选)');
            $table->json('country_new')->nullable()->comment('国家（地区）(多选)');
        });

        // 迁移数据：将现有的字符串数据转换为数组格式
        \DB::statement("UPDATE process_informations SET business_type_new = json_build_array(business_type) WHERE business_type IS NOT NULL");
        \DB::statement("UPDATE process_informations SET country_new = json_build_array(country) WHERE country IS NOT NULL");

        Schema::table('process_informations', function (Blueprint $table) {
            // 删除旧字段
            $table->dropColumn(['business_type', 'country']);
        });

        Schema::table('process_informations', function (Blueprint $table) {
            // 重命名新字段
            $table->renameColumn('business_type_new', 'business_type');
            $table->renameColumn('country_new', 'country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_informations', function (Blueprint $table) {
            // 回滚时将字段改回原来的字符串类型
            $table->string('business_type', 100)->comment('业务类型')->change();
            $table->string('country', 100)->comment('国家（地区）')->change();
        });
    }
}
