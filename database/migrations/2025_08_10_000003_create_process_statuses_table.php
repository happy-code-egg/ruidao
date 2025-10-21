<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessStatusesTable extends Migration
{
    /**
     * Run the migrations.
     * 创建处理事项状态表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('process_statuses', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('status_name', 100)->comment('处理事项状态名称');
            $table->string('status_code', 50)->comment('处理状态代码')->nullable();
            $table->tinyInteger('trigger_rule')->default(0)->comment('是否触发规则(1是0否)');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->string('updater', 100)->nullable()->comment('更新人');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 添加索引
            $table->index(['status', 'sort_order']);
            $table->index(['sort']);
            $table->index(['status_name']);
            $table->index(['is_valid']);
            $table->index(['trigger_rule']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_statuses');
    }
}
