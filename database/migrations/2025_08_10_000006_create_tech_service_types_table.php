<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTechServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     * 创建科技服务类型表
     * @return void
     */
    public function up()
    {
        Schema::create('tech_service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('科技服务名称');
            $table->string('code')->comment('科技服务编码')->nullable();
            $table->string('apply_type')->comment('申请类型');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态 0=无效 1=有效');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('updater')->nullable()->comment('更新人');
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
        Schema::dropIfExists('tech_service_types');
    }
}
