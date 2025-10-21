<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 用户表
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('用户ID');
            $table->string('username', 50)->comment('用户名')->unique();
            $table->string('password')->comment('密码');
            $table->string('real_name', 50)->comment('真实姓名');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->string('avatar_url', 255)->nullable()->comment('头像URL');
            $table->bigInteger('department_id')->nullable()->comment('部门ID（关联departments表）');
            $table->string('position', 50)->nullable()->comment('职位');
            $table->string('employee_no', 50)->nullable()->comment('员工编号');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->timestamp('last_login_time')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->comment('最后登录IP');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
