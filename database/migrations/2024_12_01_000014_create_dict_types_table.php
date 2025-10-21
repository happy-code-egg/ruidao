<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictTypesTable extends Migration
{
    /**
     * Run the migrations.
     * 字典类型表
     * @return void
     */
    public function up()
    {
        Schema::create('dict_types', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('字典类型ID');
            $table->string('dict_type_code', 50)->unique()->comment('字典类型编码');
            $table->string('dict_type_name', 100)->comment('字典类型名称');
            $table->text('description')->nullable()->comment('描述');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_types');
    }
}
