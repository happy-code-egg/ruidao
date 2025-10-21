<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     * 创建代理机构表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('代理机构ID');
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('agency_code', 50)->unique()->comment('机构编码');
            $table->string('agency_name_cn', 200)->comment('代理机构中文名称');
            $table->string('agency_name_en', 200)->nullable()->comment('代理机构英文名称');
            $table->string('country', 100)->nullable()->comment('所属国家(地区)');
            $table->string('social_credit_code', 100)->nullable()->comment('统一社会信用代码');
            $table->date('create_time')->nullable()->comment('成立时间');
            $table->string('account', 100)->nullable()->comment('账号');
            $table->string('password', 100)->nullable()->comment('密码');
            $table->string('province', 100)->nullable()->comment('省');
            $table->string('city', 100)->nullable()->comment('市');
            $table->string('province_en', 100)->nullable()->comment('省(英文)');
            $table->string('city_en', 100)->nullable()->comment('市(英文)');
            $table->string('address_cn', 500)->nullable()->comment('街道地址(中文)');
            $table->string('address_en', 500)->nullable()->comment('街道地址(英文)');
            $table->string('postcode', 20)->nullable()->comment('邮编');
            $table->string('manager', 100)->nullable()->comment('负责人');
            $table->string('contact', 100)->nullable()->comment('联系人');
            $table->string('modifier', 100)->nullable()->comment('修改人员');
            $table->string('agent_type', 100)->nullable()->comment('默认委托类型');
            $table->boolean('is_valid')->default(true)->comment('是否有效');
            $table->boolean('is_supplier')->default(false)->comment('是否为供应商');
            $table->text('requirements')->nullable()->comment('代理机构要求');
            $table->text('remark')->nullable()->comment('备注');
            $table->string('creator', 100)->nullable()->comment('新增人');
            $table->datetime('creation_time')->nullable()->comment('创建时间');
            $table->datetime('update_time')->nullable()->comment('修改时间');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID');
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->timestamps();
            $table->softDeletes();

            // 添加索引
            $table->index(['sort']);
            $table->index(['agency_name_cn']);
            $table->index(['country']);
            $table->index(['is_valid']);
            $table->index(['is_supplier']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agencies');
    }
}
