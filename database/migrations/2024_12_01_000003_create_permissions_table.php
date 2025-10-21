<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     * 权限表
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('权限ID');
            $table->string('permission_code', 100)->unique()->comment('权限编码');
            $table->string('permission_name', 200)->comment('权限名称');
            $table->bigInteger('parent_id')->default(0)->comment('父权限ID（关联permissions表）');
            $table->smallInteger('permission_type')->comment('权限类型：1-菜单，2-按钮，3-数据');
            $table->string('resource_url', 500)->nullable()->comment('资源URL');
            $table->string('icon', 100)->nullable()->comment('图标');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
