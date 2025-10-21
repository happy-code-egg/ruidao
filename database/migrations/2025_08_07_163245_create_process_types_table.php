<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessTypesTable extends Migration
{
    public function up()
    {
        Schema::create('process_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('处理事项类型名称');
            $table->string('code', 50)->comment('处理事项类型编码')->nullable();
            $table->string('category', 50)->comment('分类')->nullable();
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('process_types');
    }
}
