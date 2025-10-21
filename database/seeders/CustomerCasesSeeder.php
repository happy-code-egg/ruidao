<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerCasesSeeder extends Seeder
{
    /**
     * 运行客户案例数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('cases')->truncate();

        $now = Carbon::now();

        // 创建示例案例数据
        $cases = [
            [
                'id' => 1,
                'case_code' => 'PAT20240115001',
                'case_name' => '基于AI的专利智能检索系统',
                'customer_id' => 1,
                'contract_id' => 1,
                'case_type' => 1, // 专利
                'case_subtype' => '发明专利',
                'application_type' => '发明',
                'case_status' => 3, // 处理中
                'case_phase' => '实质审查',
                'priority_level' => 1, // 高优先级
                'application_no' => '202410012345.6',
                'application_date' => '2024-01-15',
                'registration_no' => null,
                'registration_date' => null,
                'country_code' => 'CN',
                'entity_type' => 2, // 小实体
                'applicant_info' => json_encode([
                    'name' => '上海睿道知识产权有限公司',
                    'address' => '上海市浦东新区张江高科技园区博云路2号'
                ]),
                'inventor_info' => json_encode([
                    ['name' => '张三', 'id_number' => '310115198501010001'],
                    ['name' => '李四', 'id_number' => '310115198801020002']
                ]),
                'business_person_id' => 1,
                'agent_id' => 1,
                'assistant_id' => 2,
                'agency_id' => 1,
                'deadline_date' => '2024-07-15',
                'annual_fee_due_date' => null,
                'estimated_cost' => 8000.00,
                'actual_cost' => 5200.00,
                'service_fee' => 3000.00,
                'official_fee' => 2200.00,
                'is_priority' => 0,
                'priority_info' => null,
                'classification_info' => json_encode([
                    'ipc' => 'G06F16/00',
                    'cpc' => 'G06F16/9038'
                ]),
                'case_description' => '一种基于人工智能的专利智能检索系统，能够自动分析专利文档并提供精确的检索结果',
                'technical_field' => '人工智能，信息检索，专利分析',
                'innovation_points' => '采用深度学习算法提高检索精度，支持多语言专利检索，具有自学习优化能力',
                'remarks' => '核心技术专利，具有重要战略意义',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'case_code' => 'TRA20240320001',
                'case_name' => '睿道IP商标注册',
                'customer_id' => 1,
                'contract_id' => 3,
                'case_type' => 2, // 商标
                'case_subtype' => '普通商标',
                'application_type' => '注册',
                'case_status' => 4, // 已授权
                'case_phase' => '已注册',
                'priority_level' => 2, // 中优先级
                'application_no' => '65432198',
                'application_date' => '2024-03-20',
                'registration_no' => '43218765',
                'registration_date' => '2024-11-15',
                'country_code' => 'CN',
                'entity_type' => 2,
                'applicant_info' => json_encode([
                    'name' => '上海睿道知识产权有限公司',
                    'address' => '上海市浦东新区张江高科技园区博云路2号'
                ]),
                'inventor_info' => null,
                'business_person_id' => 1,
                'agent_id' => 1,
                'assistant_id' => 2,
                'agency_id' => 1,
                'deadline_date' => null,
                'annual_fee_due_date' => '2034-11-15',
                'estimated_cost' => 2000.00,
                'actual_cost' => 1800.00,
                'service_fee' => 1000.00,
                'official_fee' => 800.00,
                'is_priority' => 0,
                'priority_info' => null,
                'classification_info' => json_encode([
                    'nice_class' => '42',
                    'description' => '科学技术服务和与之相关的研究与设计服务'
                ]),
                'case_description' => '睿道IP商标在第42类科学技术服务分类的注册申请',
                'technical_field' => '知识产权服务',
                'innovation_points' => '品牌标识设计新颖，识别度高',
                'remarks' => '公司主要商标，已成功注册',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'case_code' => 'PAT20240625001',
                'case_name' => '智能商标图像识别方法',
                'customer_id' => 1,
                'contract_id' => 2,
                'case_type' => 1, // 专利
                'case_subtype' => '发明专利',
                'application_type' => '发明',
                'case_status' => 2, // 已提交
                'case_phase' => '初步审查',
                'priority_level' => 1, // 高优先级
                'application_no' => '202410067890.X',
                'application_date' => '2024-06-25',
                'registration_no' => null,
                'registration_date' => null,
                'country_code' => 'CN',
                'entity_type' => 2,
                'applicant_info' => json_encode([
                    'name' => '上海睿道知识产权有限公司',
                    'address' => '上海市浦东新区张江高科技园区博云路2号'
                ]),
                'inventor_info' => json_encode([
                    ['name' => '张三', 'id_number' => '310115198501010001'],
                    ['name' => '王五', 'id_number' => '310115199205150003']
                ]),
                'business_person_id' => 1,
                'agent_id' => 1,
                'assistant_id' => 2,
                'agency_id' => 1,
                'deadline_date' => '2024-12-25',
                'annual_fee_due_date' => null,
                'estimated_cost' => 7500.00,
                'actual_cost' => 4800.00,
                'service_fee' => 2800.00,
                'official_fee' => 2000.00,
                'is_priority' => 0,
                'priority_info' => null,
                'classification_info' => json_encode([
                    'ipc' => 'G06T7/00',
                    'cpc' => 'G06T7/0002'
                ]),
                'case_description' => '一种基于深度学习的智能商标图像识别方法，能够快速准确识别商标图案',
                'technical_field' => '计算机视觉，图像识别，商标检索',
                'innovation_points' => '创新的卷积神经网络架构，支持多角度商标识别，识别准确率达95%以上',
                'remarks' => '技术创新性强，市场前景广阔',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'case_code' => 'COP20240810001',
                'case_name' => 'IP管理平台软件著作权',
                'customer_id' => 1,
                'contract_id' => 1,
                'case_type' => 3, // 版权
                'case_subtype' => '软件著作权',
                'application_type' => '登记',
                'case_status' => 6, // 已完成
                'case_phase' => '已登记',
                'priority_level' => 3, // 低优先级
                'application_no' => '2024SR0987654',
                'application_date' => '2024-08-10',
                'registration_no' => '2024SR0987654',
                'registration_date' => '2024-09-15',
                'country_code' => 'CN',
                'entity_type' => 2,
                'applicant_info' => json_encode([
                    'name' => '上海睿道知识产权有限公司',
                    'address' => '上海市浦东新区张江高科技园区博云路2号'
                ]),
                'inventor_info' => json_encode([
                    ['name' => '李四', 'role' => '主要开发者'],
                    ['name' => '王五', 'role' => '界面设计师']
                ]),
                'business_person_id' => 1,
                'agent_id' => 1,
                'assistant_id' => 2,
                'agency_id' => 1,
                'deadline_date' => null,
                'annual_fee_due_date' => null,
                'estimated_cost' => 1500.00,
                'actual_cost' => 1200.00,
                'service_fee' => 800.00,
                'official_fee' => 400.00,
                'is_priority' => 0,
                'priority_info' => null,
                'classification_info' => json_encode([
                    'category' => '应用软件',
                    'language' => 'Java, JavaScript'
                ]),
                'case_description' => '知识产权管理平台软件著作权登记，包含客户管理、案件管理、流程管理等功能模块',
                'technical_field' => '软件开发，知识产权管理',
                'innovation_points' => '集成化管理平台，支持多种知识产权类型，具有智能提醒功能',
                'remarks' => '公司核心软件产品，已成功登记',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'case_code' => 'SER20241010001',
                'case_name' => '高新技术企业认定咨询',
                'customer_id' => 1,
                'contract_id' => 4,
                'case_type' => 4, // 科服
                'case_subtype' => '认定咨询',
                'application_type' => '咨询服务',
                'case_status' => 3, // 处理中
                'case_phase' => '材料准备',
                'priority_level' => 2, // 中优先级
                'application_no' => null,
                'application_date' => '2024-10-10',
                'registration_no' => null,
                'registration_date' => null,
                'country_code' => 'CN',
                'entity_type' => 2,
                'applicant_info' => json_encode([
                    'name' => '上海睿道知识产权有限公司',
                    'address' => '上海市浦东新区张江高科技园区博云路2号'
                ]),
                'inventor_info' => null,
                'business_person_id' => 1,
                'agent_id' => 1,
                'assistant_id' => 2,
                'agency_id' => 1,
                'deadline_date' => '2024-12-31',
                'annual_fee_due_date' => null,
                'estimated_cost' => 15000.00,
                'actual_cost' => 8000.00,
                'service_fee' => 8000.00,
                'official_fee' => 0.00,
                'is_priority' => 0,
                'priority_info' => null,
                'classification_info' => json_encode([
                    'service_type' => '高新技术企业认定',
                    'industry' => '软件和信息技术服务业'
                ]),
                'case_description' => '协助客户准备高新技术企业重新认定申请材料，提供全程咨询服务',
                'technical_field' => '企业认定咨询，政策申报',
                'innovation_points' => '专业的认定经验，高成功率',
                'remarks' => '重要的资质认定项目，关系到税收优惠',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('cases')->insert($cases);
        
        $this->command->info('客户案例数据种子已成功植入！');
    }
}
