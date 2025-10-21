<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusFromDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     * 删除部门表的状态字段
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     * 恢复部门表的状态字段
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用')->after('sort_order');
        });
    }
}
