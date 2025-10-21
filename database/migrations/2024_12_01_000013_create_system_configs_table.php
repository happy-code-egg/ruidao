<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     * 系统参数表
     * @return void
     */
    public function up()
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('配置ID');
            $table->string('config_key', 100)->unique()->comment('配置键');
            $table->text('config_value')->nullable()->comment('配置值');
            $table->string('config_name', 200)->nullable()->comment('配置名称');
            $table->text('config_description')->nullable()->comment('配置描述');
            $table->string('config_type', 50)->nullable()->comment('配置类型');
            $table->smallInteger('is_system')->default(0)->comment('是否系统配置：0-否，1-是');
            $table->integer('sort_order')->default(0)->comment('排序');
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
        Schema::dropIfExists('system_configs');
    }
}
