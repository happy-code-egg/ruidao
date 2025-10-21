<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParksConfigTable extends Migration
{
    /**
     * Run the migrations.
     * 园区名称设置表
     * @return void
     */
    public function up()
    {
        Schema::create('parks_config', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('园区配置ID');
            $table->string('park_name', 200)->comment('园区名称');
            $table->string('park_code', 50)->comment('园区编码')->nullable();
            $table->text('description')->nullable()->comment('描述');
            $table->string('address', 500)->nullable()->comment('园区地址');
            $table->string('contact_person', 100)->nullable()->comment('联系人');
            $table->string('contact_phone', 50)->nullable()->comment('联系电话');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效：0-否，1-是');
            $table->integer('sort_order')->default(0)->comment('排序号');
            $table->integer('created_by')->default(1)->comment('创建人')->nullable();
            $table->integer('updated_by')->default(1)->comment('更新人')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parks_config');
    }
}
