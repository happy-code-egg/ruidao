<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationLogsTable extends Migration
{
    /**
     * Run the migrations.
     * 操作日志表
     * @return void
     */
    public function up()
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('日志ID');
            $table->bigInteger('user_id')->nullable()->comment('操作用户ID');
            $table->string('username', 50)->nullable()->comment('用户名');
            $table->string('operation_type', 50)->nullable()->comment('操作类型');
            $table->string('operation_name', 200)->nullable()->comment('操作名称');
            $table->string('method', 10)->nullable()->comment('请求方式');
            $table->string('request_url', 500)->nullable()->comment('请求URL');
            $table->jsonb('request_params')->nullable()->comment('请求参数');
            $table->jsonb('response_data')->nullable()->comment('响应数据');
            $table->string('business_type', 50)->nullable()->comment('业务类型');
            $table->bigInteger('business_id')->nullable()->comment('业务ID');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('用户代理');
            $table->integer('execution_time')->nullable()->comment('执行时间（毫秒）');
            $table->smallInteger('status')->nullable()->comment('操作状态：0-失败，1-成功');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamps();
       
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_logs');
    }
}
