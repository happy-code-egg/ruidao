<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('product_code', 100)->unique()->comment('产品编号');
            $table->string('project_type', 100)->comment('项目类型');
            $table->string('apply_type', 100)->comment('申请类型');
            $table->string('specification', 200)->nullable()->comment('细分规格');
            $table->string('product_name', 200)->comment('产品名称');
            $table->string('official_fee', 100)->nullable()->comment('参考官费');
            $table->string('standard_price', 100)->nullable()->comment('标准定价');
            $table->string('min_price', 100)->nullable()->comment('最低售价');
            $table->boolean('is_valid')->default(true)->comment('是否有效');
            $table->string('update_user', 100)->default('系统记录')->comment('更新人');
            $table->timestamps();
            
            // 索引
            $table->index(['project_type', 'apply_type']);
            $table->index('product_name');
            $table->index('is_valid');
            $table->index('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
