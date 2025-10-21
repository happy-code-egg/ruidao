<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cases;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 获取现有的合同
        $contracts = Contract::with('customer')->get();
        
        if ($contracts->isEmpty()) {
            $this->command->info('没有找到合同数据，请先运行合同数据填充');
            return;
        }

        // 获取用户列表
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->info('没有找到用户数据，请先运行用户数据填充');
            return;
        }

        $this->command->info('开始填充合同项目数据...');

        foreach ($contracts as $contract) {
            // 为每个合同创建2-5个项目
            $caseCount = rand(2, 5);
            
            for ($i = 0; $i < $caseCount; $i++) {
                $this->createCase($contract, $users);
            }

            // 更新合同的项目数量
            $contract->update(['case_count' => $caseCount]);
        }

        $this->command->info('合同项目数据填充完成！');
    }

    private function createCase($contract, $users)
    {
        // 随机选择项目类型
        $caseTypes = [
            Cases::TYPE_PATENT => [
                'subtypes' => ['发明专利', '实用新型', '外观设计'],
                'prefix' => 'ZL'
            ],
            Cases::TYPE_TRADEMARK => [
                'subtypes' => ['商标注册', '商标续展', '商标变更'],
                'prefix' => 'TM'
            ],
            Cases::TYPE_COPYRIGHT => [
                'subtypes' => ['软件著作权', '作品著作权'],
                'prefix' => 'SR'
            ],
            Cases::TYPE_TECH_SERVICE => [
                'subtypes' => ['高新认定', '科技项目', '其他科技服务'],
                'prefix' => 'PJ'
            ]
        ];

        $caseType = array_rand($caseTypes);
        $typeInfo = $caseTypes[$caseType];
        $subtype = $typeInfo['subtypes'][array_rand($typeInfo['subtypes'])];
        $prefix = $typeInfo['prefix'];

        // 生成项目编号
        $caseCode = $this->generateCaseCode($prefix);

        // 随机选择状态
        $statuses = [
            Cases::STATUS_DRAFT,
            Cases::STATUS_SUBMITTED,
            Cases::STATUS_PROCESSING,
            Cases::STATUS_AUTHORIZED
        ];
        $status = $statuses[array_rand($statuses)];

        // 随机选择申请类型
        $applyTypes = ['国内申请', '国际申请', 'PCT申请', '科技项目'];
        $applyType = $applyTypes[array_rand($applyTypes)];

        // 生成项目名称
        $caseNames = [
            '一种智能家居控制系统',
            '新能源汽车电池管理系统',
            '区块链技术在供应链中的应用',
            '人工智能图像识别算法',
            '5G通信技术优化方案',
            '某品牌商标设计',
            '企业管理系统软件',
            '文化创意产品设计',
            '环保技术解决方案',
            '医疗设备创新设计'
        ];
        $caseName = $caseNames[array_rand($caseNames)];

        // 随机金额
        $estimatedCost = rand(5000, 50000);
        $serviceFee = rand(2000, 30000);
        $officialFee = rand(1000, 10000);

        // 随机选择用户
        $businessPerson = $users->random();
        $agent = $users->random();
        $assistant = $users->random();

        // 商标类别（仅商标类型需要）
        $trademarkCategory = '';
        if ($caseType === Cases::TYPE_TRADEMARK) {
            $categories = ['第1类', '第2类', '第3类', '第9类', '第35类', '第42类'];
            $trademarkCategory = $categories[array_rand($categories)];
        }

        // 科技服务名称（仅科服类型需要）
        $techServiceName = '';
        if ($caseType === Cases::TYPE_TECH_SERVICE) {
            $techServices = ['高新技术企业认定', '科技型中小企业认定', '研发费用加计扣除', '知识产权贯标'];
            $techServiceName = $techServices[array_rand($techServices)];
        }

        // 备注
        $remarks = [
            '客户重点项目，需要优先处理',
            '技术含量较高，需要仔细审核',
            '客户要求加急处理',
            '常规项目，按正常流程处理',
            '需要与客户进一步沟通确认',
            ''
        ];
        $remark = $remarks[array_rand($remarks)];

        // 创建项目
        Cases::create([
            'case_code' => $caseCode,
            'case_name' => $caseName,
            'customer_id' => $contract->customer_id,
            'contract_id' => $contract->id,
            'case_type' => $caseType,
            'case_subtype' => $subtype,
            'application_type' => $applyType,
            'case_status' => $status,
            'case_phase' => '申请阶段',
            'priority_level' => rand(1, 3),
            'application_no' => $this->generateApplicationNo($caseType),
            'application_date' => now()->subDays(rand(1, 365)),
            'registration_no' => $status >= Cases::STATUS_AUTHORIZED ? $this->generateRegistrationNo($caseType) : null,
            'registration_date' => $status >= Cases::STATUS_AUTHORIZED ? now()->subDays(rand(1, 100)) : null,
            'country_code' => 'CN',
            'presale_support' => rand(0, 1),
            'tech_leader' => $businessPerson->id,
            'tech_contact' => $agent->id,
            'is_authorized' => $status >= Cases::STATUS_AUTHORIZED ? 1 : 0,
            'tech_service_name' => $techServiceName,
            'trademark_category' => $trademarkCategory,
            'entity_type' => rand(1, 3),
            'business_person_id' => $businessPerson->id,
            'agent_id' => $agent->id,
            'assistant_id' => $assistant->id,
            'agency_id' => null,
            'deadline_date' => now()->addDays(rand(30, 365)),
            'annual_fee_due_date' => $caseType === Cases::TYPE_PATENT ? now()->addDays(rand(30, 365)) : null,
            'estimated_cost' => $estimatedCost,
            'actual_cost' => $status >= Cases::STATUS_PROCESSING ? $estimatedCost : 0.00,
            'service_fee' => $serviceFee,
            'official_fee' => $officialFee,
            'is_priority' => rand(0, 1),
            'priority_info' => null,
            'classification_info' => null,
            'case_description' => '这是一个' . $subtype . '项目，主要涉及' . $caseName . '的技术创新和知识产权保护。',
            'technical_field' => $this->getTechnicalField($caseType),
            'innovation_points' => '技术创新点包括：1. 技术方案优化；2. 性能提升；3. 成本降低。',
            'remarks' => $remark,
            'created_by' => $businessPerson->id,
            'updated_by' => $businessPerson->id,
        ]);
    }

    private function generateCaseCode($prefix)
    {
        $yearMonth = date('Ym');
        $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $yearMonth . $number;
    }

    private function generateApplicationNo($caseType)
    {
        $prefixes = [
            Cases::TYPE_PATENT => 'CN',
            Cases::TYPE_TRADEMARK => 'TM',
            Cases::TYPE_COPYRIGHT => 'SR',
            Cases::TYPE_TECH_SERVICE => 'PJ'
        ];
        
        $prefix = $prefixes[$caseType] ?? 'CN';
        $year = date('Y');
        $number = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $year . $number;
    }

    private function generateRegistrationNo($caseType)
    {
        $prefixes = [
            Cases::TYPE_PATENT => 'ZL',
            Cases::TYPE_TRADEMARK => 'TM',
            Cases::TYPE_COPYRIGHT => 'SR',
            Cases::TYPE_TECH_SERVICE => 'PJ'
        ];
        
        $prefix = $prefixes[$caseType] ?? 'ZL';
        $year = date('Y');
        $number = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $year . $number;
    }

    private function getTechnicalField($caseType)
    {
        $fields = [
            Cases::TYPE_PATENT => ['电子技术', '机械工程', '化学工程', '生物技术', '信息技术'],
            Cases::TYPE_TRADEMARK => ['商业服务', '电子产品', '服装鞋帽', '食品饮料', '医药保健'],
            Cases::TYPE_COPYRIGHT => ['软件开发', '文学创作', '艺术设计', '音乐作品', '影视作品'],
            Cases::TYPE_TECH_SERVICE => ['科技咨询', '技术评估', '项目申报', '资质认定', '其他服务']
        ];
        
        $fieldList = $fields[$caseType] ?? ['其他'];
        return $fieldList[array_rand($fieldList)];
    }
}
