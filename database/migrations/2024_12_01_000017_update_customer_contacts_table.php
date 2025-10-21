<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerContactsTable extends Migration
{
    /**
     * Run the migrations.
     * 更新客户联系人表，添加前端需要的字段
     * @return void
     */
    public function up()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 添加前端需要的字段
            $table->string('gender', 10)->nullable()->after('contact_name')->comment('性别');
            $table->string('contact_type_text', 50)->nullable()->after('contact_type')->comment('联系人类型文本');
            $table->boolean('is_on_job')->default(true)->after('is_primary')->comment('是否在职');
            $table->string('department', 100)->nullable()->after('position')->comment('部门');
            $table->string('title', 50)->nullable()->after('department')->comment('称呼');
            $table->string('business_staff', 100)->nullable()->after('title')->comment('业务人员');
            $table->text('work_address')->nullable()->after('address')->comment('工作地址');
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
                'gender',
                'contact_type_text', 
                'is_on_job',
                'department',
                'title',
                'business_staff',
                'work_address'
            ]);
        });
    }
}
