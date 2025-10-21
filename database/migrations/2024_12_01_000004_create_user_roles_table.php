<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     * 用户角色关联表
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->bigInteger('user_id')->comment('用户ID（关联users表）');
            $table->bigInteger('role_id')->comment('角色ID（关联roles表）');
            $table->timestamps();
            
            // 唯一约束
            $table->unique(['user_id', 'role_id'], 'uk_user_role');
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
}
