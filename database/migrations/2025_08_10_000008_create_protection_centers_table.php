<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProtectionCentersTable extends Migration
{
    /**
     * Run the migrations.
     * 创建保护中心表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('protection_centers', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('name', 200)->comment('保护中心名称')->nullable();
            $table->string('center_name', 200)->nullable()->comment('保护中心名称');
            $table->string('code', 50)->comment('保护中心编码')->nullable();
            $table->string('address', 255)->nullable()->comment('地址');
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系电话');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
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
        Schema::dropIfExists('protection_centers');
    }
}
