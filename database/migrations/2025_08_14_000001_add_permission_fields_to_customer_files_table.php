<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermissionFieldsToCustomerFilesTable extends Migration
{
    /**
     * Run the migrations.
     * 为客户文件表添加权限相关字段
     * @return void
     */
    public function up()
    {
        Schema::table('customer_files', function (Blueprint $table) {
            // 添加权限类型字段
            $table->string('permission_type', 20)->default('public')->comment('权限类型：public-公开，department-部门，private-私有')->after('is_private');
            
            // 添加允许访问的部门列表（JSON格式）
            $table->json('allowed_departments')->nullable()->comment('允许访问的部门列表')->after('permission_type');
            
            // 添加允许访问的用户列表（JSON格式）
            $table->json('allowed_users')->nullable()->comment('允许访问的用户列表')->after('allowed_departments');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_files', function (Blueprint $table) {
            $table->dropColumn(['permission_type', 'allowed_departments', 'allowed_users']);
        });
    }
}
