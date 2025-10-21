<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLogsTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     * 
     * 更新日志表以支持前端日志管理页面的需求
     * @return void
     */
    public function up()
    {
        Schema::table('logs', function (Blueprint $table) {
            // 添加前端需要的字段（排除已存在的ip_address）
            $table->string('title', 200)->nullable()->comment('操作标题')->after('content');
            $table->string('method', 100)->nullable()->comment('操作方法')->after('title');
            $table->string('request_method', 10)->nullable()->comment('请求方式')->after('method');
            $table->string('location', 200)->nullable()->comment('操作地点')->after('ip_address');
            $table->string('url', 500)->nullable()->comment('请求URL')->after('location');
            $table->text('request_param')->nullable()->comment('请求参数')->after('url');
            $table->text('json_result')->nullable()->comment('响应结果')->after('request_param');
            $table->text('error_msg')->nullable()->comment('错误信息')->after('json_result');
            $table->smallInteger('status')->default(1)->comment('操作状态：0-失败，1-成功')->after('error_msg');
            $table->integer('execution_time')->nullable()->comment('执行时间（毫秒）')->after('status');

            // 添加索引
            $table->index('status');
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'method',
                'request_method',
                'location',
                'url',
                'request_param',
                'json_result',
                'error_msg',
                'status',
                'execution_time'
            ]);
        });
    }
}
