<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOurCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('our_companies', function (Blueprint $table) {
            // 添加前端页面需要的字段
            $table->string('short_name', 100)->nullable()->after('name')->comment('我方公司简称');
            $table->string('full_name', 200)->nullable()->after('short_name')->comment('我方公司全称');
            $table->string('credit_code', 50)->nullable()->after('full_name')->comment('信用代码');
            $table->string('bank', 100)->nullable()->after('address')->comment('开户行');
            $table->string('account', 50)->nullable()->after('bank')->comment('账号');
            $table->string('invoice_phone', 20)->nullable()->after('account')->comment('开票电话');
            
            // 修改现有字段注释
            $table->string('name', 100)->comment('我方公司名称（兼容字段）')->change();
        });
    }

    public function down()
    {
        Schema::table('our_companies', function (Blueprint $table) {
            $table->dropColumn([
                'short_name',
                'full_name',
                'credit_code',
                'bank',
                'account',
                'invoice_phone'
            ]);
        });
    }
}
