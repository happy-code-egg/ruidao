<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     * 部门表
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('部门ID');
            $table->string('department_code', 50)->unique()->comment('部门编码');
            $table->string('department_name', 100)->comment('部门名称');
            $table->bigInteger('parent_id')->default(0)->comment('父部门ID（关联departments表）');
            $table->string('level_path', 500)->nullable()->comment('层级路径');
            $table->bigInteger('manager_id')->nullable()->comment('部门负责人ID（关联users表）');
            $table->text('description')->nullable()->comment('部门描述');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();
       
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
