<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTechServiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建科技服务事项表
     * @return void
     */
    public function up()
    {
        Schema::create('tech_service_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tech_service_type_id')->comment('科技服务类型ID');
            $table->string('name')->comment('处理事项名称');
            $table->string('code')->unique()->comment('处理事项编码');
            $table->text('description')->nullable()->comment('事项描述');
            $table->date('expected_start_date')->nullable()->comment('预计启动日');
            $table->date('internal_deadline')->nullable()->comment('内部期限');
            $table->date('official_deadline')->nullable()->comment('官方期限');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('updater')->nullable()->comment('更新人');
            $table->timestamps();

            // 添加索引
            $table->index(['tech_service_type_id']);
            $table->index(['status', 'sort_order']);
            $table->index(['name']);
            $table->index(['code']);
            
            // 外键约束
            $table->foreign('tech_service_type_id')->references('id')->on('tech_service_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tech_service_items');
    }
}
