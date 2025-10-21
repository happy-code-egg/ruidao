<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelatedTypesTable extends Migration
{
    public function up()
    {
        Schema::create('related_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('相关类型名称');
            $table->string('code', 50)->unique()->comment('相关类型编码');
            $table->string('category', 50)->comment('分类');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('related_types');
    }
}
