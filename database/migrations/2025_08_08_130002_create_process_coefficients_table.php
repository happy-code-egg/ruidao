<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessCoefficientsTable extends Migration
{
    /**
     * Run the migrations.
     * 处理事项系数表
     * @return void
     */
    public function up()
    {
        Schema::create('process_coefficients', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            $table->string('name', 100)->comment('处理事项系数名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->string('created_by', 100)->nullable()->comment('创建人');
            $table->string('updated_by', 100)->nullable()->comment('更新人');
            $table->timestamps();
            
            // 添加索引
            $table->index(['sort']);
            $table->index(['is_valid']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_coefficients');
    }
}
