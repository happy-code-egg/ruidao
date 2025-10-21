<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Contract;
use App\Models\ContractService;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class ContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('contract_services')->delete();
        DB::table('contracts')->delete();

        // 获取一些客户和用户用于关联
        $customers = Customer::limit(5)->get();
        $users = User::limit(10)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('需要先创建客户数据才能创建合同种子数据');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('需要先创建用户数据才能创建合同种子数据');
            return;
        }

        $contracts = [
            [
                'contract_no' => 'HT202501001',
                'contract_code' => 'HT-ZL-001',
                'contract_name' => '智能家居控制系统专利申请服务合同',
                'customer_id' => $customers[0]->id,
                'service_type' => 'patent',
                'status' => '草稿',
                'summary' => '为客户提供智能家居控制系统发明专利申请服务，包括技术交底书撰写、专利申请文件准备、申请递交等全流程服务。',
                'business_person_id' => $users[0]->id,
                'technical_director_id' => $users[1]->id,
                'technical_department' => '专利技术部',
                'paper_status' => '是',
                'party_a_phone' => '010-12345678',
                'party_a_email' => 'contact@example.com',
                'party_a_address' => '北京市海淀区中关村大街1号',
                'party_b_signer' => '张三',
                'party_b_phone' => '13800138001',
                'party_b_company' => '睿道知识产权代理有限公司',
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 8000.00,
                'official_fee' => 950.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 8000.00,
                'total_amount' => 8950.00,
                'case_count' => 1,
                'opportunity_no' => 'SJ2025001',
                'opportunity_name' => '智能家居专利申请项目',
                'signing_date' => Carbon::now()->subDays(10),
                'validity_start_date' => Carbon::now()->subDays(10),
                'validity_end_date' => Carbon::now()->addMonths(12),
                'additional_terms' => '1. 如遇审查意见，免费提供一次答复服务；2. 专利授权后提供电子版证书；3. 提供专利年费缴纳提醒服务。',
                'remark' => '重要客户，优先处理',
                'last_process_time' => Carbon::now()->subDays(3),
                'process_remark' => '技术交底书已完成，正在撰写申请文件。',
                'created_by' => $users[0]->id,
                'updated_by' => $users[0]->id,
            ],
            [
                'contract_no' => 'HT202501002',
                'contract_code' => 'HT-SB-002',
                'contract_name' => '睿道商标注册申请服务合同',
                'customer_id' => $customers[1]->id,
                'service_type' => 'trademark',
                'status' => '已确认',
                'summary' => '为客户提供"睿道"文字及图形商标注册申请服务，涵盖第42类科学技术服务。',
                'business_person_id' => $users[1]->id,
                'technical_director_id' => $users[2]->id,
                'technical_department' => '商标业务部',
                'paper_status' => '否',
                'party_a_phone' => '021-87654321',
                'party_a_email' => 'tm@example.com',
                'party_a_address' => '上海市浦东新区张江高科技园区',
                'party_b_signer' => '李四',
                'party_b_phone' => '13900139002',
                'party_b_company' => '睿道知识产权代理有限公司',
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 2000.00,
                'official_fee' => 300.00,
                'channel_fee' => 200.00,
                'total_service_fee' => 2200.00,
                'total_amount' => 2500.00,
                'case_count' => 1,
                'opportunity_no' => 'SJ2025002',
                'opportunity_name' => '睿道商标注册项目',
                'signing_date' => Carbon::now()->subDays(20),
                'validity_start_date' => Carbon::now()->subDays(20),
                'validity_end_date' => Carbon::now()->addMonths(18),
                'additional_terms' => '1. 包含商标查询服务；2. 如遇驳回，免费提供驳回复审建议；3. 商标注册成功后提供监测服务1年。',
                'remark' => '老客户，价格优惠',
                'last_process_time' => Carbon::now()->subDays(5),
                'process_remark' => '商标申请已受理，等待审查。',
                'created_by' => $users[1]->id,
                'updated_by' => $users[1]->id,
            ],
            [
                'contract_no' => 'HT202501003',
                'contract_code' => 'HT-BR-003',
                'contract_name' => '知识产权管理系统软件著作权登记合同',
                'customer_id' => $customers[2]->id,
                'service_type' => 'copyright',
                'status' => '确认中',
                'summary' => '为客户提供知识产权管理系统V1.0软件著作权登记服务，加急处理。',
                'business_person_id' => $users[2]->id,
                'technical_director_id' => $users[3]->id,
                'technical_department' => '版权业务部',
                'paper_status' => '是',
                'party_a_phone' => '0755-88888888',
                'party_a_email' => 'copyright@example.com',
                'party_a_address' => '深圳市南山区科技园南区',
                'party_b_signer' => '王五',
                'party_b_phone' => '13700137003',
                'party_b_company' => '北京睿道科技有限公司',
                'party_b_address' => '北京市朝阳区望京SOHO T1 C座2307室',
                'service_fee' => 1500.00,
                'official_fee' => 0.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 1500.00,
                'total_amount' => 1500.00,
                'signing_date' => Carbon::now()->subDays(5),
                'validity_start_date' => Carbon::now()->subDays(5),
                'validity_end_date' => Carbon::now()->addMonths(6),
                'additional_terms' => '1. 加急申请，2周内完成登记；2. 提供电子版登记证书；3. 如遇补正，免费协助处理。',
                'remark' => '加急项目',
                'created_by' => $users[2]->id,
                'updated_by' => $users[2]->id,
                'case_count' => 1,
                'opportunity_no' => 'SJ2025003',
                'opportunity_name' => '软件著作权登记项目',
                'last_process_time' => Carbon::now()->subDays(1),
                'process_remark' => '材料已准备完毕，等待提交。',
            ],
            [
                'contract_no' => 'HT202501004',
                'contract_code' => 'HT-ZL-004',
                'contract_name' => '新能源汽车电池管理系统专利申请合同',
                'customer_id' => $customers[3]->id ?? $customers[0]->id,
                'service_type' => '专利申请',
                'status' => '审批中',
                'summary' => '新能源汽车电池管理系统相关技术专利申请服务。',
                'business_person_id' => $users[3]->id ?? $users[0]->id,
                'technical_director_id' => $users[4]->id ?? $users[1]->id,
                'technical_department' => '专利技术部',
                'paper_status' => '是',
                'party_a_phone' => '0571-12345678',
                'party_a_email' => 'patent@newenergy.com',
                'party_a_address' => '杭州市西湖区文三路168号',
                'party_b_signer' => '赵六',
                'party_b_phone' => '13600136004',
                'party_b_company' => '睿道知识产权代理有限公司',
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 12000.00,
                'official_fee' => 950.00,
                'channel_fee' => 500.00,
                'total_service_fee' => 12500.00,
                'total_amount' => 13450.00,
                'case_count' => 2,
                'opportunity_no' => 'SJ2025004',
                'opportunity_name' => '新能源技术专利布局项目',
                'signing_date' => Carbon::now()->subDays(15),
                'validity_start_date' => Carbon::now()->subDays(15),
                'validity_end_date' => Carbon::now()->addMonths(24),
                'additional_terms' => '包含PCT国际申请服务；提供专利技术分析报告。',
                'remark' => '战略客户，重点项目',
                'last_process_time' => Carbon::now()->subHours(6),
                'process_remark' => '正在进行技术交底书审核。',
                'created_by' => $users[3]->id ?? $users[0]->id,
                'updated_by' => $users[3]->id ?? $users[0]->id,
            ],
            [
                'contract_no' => 'HT202501005',
                'contract_code' => 'HT-SB-005',
                'contract_name' => '企业品牌商标注册服务合同',
                'customer_id' => $customers[4]->id ?? $customers[1]->id,
                'service_type' => '商标注册',
                'status' => '已完成',
                'summary' => '企业主品牌及系列产品商标注册申请服务。',
                'business_person_id' => $users[4]->id ?? $users[1]->id,
                'technical_director_id' => $users[5]->id ?? $users[2]->id,
                'technical_department' => '商标业务部',
                'paper_status' => '否',
                'party_a_phone' => '020-88888888',
                'party_a_email' => 'brand@company.com',
                'party_a_address' => '广州市天河区珠江新城',
                'party_b_signer' => '孙七',
                'party_b_phone' => '13500135005',
                'party_b_company' => '睿道知识产权代理有限公司',
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 5000.00,
                'official_fee' => 900.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 5000.00,
                'total_amount' => 5900.00,
                'case_count' => 3,
                'opportunity_no' => 'SJ2025005',
                'opportunity_name' => '品牌保护项目',
                'signing_date' => Carbon::now()->subDays(30),
                'validity_start_date' => Carbon::now()->subDays(30),
                'validity_end_date' => Carbon::now()->addMonths(12),
                'additional_terms' => '包含3个类别商标注册；提供商标监测服务。',
                'remark' => '已完成注册，客户满意',
                'last_process_time' => Carbon::now()->subDays(3),
                'process_remark' => '所有商标已成功注册，证书已邮寄。',
                'created_by' => $users[4]->id ?? $users[1]->id,
                'updated_by' => $users[4]->id ?? $users[1]->id,
            ],
        ];

        foreach ($contracts as $contractData) {
            try {
                $contract = Contract::create($contractData);
                $this->command->info("创建合同: {$contract->contract_no}");

                // 为每个合同创建服务明细
                $this->createContractServices($contract);
            } catch (\Exception $e) {
                $this->command->error("创建合同失败: " . $e->getMessage());
                $this->command->error("合同数据: " . json_encode($contractData));
            }
        }

        $this->command->info('合同种子数据创建完成！');
    }

    /**
     * 为合同创建服务明细
     */
    private function createContractServices($contract)
    {
        $services = [];

        switch ($contract->service_type) {
            case 'patent':
                $services = [
                    [
                        'service_name' => '发明专利申请',
                        'service_description' => '智能家居控制系统发明专利申请文件撰写',
                        'amount' => 6000.00,
                        'official_fee' => 750.00,
                        'remark' => '包含技术交底书分析、权利要求书撰写、说明书撰写',
                        'sort_order' => 1,
                    ],
                    [
                        'service_name' => '实质审查费',
                        'service_description' => '发明专利实质审查官费',
                        'amount' => 0.00,
                        'official_fee' => 200.00,
                        'remark' => '官费代缴',
                        'sort_order' => 2,
                    ],
                    [
                        'service_name' => '答复审查意见',
                        'service_description' => '审查意见答复服务',
                        'amount' => 2000.00,
                        'official_fee' => 0.00,
                        'remark' => '免费提供一次答复服务',
                        'sort_order' => 3,
                    ],
                ];
                break;

            case 'trademark':
                $services = [
                    [
                        'service_name' => '商标注册申请',
                        'service_description' => '睿道文字及图形商标注册申请',
                        'amount' => 1500.00,
                        'official_fee' => 300.00,
                        'remark' => '第42类科学技术服务',
                        'sort_order' => 1,
                    ],
                    [
                        'service_name' => '商标查询',
                        'service_description' => '商标注册前查询分析',
                        'amount' => 500.00,
                        'official_fee' => 0.00,
                        'remark' => '降低注册风险',
                        'sort_order' => 2,
                    ],
                ];
                break;

            case 'copyright':
                $services = [
                    [
                        'service_name' => '软件著作权登记',
                        'service_description' => '知识产权管理系统V1.0著作权登记',
                        'amount' => 1500.00,
                        'official_fee' => 0.00,
                        'remark' => '加急申请，2周内完成',
                        'sort_order' => 1,
                    ],
                ];
                break;
        }

        foreach ($services as $serviceData) {
            $serviceData['contract_id'] = $contract->id;
            ContractService::create($serviceData);
        }
    }
}
