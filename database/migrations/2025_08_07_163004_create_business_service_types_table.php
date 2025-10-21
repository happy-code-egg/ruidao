<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessServiceTypesTable extends Migration
{
    public function up()
    {
        Schema::create('business_service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('业务服务类型名称');
            $table->string('code', 50)->comment('业务服务类型编码')->nullable();
            $table->string('category', 50)->comment('分类');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->integer('created_by')->default(1)->comment('创建人')->nullable();
            $table->integer('updated_by')->default(1)->comment('更新人')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_service_types');
    }
}
