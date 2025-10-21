<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessInformation;
use App\Models\OpportunityType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MissingPageDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 为缺失数据的页面插入模拟数据
     */
    public function run()
    {
        $now = Carbon::now();
        
        // 1. 处理事项信息数据
        $processInformations = [
            [
                'case_type' => '发明专利',
                'business_type' => '申请',
                'application_type' => json_encode(['正常申请', '快速申请']),
                'country' => 'CN',
                'process_name' => '专利申请受理',
                'flow_completed' => '否',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'yes',
                'process_status' => json_encode(['待处理', '处理中']),
                'case_phase' => '申请阶段',
                'process_type' => '受理类型',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => 1,
                'sort_order' => 1,
                'consultant_contract' => '标准合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'case_type' => '发明专利',
                'business_type' => '实审',
                'application_type' => json_encode(['正常实审', '加急实审']),
                'country' => 'CN',
                'process_name' => '专利实质审查',
                'flow_completed' => '否',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'yes', 
                'update_case_handler' => 'no',
                'process_status' => json_encode(['实审中', '审查完成']),
                'case_phase' => '审查阶段',
                'process_type' => '审查类型',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => 1,
                'sort_order' => 2,
                'consultant_contract' => '实审合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'case_type' => '实用新型',
                'business_type' => '申请',
                'application_type' => json_encode(['正常申请']),
                'country' => 'CN',
                'process_name' => '实用新型申请',
                'flow_completed' => '是',
                'proposal_inquiry' => 'no',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'yes',
                'process_status' => json_encode(['待处理', '已授权']),
                'case_phase' => '申请阶段',
                'process_type' => '申请类型',
                'is_case_node' => 'yes',
                'is_commission' => 'no',
                'is_valid' => 1,
                'sort_order' => 3,
                'consultant_contract' => '实用新型合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'case_type' => '外观设计',
                'business_type' => '申请',
                'application_type' => json_encode(['正常申请', '优先权申请']),
                'country' => 'CN',
                'process_name' => '外观设计申请',
                'flow_completed' => '是',
                'proposal_inquiry' => 'pending',
                'data_updater_inquiry' => 'no',
                'update_case_handler' => 'pending',
                'process_status' => json_encode(['待处理', '已授权', '已驳回']),
                'case_phase' => '申请阶段',
                'process_type' => '申请类型',
                'is_case_node' => 'no',
                'is_commission' => 'no',
                'is_valid' => 1,
                'sort_order' => 4,
                'consultant_contract' => '外观设计合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'case_type' => '发明专利',
                'business_type' => '答复',
                'application_type' => json_encode(['审查意见答复']),
                'country' => 'US',
                'process_name' => '美国专利审查答复',
                'flow_completed' => '否',
                'proposal_inquiry' => 'yes',
                'data_updater_inquiry' => 'yes',
                'update_case_handler' => 'yes',
                'process_status' => json_encode(['答复中', '等待决定']),
                'case_phase' => '审查阶段',
                'process_type' => '答复类型',
                'is_case_node' => 'yes',
                'is_commission' => 'yes',
                'is_valid' => 1,
                'sort_order' => 5,
                'consultant_contract' => 'US专利合同',
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('process_informations')->insert($processInformations);

        // 2. 商机类型数据
        $opportunityTypes = [
            [
                'status_name' => '新客户开发',
                'is_valid' => true,
                'sort' => 1,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '老客户维护',
                'is_valid' => true,
                'sort' => 2,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '潜在客户跟进',
                'is_valid' => true,
                'sort' => 3,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '合作伙伴推荐',
                'is_valid' => true,
                'sort' => 4,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '展会接触',
                'is_valid' => true,
                'sort' => 5,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '网络询盘',
                'is_valid' => false,
                'sort' => 6,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'status_name' => '电话咨询',
                'is_valid' => true,
                'sort' => 7,
                'updated_by' => '系统',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('opportunity_types')->insert($opportunityTypes);

        $this->command->info('数据种子执行完成！');
        $this->command->info('ProcessInformation 插入了 ' . count($processInformations) . ' 条记录');
        $this->command->info('OpportunityType 插入了 ' . count($opportunityTypes) . ' 条记录');
    }
}
