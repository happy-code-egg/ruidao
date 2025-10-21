<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOurCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('our_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('我方公司名称');
            $table->string('code', 50)->unique()->comment('我方公司编码');
            $table->string('address', 255)->nullable()->comment('地址');
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系电话');
            $table->string('tax_number', 50)->nullable()->comment('税号');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->integer('created_by')->nullable()->comment('创建人');
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('our_companies');
    }
}
