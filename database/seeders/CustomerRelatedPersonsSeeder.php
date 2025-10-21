<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerRelatedPersonsSeeder extends Seeder
{
    /**
     * 运行客户相关人员数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customer_related_persons')->truncate();

        $now = Carbon::now();

        // 创建示例相关人员数据
        $relatedPersons = [
            [
                'id' => 1,
                'customer_id' => 1,
                'person_name' => '陈技术',
                'person_type' => '技术负责人',
                'phone' => '13800001111',
                'email' => 'chenjishu@ruidao.com',
                'position' => '技术总监',
                'department' => '技术部',
                'relationship' => '技术合作伙伴',
                'responsibility' => '负责技术方案制定和技术审核',
                'is_active' => true,
                'remark' => '技术实力强，合作默契',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'person_name' => '刘商务',
                'person_type' => '商务负责人',
                'phone' => '13900002222',
                'email' => 'liushangwu@ruidao.com',
                'position' => '商务经理',
                'department' => '商务部',
                'relationship' => '商务对接人',
                'responsibility' => '负责商务谈判和合同签订',
                'is_active' => true,
                'remark' => '商务经验丰富，沟通能力强',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'customer_id' => 1,
                'person_name' => '赵财务',
                'person_type' => '财务负责人',
                'phone' => '13700003333',
                'email' => 'zhaocaiwu@ruidao.com',
                'position' => '财务总监',
                'department' => '财务部',
                'relationship' => '财务对接人',
                'responsibility' => '负责财务审核和付款流程',
                'is_active' => true,
                'remark' => '财务专业，处理效率高',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('customer_related_persons')->insert($relatedPersons);
        
        $this->command->info('客户相关人员数据种子已成功植入！');
    }
}
