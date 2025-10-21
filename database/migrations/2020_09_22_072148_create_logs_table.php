<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 系统日志表
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->comment('用户id');
            $table->integer('type')->comment('操作类型');
            $table->text('content')->comment('记录内容');
            $table->string('title')->comment('标题')->nullable();
            $table->string('method')->comment('方法')->nullable();
            $table->string('request_method')->comment('请求方法')->nullable();
            $table->string('ip_address')->comment('IP地址')->nullable();
            $table->string('location')->comment('位置')->nullable();
            $table->string('url')->comment('URL')->nullable();
            $table->text('request_param')->comment('请求参数')->nullable();
            $table->text('json_result')->comment('JSON结果')->nullable();
            $table->text('error_msg')->comment('错误信息')->nullable();
            $table->integer('status')->comment('状态')->nullable();
            $table->integer('execution_time')->comment('执行时间')->nullable();
            $table->string('user_agent')->comment('用户代理')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
