<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManuscriptScoringItemsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建审核打分项表
     * @return void
     */
    public function up()
    {
        Schema::create('manuscript_scoring_items', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('name')->comment('打分项名称');
            $table->string('code')->unique()->comment('打分项编码');
            $table->string('major_category')->comment('大类');
            $table->string('minor_category')->comment('小类');
            $table->text('description')->nullable()->comment('说明');
            $table->integer('score')->default(0)->comment('分值');
            $table->integer('max_score')->default(100)->comment('最高分值');
            $table->decimal('weight', 5, 2)->default(1.00)->comment('权重');
            $table->tinyInteger('status')->default(1)->comment('状态 0=无效 1=有效');
            $table->integer('sort_order')->default(0)->comment('排序号');
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->integer('created_by')->nullable()->comment('创建人');
            $table->timestamps();
            
            $table->index(['status', 'sort']);
            $table->index('major_category');
            $table->index('minor_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manuscript_scoring_items');
    }
}
