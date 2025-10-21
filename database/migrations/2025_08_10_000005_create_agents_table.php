<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建代理师表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('代理师ID');
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('name_cn', 100)->comment('中文姓名');
            $table->string('last_name_cn', 50)->nullable()->comment('姓氏(中文)');
            $table->string('first_name_cn', 50)->nullable()->comment('名(中文)');
            $table->string('name_en', 100)->nullable()->comment('英文姓名');
            $table->string('last_name_en', 50)->nullable()->comment('姓氏(英文)');
            $table->string('first_name_en', 50)->nullable()->comment('名(英文)');
            $table->string('license_number', 100)->unique()->comment('执业证号');
            $table->date('license_date')->nullable()->comment('获代理资格日期');
            $table->bigInteger('agency_id')->comment('所属代理机构ID');
            $table->string('phone', 50)->nullable()->comment('联系电话');
            $table->string('email', 255)->nullable()->comment('邮箱');
            $table->string('gender', 10)->default('男')->comment('性别');
            $table->date('license_expiry')->nullable()->comment('执业证到期日期');
            $table->string('specialty', 255)->nullable()->comment('专业领域');
            $table->boolean('is_default_agent')->default(false)->comment('默认本所代理师');
            $table->boolean('is_valid')->default(true)->comment('是否有效');
            $table->string('credit_rating', 50)->nullable()->comment('信用等级');
            $table->tinyInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->text('remarks')->nullable()->comment('备注');
            $table->string('creator', 100)->nullable()->comment('新增人');
            $table->datetime('creation_time')->nullable()->comment('创建时间');
            $table->string('modifier', 100)->nullable()->comment('修改人');
            $table->datetime('update_time')->nullable()->comment('修改时间');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID');
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->timestamps();
            $table->softDeletes();

            // 添加索引
            $table->foreign('agency_id')->references('id')->on('agencies');
            $table->index(['agency_id', 'status']);
            $table->index(['sort']);
            $table->index(['is_valid']);
            $table->index(['name_cn']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agents');
    }
}
