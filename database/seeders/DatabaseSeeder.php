<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 数据库种子文件主入口，按照依赖关系顺序执行所有种子文件
     */
    public function run()
    {
        // 1. 系统基础数据（按依赖关系排序）
        $this->call([
            // 部门（必须在用户之前）
            DepartmentsSeeder::class,

            // 用户（依赖部门）
            UsersSeeder::class,

            // 角色（基础配置）
            RolesSeeder::class,

            // 权限（基础配置）
            PermissionsSeeder::class,

            // 用户角色关联（依赖用户和角色）
            UserRolesSeeder::class,

            // 角色权限关联（依赖角色和权限）
            RolePermissionsSeeder::class,
        ]);

        // 2. 基础配置数据（地理和业务类型）
        $this->call([
            // 国家（基础配置）
            CountriesSeeder::class,

            // 城市（基础配置）
            CitiesSeeder::class,

            // 货币（基础配置）
            CurrenciesSeeder::class,

            // 业务类型（基础配置）
            BusinessTypesSeeder::class,

            // 申请类型（基础配置）
            ApplyTypesSeeder::class,

            // 处理事项状态（基础配置）
            ProcessStatusesSeeder::class,

            // 费用配置（基础配置）
            FeeConfigsSeeder::class,
        ]);

        // 3. 公司和代理机构数据（有依赖关系）
        $this->call([
            // 公司（基础配置）
            CompaniesSeeder::class,

            // 代理机构（必须在代理师之前）
            AgenciesSeeder::class,

            // 代理师（依赖代理机构）
            AgentsSeeder::class,
        ]);

        // 4. 数据配置表（有依赖关系）
        $this->call([
            // 科技服务类型（必须在科技服务事项之前）
            TechServiceTypesSeeder::class,

            // 科技服务事项（依赖科技服务类型）
            TechServiceItemsSeeder::class,

            // 审核打分项
            ManuscriptScoringItemsSeeder::class,

            // 保护中心
            ProtectionCentersSeeder::class,

            // 价格指数
            PriceIndicesSeeder::class,

            // 创新指数
            InnovationIndicesSeeder::class,

            // 文件描述设置
            FileDescriptionsSeeder::class,

            // 处理事项规则
            ProcessRulesSeeder::class,

            // 工作流配置
            WorkflowsSeeder::class,
        ]);

        // 5. 客户管理数据
        $this->call([
            // 客户（依赖客户级别和规模）
            CustomersSeeder::class,
            CustomerDetailSeeder::class,        // 客户详情数据
            
            // 客户联系人（依赖客户）
            CustomerContactsSeeder::class,
            
            // 客户申请人（依赖客户）
            CustomerApplicantsSeeder::class,
            
            // 客户发明人（依赖客户）
            CustomerInventorsSeeder::class,
            
            // 客户相关人员（依赖客户）
            CustomerRelatedPersonsSeeder::class,
            
            // 客户文件（依赖客户）
            CustomerFilesSeeder::class,
            
            // 客户合同（依赖客户）
            CustomerContractsSeeder::class,

            // 新合同系统（依赖客户和用户）
            ContractsSeeder::class,

            // 更新现有合同的合同类型（依赖合同）
            UpdateExistingContractsWithTypeSeeder::class,

            // 合同项目（依赖合同和用户）
            ContractCaseSeeder::class,

            // 客户案例（依赖客户）
            CustomerCasesSeeder::class,
        ]);

        // 6. 业务配置数据（现有的种子文件）
        $this->call([
            // 业务服务类型
            BusinessServiceTypesSeeder::class,

            // 业务状态
            BusinessStatusesSeeder::class,

            // 客户级别
            CustomerLevelsSeeder::class,

            // 客户规模
            CustomerScalesSeeder::class,

            // 跟进方式
            FollowUpMethodsSeeder::class,

            // 跟进类型
            FollowUpTypesSeeder::class,

            // 跟进进度
            FollowUpProgressesSeeder::class,

            // 案件系数
            CaseCoefficientsSeeder::class,

            // 委托设置
            CommissionSettingsSeeder::class,

            // 委托类型
            CommissionTypesSeeder::class,

            // 版权加急类型
            CopyrightExpediteTypesSeeder::class,

            // 发票服务
            InvoiceServicesSeeder::class,

            // 我方公司
            OurCompaniesSeeder::class,

            // 园区配置
            ParksConfigSeeder::class,

            // 专利年费
            PatentAnnualFeesSeeder::class,

            // 流程系数
            ProcessCoefficientsSeeder::class,

            // 流程信息
            ProcessInformationSeeder::class,

            // 流程类型
            ProcessTypesSeeder::class,

            // 文件分类
            FileCategoriesSeeder::class,

            // 产品设置
            ProductsSeeder::class,

            // 关联类型
            RelatedTypesSeeder::class,
        ]);

        if ($this->command) {


            $this->command->info('所有种子数据已成功填充！');


        }
        if ($this->command) {

            $this->command->info('');

        }
        if ($this->command) {

            $this->command->info('已填充的数据表：');

        }
        if ($this->command) {

            $this->command->info('=== 系统基础数据 ===');

        }
        $this->command->info('- departments (部门)');
        $this->command->info('- users (用户)');
        $this->command->info('- roles (角色)');
        if ($this->command) {

            $this->command->info('');

        }
        if ($this->command) {

            $this->command->info('=== 基础配置数据 ===');

        }
        $this->command->info('- countries (国家)');
        $this->command->info('- apply_types (申请类型)');
        $this->command->info('- process_statuses (处理事项状态)');
        $this->command->info('- fee_configs (费用配置)');
        if ($this->command) {

            $this->command->info('');

        }
        if ($this->command) {

            $this->command->info('=== 代理机构数据 ===');

        }
        $this->command->info('- agencies (代理机构)');
        $this->command->info('- agents (代理师)');
        if ($this->command) {

            $this->command->info('');

        }
        if ($this->command) {

            $this->command->info('=== 业务配置数据 ===');

        }
        $this->command->info('- tech_service_types (科技服务类型)');
        $this->command->info('- manuscript_scoring_items (审核打分项)');
        $this->command->info('- protection_centers (保护中心)');
        $this->command->info('- price_indices (价格指数)');
        $this->command->info('- innovation_indices (创新指数)');
        $this->command->info('- file_descriptions (文件描述设置)');
        $this->command->info('- process_rules (处理事项规则)');
        $this->command->info('- business_service_types (业务服务类型)');
        $this->command->info('- business_statuses (业务状态)');
        $this->command->info('- customer_levels (客户级别)');
        $this->command->info('- customer_scales (客户规模)');
        if ($this->command) {

            $this->command->info('- 以及其他20+个业务配置表');

        }
        if ($this->command) {

            $this->command->info('');

        }
        if ($this->command) {

            $this->command->info('数据填充完成！系统已具备完整的基础数据，可以开始使用了。');

        }
    }
}
