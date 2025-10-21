<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCopyrightExpediteTypesTable extends Migration
{
    public function up()
    {
        Schema::create('copyright_expedite_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('版权加快类型名称');
            $table->string('code', 50)->unique()->comment('版权加快类型编码');
            $table->integer('days')->comment('加快天数');
            $table->decimal('extra_fee', 10, 2)->comment('额外费用');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('copyright_expedite_types');
    }
}
