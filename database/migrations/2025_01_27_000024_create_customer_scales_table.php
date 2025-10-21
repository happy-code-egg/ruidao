<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerScalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('规模名称');
            $table->string('code', 50)->unique()->comment('规模编码');
            $table->integer('min_employees')->default(0)->comment('最小员工数');
            $table->integer('max_employees')->nullable()->comment('最大员工数');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->integer('created_by')->default(1)->comment('创建人');
            $table->integer('updated_by')->default(1)->comment('更新人');
            $table->timestamps();
            
            // 添加索引
            $table->index(['status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_scales');
    }
}
