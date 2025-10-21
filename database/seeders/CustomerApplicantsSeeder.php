<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerApplicantsSeeder extends Seeder
{
    /**
     * 运行客户申请人数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customer_applicants')->truncate();

        $now = Carbon::now();

        // 创建示例申请人数据
        $applicants = [
            [
                'id' => 1,
                'customer_id' => 1,
                'applicant_name_cn' => '上海睿道知识产权有限公司',
                'applicant_name_en' => 'Shanghai Ruidao Intellectual Property Co., Ltd.',
                'applicant_code' => 'APP20250101001',
                'applicant_type' => '企业',
                'id_type' => '统一社会信用代码',
                'id_number' => '91310115MA1FL5E91P',
                'country' => '中国',
                'business_location' => '上海市浦东新区',
                'fee_reduction' => true,
                'fee_reduction_start_date' => '2024-01-01',
                'fee_reduction_end_date' => '2026-12-31',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'street' => '张江高科技园区博云路2号',
                'postal_code' => '201203',
                'entity_type' => '小微企业',
                'address_en' => 'No.2 Boyun Road, Zhangjiang Hi-Tech Park, Pudong New District, Shanghai, China',
                'total_condition_no' => 'TOTAL202401001',
                'sync_date' => '2024-01-01',
                'email' => 'legal@ruidao.com',
                'phone' => '021-88889999',
                'inventor_note' => '该申请人为高新技术企业，享受费减优惠',
                'remark' => '主要申请人，负责公司所有知识产权申请',
                'business_staff' => '王销售',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'applicant_name_cn' => '张三',
                'applicant_name_en' => 'Zhang San',
                'applicant_code' => 'APP20250101002',
                'applicant_type' => '个人',
                'id_type' => '身份证',
                'id_number' => '310115198501010001',
                'country' => '中国',
                'business_location' => '上海市浦东新区',
                'fee_reduction' => true,
                'fee_reduction_start_date' => '2024-01-01',
                'fee_reduction_end_date' => '2026-12-31',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'street' => '张江镇博云路2号501室',
                'postal_code' => '201203',
                'entity_type' => '个人',
                'address_en' => 'Room 501, No.2 Boyun Road, Zhangjiang Town, Pudong New District, Shanghai, China',
                'total_condition_no' => 'TOTAL202401002',
                'sync_date' => '2024-01-15',
                'email' => 'zhangsan@ruidao.com',
                'phone' => '13800138000',
                'inventor_note' => '公司法定代表人，主要发明人',
                'remark' => '公司创始人，主要负责技术创新',
                'business_staff' => '王销售',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('customer_applicants')->insert($applicants);
        
        $this->command->info('客户申请人数据种子已成功植入！');
    }
}