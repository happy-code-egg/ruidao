<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseCoefficient;
use App\Models\ProcessCoefficient;
use App\Models\ProcessInformation;
use App\Models\PatentAnnualFee;
use App\Models\PatentAnnualFeeDetail;
use App\Models\RelatedType;
use Carbon\Carbon;

class DataConfigFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 为数据配置页面插入模拟数据
     */
    public function run()
    {
        $now = Carbon::now();
        
        // 1. 项目系数数据
        $caseCoefficients = [
            ['id' => 1, 'sort' => 1, 'name' => '普通项目系数', 'is_valid' => 1, 'sort_order' => 1, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'sort' => 2, 'name' => '复杂项目系数', 'is_valid' => 1, 'sort_order' => 2, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'sort' => 3, 'name' => '紧急项目系数', 'is_valid' => 1, 'sort_order' => 3, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'sort' => 4, 'name' => '特殊项目系数', 'is_valid' => 0, 'sort_order' => 4, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
        ];
        
        foreach ($caseCoefficients as $data) {
            CaseCoefficient::updateOrCreate(['id' => $data['id']], $data);
        }
        
        // 2. 处理事项系数数据
        $processCoefficients = [
            ['id' => 1, 'sort' => 1, 'name' => '初级处理系数', 'is_valid' => 1, 'sort_order' => 1, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'sort' => 2, 'name' => '中级处理系数', 'is_valid' => 1, 'sort_order' => 2, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'sort' => 3, 'name' => '高级处理系数', 'is_valid' => 1, 'sort_order' => 3, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'sort' => 4, 'name' => '专家处理系数', 'is_valid' => 0, 'sort_order' => 4, 'updated_by' => '系统', 'created_at' => $now, 'updated_at' => $now],
        ];
        
        foreach ($processCoefficients as $data) {
            ProcessCoefficient::updateOrCreate(['id' => $data['id']], $data);
        }
        
        // 3. 处理事项信息数据
        $processInformations = [
            [
                'id' => 1,
                'sort' => 1,
                'case_type' => '发明专利',
                'business_type' => '申请业务',
                'application_type' => json_encode(['申请类型1', '申请类型2']),
                'country' => 'CN',
                'process_name' => '专利申请受理',
                'flow_completed' => '是',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'yes',
                'process_status' => json_encode(['pending', 'processing']),
                'case_phase' => '申请阶段',
                'process_type' => '受理类',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => 1,
                'sort_order' => 1,
                'consultant_contract' => '标准顾问合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'sort' => 2,
                'case_type' => '实用新型',
                'business_type' => '审查业务',
                'application_type' => json_encode(['申请类型2', '申请类型3']),
                'country' => 'US',
                'process_name' => '实质审查',
                'flow_completed' => '否',
                'proposal_inquiry' => 'no',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'no',
                'process_status' => json_encode(['processing', 'completed']),
                'case_phase' => '审查阶段',
                'process_type' => '审查类',
                'is_case_node' => 'no',
                'is_commission' => 'no',
                'is_valid' => 1,
                'sort_order' => 2,
                'consultant_contract' => '审查顾问合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'sort' => 3,
                'case_type' => '外观设计',
                'business_type' => '授权业务',
                'application_type' => json_encode(['申请类型1']),
                'country' => 'UK',
                'process_name' => '授权公告',
                'flow_completed' => '待定',
                'proposal_inquiry' => 'pending',
                'data_updater_inquiry' => 'pending',
                'update_case_handler' => 'pending',
                'process_status' => json_encode(['completed']),
                'case_phase' => '授权阶段',
                'process_type' => '授权类',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => 1,
                'sort_order' => 3,
                'consultant_contract' => '授权顾问合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        
        foreach ($processInformations as $data) {
            ProcessInformation::updateOrCreate(['id' => $data['id']], $data);
        }
        
        // 4. 专利年费配置数据
        $patentAnnualFees = [
            [
                'id' => 1,
                'case_type' => '发明专利',
                'apply_type' => '普通申请',
                'country' => '中国',
                'start_date' => '申请日',
                'currency' => 'CNY',
                'has_fee_guide' => 1,
                'sort_order' => 1,
                'is_valid' => 1,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'case_type' => '实用新型',
                'apply_type' => 'PCT申请',
                'country' => '美国',
                'start_date' => '公告日',
                'currency' => 'USD',
                'has_fee_guide' => 1,
                'sort_order' => 2,
                'is_valid' => 1,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'case_type' => '外观设计',
                'apply_type' => '巴黎公约申请',
                'country' => '日本',
                'start_date' => '申请日',
                'currency' => 'EUR',
                'has_fee_guide' => 0,
                'sort_order' => 3,
                'is_valid' => 0,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        
        foreach ($patentAnnualFees as $data) {
            PatentAnnualFee::updateOrCreate(['id' => $data['id']], $data);
        }
        
        // 5. 专利年费详情数据
        $patentAnnualFeeDetails = [
            [
                'id' => 1,
                'patent_annual_fee_id' => 1,
                'stage_code' => '第1年年费',
                'rank' => 1,
                'official_year' => 1,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 0,
                'end_year' => 1,
                'base_fee' => 600.00,
                'small_fee' => 180.00,
                'micro_fee' => 90.00,
                'authorization_fee' => 400.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'patent_annual_fee_id' => 1,
                'stage_code' => '第2年年费',
                'rank' => 2,
                'official_year' => 2,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 1,
                'end_year' => 2,
                'base_fee' => 900.00,
                'small_fee' => 270.00,
                'micro_fee' => 135.00,
                'authorization_fee' => 600.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'patent_annual_fee_id' => 2,
                'stage_code' => '第1年年费',
                'rank' => 1,
                'official_year' => 1,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 0,
                'end_year' => 1,
                'base_fee' => 800.00,
                'small_fee' => 240.00,
                'micro_fee' => 120.00,
                'authorization_fee' => 500.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        
        foreach ($patentAnnualFeeDetails as $data) {
            PatentAnnualFeeDetail::updateOrCreate(['id' => $data['id']], $data);
        }
        
        // 6. 相关类型数据
        $relatedTypes = [
            [
                'id' => 1,
                'sort' => 1,
                'case_type' => '发明专利',
                'type_name' => '技术领域一',
                'type_code' => 'TECH001',
                'description' => '技术领域一的描述信息',
                'is_valid' => 1,
                'sort_order' => 1,
                'updater' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'sort' => 2,
                'case_type' => '实用新型',
                'type_name' => '技术领域二',
                'type_code' => 'TECH002',
                'description' => '技术领域二的描述信息',
                'is_valid' => 1,
                'sort_order' => 2,
                'updater' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'sort' => 3,
                'case_type' => '外观设计',
                'type_name' => '设计领域一',
                'type_code' => 'DESIGN001',
                'description' => '设计领域一的描述信息',
                'is_valid' => 1,
                'sort_order' => 3,
                'updater' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'sort' => 4,
                'case_type' => '商标',
                'type_name' => '商标分类一',
                'type_code' => 'TM001',
                'description' => '商标分类一的描述信息',
                'is_valid' => 0,
                'sort_order' => 4,
                'updater' => '系统',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        
        foreach ($relatedTypes as $data) {
            RelatedType::updateOrCreate(['id' => $data['id']], $data);
        }
        
        $this->command->info('数据配置模拟数据插入完成！');
        $this->command->info('- 项目系数: ' . count($caseCoefficients) . ' 条');
        $this->command->info('- 处理事项系数: ' . count($processCoefficients) . ' 条');
        $this->command->info('- 处理事项信息: ' . count($processInformations) . ' 条');
        $this->command->info('- 专利年费配置: ' . count($patentAnnualFees) . ' 条');
        $this->command->info('- 专利年费详情: ' . count($patentAnnualFeeDetails) . ' 条');
        $this->command->info('- 相关类型: ' . count($relatedTypes) . ' 条');
    }
}
