<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCustomersTableDuplicateFields extends Migration
{
    /**
     * Run the migrations.
     * 修复customers表重复字段问题并确保前端字段完整性
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 检查并添加缺失的字段，避免重复添加
            $columns = Schema::getColumnListing('customers');
            
            // 处理公司类型字段（如果不存在则添加）
            if (!in_array('company_type', $columns)) {
                $table->string('company_type', 100)->nullable()->comment('企业类型');
            }
            
            // 确保前端需要的创建人/更新人字段别名存在
            if (!in_array('creator', $columns)) {
                $table->string('creator', 100)->nullable()->comment('创建人姓名');
            }
            if (!in_array('updater', $columns)) {
                $table->string('updater', 100)->nullable()->comment('更新人姓名');
            }
            
            // 确保时间字段的字符串版本存在（用于前端显示）
            if (!in_array('create_date', $columns)) {
                $table->string('create_date', 20)->nullable()->comment('创建日期字符串');
            }
            if (!in_array('create_time', $columns)) {
                $table->string('create_time', 30)->nullable()->comment('创建时间字符串');
            }
            if (!in_array('update_time', $columns)) {
                $table->string('update_time', 30)->nullable()->comment('更新时间字符串');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 删除添加的字段
            $columns = Schema::getColumnListing('customers');
            
            $fieldsToRemove = ['company_type', 'creator', 'updater', 'create_date', 'create_time', 'update_time'];
            $existingFields = array_intersect($fieldsToRemove, $columns);
            
            if (!empty($existingFields)) {
                $table->dropColumn($existingFields);
            }
        });
    }
}
