<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseCoefficientsTable extends Migration
{
    /**
     * Run the migrations.
     * 项目系数表
     * @return void
     */
    public function up()
    {
        Schema::create('case_coefficients', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            $table->string('name', 100)->comment('项目系数名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->bigInteger('created_by')->nullable()->comment('创建人');
            $table->bigInteger('updated_by')->nullable()->comment('更新人');
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
        Schema::dropIfExists('case_coefficients');
    }
}
