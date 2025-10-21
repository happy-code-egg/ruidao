<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileDescriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('file_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('文件描述名称');
            $table->string('code', 50)->unique()->comment('文件描述编码');
            $table->bigInteger('category_id')->comment('分类ID');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_descriptions');
    }
}
