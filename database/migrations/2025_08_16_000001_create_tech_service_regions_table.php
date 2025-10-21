<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTechServiceRegionsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建科技服务地区表 - 三级结构：申请类型 -> 科技服务名称 -> 科服地区
     * @return void
     */
    public function up()
    {
        Schema::create('tech_service_regions', function (Blueprint $table) {
            $table->id();
            $table->string('apply_type')->comment('申请类型');
            $table->string('service_name')->comment('科技服务名称');
            $table->string('service_level')->nullable()->comment('科服级别');
            $table->string('main_area')->comment('主管地');
            $table->string('project_year')->comment('申报年份');
            $table->date('deadline')->nullable()->comment('申报截止日');
            $table->integer('batch_number')->default(1)->comment('申报批次');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->integer('sort_order')->default(1)->comment('排序');
            $table->string('updater')->nullable()->comment('更新人');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->timestamps();

            // 添加索引
            $table->index(['apply_type']);
            $table->index(['service_name']);
            $table->index(['main_area']);
            $table->index(['project_year']);
            $table->index(['status']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tech_service_regions');
    }
}
