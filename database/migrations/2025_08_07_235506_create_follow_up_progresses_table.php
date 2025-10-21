<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpProgressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follow_up_progresses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('进度名称');
            $table->string('code', 50)->unique()->comment('进度编码');
            $table->integer('percentage')->default(0)->comment('进度百分比');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            
            // 添加索引
            $table->index(['status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('follow_up_progresses');
    }
}
