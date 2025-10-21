<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('状态名称');
            $table->string('code', 50)->unique()->comment('状态编码');
            $table->string('color', 20)->default('#409EFF')->comment('显示颜色');
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
        Schema::dropIfExists('business_statuses');
    }
}
