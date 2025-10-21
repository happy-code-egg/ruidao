<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateExistingBusinessPersonsToRelatedPersons extends Migration
{
    /**
     * Run the migrations.
     * 将现有客户的业务员信息迁移到 customer_related_persons 表
     * @return void
     */
    public function up()
    {
        // 迁移现有客户的业务员信息
        $customers = DB::table('customers')
            ->whereNotNull('business_person_id')
            ->get();

        foreach ($customers as $customer) {
            // 检查是否已存在业务员记录
            $exists = DB::table('customer_related_persons')
                ->where('customer_id', $customer->id)
                ->where('related_business_person_id', $customer->business_person_id)
                ->where('person_type', '业务员')
                ->exists();

            if (!$exists) {
                // 获取业务员用户信息
                $user = DB::table('users')->where('id', $customer->business_person_id)->first();
                
                if ($user) {
                    DB::table('customer_related_persons')->insert([
                        'customer_id' => $customer->id,
                        'related_business_person_id' => $customer->business_person_id,
                        'person_name' => $user->real_name,
                        'person_type' => '业务员',
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'department' => '', // 部门信息需要从关联表获取，这里暂时留空
                        'is_active' => true,
                        'remark' => '从客户表迁移的业务员信息',
                        'created_by' => 1, // 系统用户
                        'updated_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        // 删除迁移的业务员记录
        DB::table('customer_related_persons')
            ->where('person_type', '业务员')
            ->where('remark', '从客户表迁移的业务员信息')
            ->delete();
    }
}
