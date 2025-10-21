<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToCustomerContactsTable extends Migration
{
    /**
     * Run the migrations.
     * 为客户联系人表添加前端需要的缺失字段
     * @return void
     */
    public function up()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 检查字段是否已存在，只添加不存在的字段
            if (!Schema::hasColumn('customer_contacts', 'work_email')) {
                $table->string('work_email', 100)->nullable()->comment('工作邮箱');
            }
            if (!Schema::hasColumn('customer_contacts', 'creator')) {
                $table->string('creator', 100)->nullable()->comment('创建人姓名');
            }
            if (!Schema::hasColumn('customer_contacts', 'create_time')) {
                $table->string('create_time', 30)->nullable()->comment('创建时间');
            }
            if (!Schema::hasColumn('customer_contacts', 'updater')) {
                $table->string('updater', 100)->nullable()->comment('更新人姓名');
            }
            if (!Schema::hasColumn('customer_contacts', 'update_time')) {
                $table->string('update_time', 30)->nullable()->comment('更新时间');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'work_email', 'creator', 'create_time', 'updater', 'update_time'
            ]);
        });
    }
}
