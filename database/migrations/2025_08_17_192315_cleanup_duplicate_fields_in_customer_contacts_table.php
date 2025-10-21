<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanupDuplicateFieldsInCustomerContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 删除重复的字符串字段，保留关联字段和时间戳字段
            if (Schema::hasColumn('customer_contacts', 'creator')) {
                $table->dropColumn('creator');
            }
            if (Schema::hasColumn('customer_contacts', 'create_time')) {
                $table->dropColumn('create_time');
            }
            if (Schema::hasColumn('customer_contacts', 'updater')) {
                $table->dropColumn('updater');
            }
            if (Schema::hasColumn('customer_contacts', 'update_time')) {
                $table->dropColumn('update_time');
            }
            if (Schema::hasColumn('customer_contacts', 'business_staff')) {
                $table->dropColumn('business_staff');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 恢复删除的字段
            $table->string('creator', 100)->nullable()->comment('创建人姓名');
            $table->string('create_time', 30)->nullable()->comment('创建时间');
            $table->string('updater', 100)->nullable()->comment('更新人姓名');
            $table->string('update_time', 30)->nullable()->comment('更新时间');
            $table->string('business_staff', 100)->nullable()->comment('业务人员');
        });
    }
}
