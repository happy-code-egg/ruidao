<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLevelConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_level_configs', function (Blueprint $table) {
            $table->id();
            $table->string('level_name')->comment('等级名称');
            $table->string('level_code')->unique()->comment('等级代码');
            $table->integer('level_order')->comment('等级排序');
            $table->enum('user_type', ['business', 'agent', 'consultant', 'operation'])->comment('人员类型');
            $table->integer('min_experience')->default(0)->comment('最低经验(年)');
            $table->integer('max_experience')->default(0)->comment('最高经验(年)');
            $table->decimal('base_salary', 12, 2)->default(0)->comment('基础薪资');
            $table->json('required_skills')->nullable()->comment('必备技能');
            $table->text('description')->nullable()->comment('等级描述');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_level_configs');
    }
}

