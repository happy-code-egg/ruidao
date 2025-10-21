<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceService;

class InvoiceServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoiceServices = [
            [
                'service_name' => '专利代理服务',
                'service_code' => 'PATENT_AGENT',
                'description' => '发明专利、实用新型、外观设计专利代理服务',
                'is_valid' => 1,
                'sort_order' => 10,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '商标代理服务',
                'service_code' => 'TRADEMARK_AGENT',
                'description' => '商标注册、变更、续展等代理服务',
                'is_valid' => 1,
                'sort_order' => 20,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '版权登记服务',
                'service_code' => 'COPYRIGHT_REGISTER',
                'description' => '软件著作权、作品著作权登记服务',
                'is_valid' => 1,
                'sort_order' => 30,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '知识产权咨询服务',
                'service_code' => 'IP_CONSULTING',
                'description' => '知识产权法律咨询、策略规划服务',
                'is_valid' => 1,
                'sort_order' => 40,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '专利检索分析服务',
                'service_code' => 'PATENT_SEARCH',
                'description' => '专利检索、分析、评估服务',
                'is_valid' => 1,
                'sort_order' => 50,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '知识产权维权服务',
                'service_code' => 'IP_PROTECTION',
                'description' => '专利侵权分析、商标维权等服务',
                'is_valid' => 1,
                'sort_order' => 60,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '技术转移服务',
                'service_code' => 'TECH_TRANSFER',
                'description' => '专利技术转移、许可等服务',
                'is_valid' => 1,
                'sort_order' => 70,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '高新技术企业认定服务',
                'service_code' => 'HIGH_TECH_CERT',
                'description' => '高新技术企业认定申请服务',
                'is_valid' => 1,
                'sort_order' => 80,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '科技项目申报服务',
                'service_code' => 'TECH_PROJECT',
                'description' => '政府科技项目申报服务',
                'is_valid' => 1,
                'sort_order' => 90,
                'updater' => '系统初始化',
            ],
            [
                'service_name' => '知识产权培训服务',
                'service_code' => 'IP_TRAINING',
                'description' => '企业知识产权培训服务',
                'is_valid' => 0,
                'sort_order' => 100,
                'updater' => '系统初始化',
            ],
        ];

        foreach ($invoiceServices as $service) {
            InvoiceService::updateOrCreate(
                ['service_code' => $service['service_code']],
                $service
            );
        }

        if ($this->command) {


            $this->command->info('开票服务类型数据初始化完成');


        }
    }
}
