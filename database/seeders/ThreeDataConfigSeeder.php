<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseCoefficient;
use App\Models\ProcessCoefficient;
use App\Models\ProcessInformation;
use Illuminate\Support\Facades\DB;

class ThreeDataConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 清空现有数据
        DB::table('case_coefficients')->truncate();
        DB::table('process_coefficients')->truncate();
        DB::table('process_informations')->truncate();

        // 插入项目系数测试数据
        $caseCoefficients = [
            [
                'sort' => 1,
                'name' => '专利申请系数A',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 2,
                'name' => '商标申请系数B',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 3,
                'name' => '版权申请系数C',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 4,
                'name' => '临时系数D',
                'is_valid' => false,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        CaseCoefficient::insert($caseCoefficients);

        // 插入处理事项系数测试数据
        $processCoefficients = [
            [
                'sort' => 1,
                'name' => '审查响应系数A',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 2,
                'name' => '答复通知系数B',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 3,
                'name' => '缴费处理系数C',
                'is_valid' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 4,
                'name' => '废弃系数D',
                'is_valid' => false,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        ProcessCoefficient::insert($processCoefficients);

        // 插入处理事项信息测试数据
        $processInformations = [
            [
                'case_type' => '发明专利',
                'business_type' => '申请业务',
                'application_type' => json_encode(['发明专利申请', '实用新型申请']),
                'country' => 'CN',
                'process_name' => '专利申请审查意见答复',
                'flow_completed' => '是',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'no',
                'process_status' => json_encode(['待处理', '处理中']),
                'case_phase' => '实质审查阶段',
                'process_type' => '审查答复',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => true,
                'sort' => 1,
                'consultant_contract' => '标准服务合约',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'case_type' => '商标注册',
                'business_type' => '注册业务',
                'application_type' => json_encode(['商标注册申请', '商标续展']),
                'country' => 'CN',
                'process_name' => '商标驳回复审申请',
                'flow_completed' => '否',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'no',
                'update_case_handler' => 'yes',
                'process_status' => json_encode(['待处理', '已完成']),
                'case_phase' => '复审阶段',
                'process_type' => '复审申请',
                'is_case_node' => 'yes',
                'is_commission' => 'no',
                'is_valid' => true,
                'sort' => 2,
                'consultant_contract' => '复审专项合约',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'case_type' => '外观设计专利',
                'business_type' => '申请业务',
                'application_type' => json_encode(['外观设计申请']),
                'country' => 'US',
                'process_name' => '外观设计专利申请',
                'flow_completed' => '是',
                'proposal_inquiry' => 'no',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'no',
                'process_status' => json_encode(['处理中', '已完成']),
                'case_phase' => '形式审查阶段',
                'process_type' => '申请提交',
                'is_case_node' => 'no',
                'is_commission' => 'yes',
                'is_valid' => true,
                'sort' => 3,
                'consultant_contract' => '美国专利合约',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'case_type' => '版权登记',
                'business_type' => '登记业务',
                'application_type' => json_encode(['软件著作权', '作品著作权']),
                'country' => 'CN',
                'process_name' => '版权变更登记',
                'flow_completed' => '否',
                'proposal_inquiry' => 'pending',
                'data_updater_inquiry' => 'pending',
                'update_case_handler' => 'pending',
                'process_status' => json_encode(['待处理']),
                'case_phase' => '审查阶段',
                'process_type' => '变更登记',
                'is_case_node' => 'no',
                'is_commission' => 'no',
                'is_valid' => false,
                'sort' => 4,
                'consultant_contract' => '版权登记合约',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        ProcessInformation::insert($processInformations);

        if ($this->command) {


            $this->command->info('Three data config seeder completed successfully!');


        }
        if ($this->command) {

            $this->command->info('Inserted:');

        }
        $this->command->info('- ' . count($caseCoefficients) . ' case coefficients');
        $this->command->info('- ' . count($processCoefficients) . ' process coefficients');
        $this->command->info('- ' . count($processInformations) . ' process informations');
    }
}
