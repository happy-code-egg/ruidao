<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictDataTable extends Migration
{
    /**
     * Run the migrations.
     * 字典数据表
     * @return void
     */
    public function up()
    {
        Schema::create('dict_data', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('字典数据ID');
            $table->bigInteger('dict_type_id')->comment('字典类型ID');
            $table->string('dict_code', 50)->comment('字典编码');
            $table->string('dict_label', 100)->comment('字典标签');
            $table->string('dict_value', 100)->comment('字典值');
            $table->bigInteger('parent_id')->default(0)->comment('父字典ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('css_class', 100)->nullable()->comment('CSS类名');
            $table->string('list_class', 100)->nullable()->comment('列表类名');
            $table->smallInteger('is_default')->default(0)->comment('是否默认：0-否，1-是');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->text('description')->nullable()->comment('描述');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_data');
    }
}
