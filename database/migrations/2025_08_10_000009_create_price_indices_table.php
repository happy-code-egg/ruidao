<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceIndicesTable extends Migration
{
    /**
     * Run the migrations.
     * 创建价格指数表
     * @return void
     */
    public function up()
    {
        Schema::create('price_indices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('价格指数名称');
            $table->string('code', 50)->comment('价格指数编码')->nullable();
            $table->string('index_name', 200)->comment('指数名称');
            $table->decimal('base_value', 10, 2)->nullable()->comment('基准值');
            $table->decimal('current_value', 10, 2)->nullable()->comment('当前值');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->integer('created_by')->nullable()->comment('创建人');
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
        Schema::dropIfExists('price_indices');
    }
}
