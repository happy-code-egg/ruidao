<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionTypesTable extends Migration
{
    public function up()
    {
        Schema::create('commission_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('提成类型名称');
            $table->string('code', 50)->comment('提成类型编码')->nullable();
            $table->decimal('rate', 5, 2)->comment('比例')->nullable();
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->integer('created_by')->nullable()->comment('创建人');
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('commission_types');
    }
}
