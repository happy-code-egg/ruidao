<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusFromRolesTable extends Migration
{
    /**
     * Run the migrations.
     * 移除角色表的状态字段
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            // 移除状态字段
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     * 恢复状态字段
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            // 恢复状态字段
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用')->after('description');
        });
    }
}
