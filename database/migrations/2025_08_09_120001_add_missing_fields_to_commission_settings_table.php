<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToCommissionSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // 添加缺失的字段
            $table->string('name', 100)->nullable()->after('id')->comment('提成配置名称');
            $table->string('code', 50)->nullable()->after('name')->comment('提成配置编码');
            $table->text('description')->nullable()->after('country')->comment('描述');
            $table->integer('created_by')->nullable()->after('description')->comment('创建人');
            $table->integer('updated_by')->nullable()->after('created_by')->comment('更新人');
        });
    }

    public function down()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'description', 'updater']);
        });
    }
}
