<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     * 角色权限关联表
     * @return void
     */
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->bigInteger('role_id')->comment('角色ID（关联roles表）');
            $table->bigInteger('permission_id')->comment('权限ID（关联permissions表）');
            $table->timestamps();
            
            // 唯一约束
            $table->unique(['role_id', 'permission_id'], 'uk_role_permission');
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
}
