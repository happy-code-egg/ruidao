# EMA系统配置项与数据导入文件对应关系

## 概述
本文档详细列出了MainLayout.vue中所有配置项与后端数据导入文件(Seeder)的对应关系，以及数据导入的处理建议。

## 系统设置 (/config/system)

| 配置项 | 路由 | 对应Seeder文件 | 状态 | 说明 |
|--------|------|----------------|------|------|
| 用户管理 | /config/system/user | UsersSeeder.php | ✅ 已存在 | 系统用户基础数据 |
| 角色管理 | /config/system/role | RolesSeeder.php | ✅ 已存在 | 用户角色定义 |
| 权限管理 | /config/system/permission | PermissionsSeeder.php | ✅ 已存在 | 系统权限配置 |
| 部门管理 | /config/system/department | DepartmentsSeeder.php | ✅ 已存在 | 组织架构数据 |
| 通知书规则 | /config/system/notification-rule | NotificationRulesSeeder.php | ✅ 已存在 | 通知规则配置 |
| 处理事项规则 | /config/system/process-rule | ProcessRulesSeeder.php | ✅ 已存在 | 业务处理规则 |
| 代理机构设置 | /config/data/agency | AgenciesSeeder.php | ✅ 已存在 | 代理机构信息 |
| 代理师设置 | /config/data/agent | AgentsSeeder.php | ✅ 已存在 | 代理师信息 |
| 流程配置 | /config/data/workflow | WorkflowSeeder.php<br>WorkflowsSeeder.php | ✅ 已存在 | 工作流程定义 |
| 系统日志 | /config/system/log | - | ⚠️ 运行时生成 | 系统运行日志，无需导入 |

## 数据配置 (/config/data)

| 配置项 | 路由 | 对应Seeder文件 | 状态 | 说明 |
|--------|------|----------------|------|------|
| 申请类型设置 | /config/data/apply-type | ApplyTypesSeeder.php | ✅ 已存在 | 知识产权申请类型 |
| 处理事项状态设置 | /config/data/process-status | ProcessStatusesSeeder.php | ✅ 已存在 | 处理状态定义 |
| 费用配置设置 | /config/data/fee-config | FeeConfigsSeeder.php | ✅ 已存在 | 费用标准配置 |
| 项目系数设置 | /config/data/case-coefficient | CaseCoefficientsSeeder.php | ✅ 已存在 | 项目难度系数 |
| 处理事项设置 | /config/data/process-information | ProcessInformationSeeder.php | ❌ 需创建 | 处理事项详细信息 |
| 处理事项系数设置 | /config/data/process-coefficient | ProcessCoefficientsSeeder.php | ✅ 已存在 | 处理事项系数 |
| 专利年费配置 | /config/data/patent-annual-fee | PatentAnnualFeesSeeder.php | ✅ 已存在 | 专利年费标准 |
| 客户等级设置 | /config/data/customer-level | CustomerLevelsSeeder.php | ✅ 已存在 | 客户分级标准 |
| 开票服务类型设置 | /config/data/invoice-service | InvoiceServicesSeeder.php | ✅ 已存在 | 发票服务类型 |
| 园区名称设置 | /config/data/park | ParksConfigSeeder.php | ✅ 已存在 | 产业园区信息 |
| 业务服务类型设置 | /config/data/business-service | BusinessServiceTypesSeeder.php | ✅ 已存在 | 业务服务分类 |
| 客户规模设置 | /config/data/customer-scale | CustomerScalesSeeder.php | ✅ 已存在 | 客户规模分类 |
| 文件大类小类设置 | /config/data/file-category | FileCategoriesSeeder.php | ❌ 需创建 | 文件分类体系 |
| 文件描述设置 | /config/data/file-description | FileDescriptionsSeeder.php | ✅ 已存在 | 文件描述模板 |
| 处理事项类型设置 | /config/data/process-type | ProcessTypesSeeder.php | ✅ 已存在 | 处理事项分类 |
| 我方公司设置 | /config/data/our-company | OurCompaniesSeeder.php | ✅ 已存在 | 本公司信息 |
| 提成类型设置 | /config/data/commission-type | CommissionTypesSeeder.php | ✅ 已存在 | 提成分类 |
| 提成配置设置 | /config/data/commission-setting | CommissionSettingsSeeder.php | ✅ 已存在 | 提成计算规则 |
| 科技服务类型设置 | /config/data/tech-service-type | TechServiceTypesSeeder.php | ✅ 已存在 | 科技服务分类 |
| 科技服务事项设置 | /config/data/tech-service-item | TechServiceItemsSeeder.php | ✅ 已存在 | 科技服务项目 |
| 审核打分项设置 | /config/data/manuscript-scoring | ManuscriptScoringItemsSeeder.php | ✅ 已存在 | 审核评分标准 |
| 保护中心设置 | /config/data/protection-center | ProtectionCentersSeeder.php | ✅ 已存在 | 知识产权保护中心 |
| 价格指数设置 | /config/data/price-index | PriceIndicesSeeder.php | ✅ 已存在 | 价格指数配置 |
| 创新指数设置 | /config/data/innovation-index | InnovationIndicesSeeder.php | ✅ 已存在 | 创新指数配置 |
| 产品设置 | /config/data/product | ProductsSeeder.php | ❌ 需创建 | 产品信息配置 |

## 需要创建的Seeder文件

### 1. ProcessInformationSeeder.php
- **用途**: 处理事项设置的基础数据
- **对应模型**: ProcessInformation.php
- **数据内容**: 各种处理事项的详细信息、流程步骤等

### 2. FileCategoriesSeeder.php  
- **用途**: 文件大类小类设置的基础数据
- **对应模型**: FileCategories.php
- **数据内容**: 文件分类层级结构、类别名称等

### 3. ProductsSeeder.php
- **用途**: 产品设置的基础数据  
- **对应模型**: Product.php
- **数据内容**: 公司产品信息、产品分类等

## 数据导入执行建议

### 执行顺序
1. **基础配置数据** (最高优先级)
   - DepartmentsSeeder
   - RolesSeeder  
   - PermissionsSeeder
   - UsersSeeder

2. **业务基础数据** (高优先级)
   - ApplyTypesSeeder
   - ProcessTypesSeeder
   - ProcessStatusesSeeder
   - ProcessInformationSeeder (新建)

3. **业务配置数据** (中优先级)
   - CaseCoefficientsSeeder
   - ProcessCoefficientsSeeder
   - FeeConfigsSeeder
   - PatentAnnualFeesSeeder

4. **辅助配置数据** (低优先级)
   - FileCategoriesSeeder (新建)
   - FileDescriptionsSeeder
   - ProductsSeeder (新建)
   - 其他业务配置seeder

### 执行方法
```bash
# 执行所有seeder
php artisan db:seed

# 执行特定seeder
php artisan db:seed --class=ProcessInformationSeeder
php artisan db:seed --class=FileCategoriesSeeder  
php artisan db:seed --class=ProductsSeeder
```

## 注意事项

1. **数据依赖关系**: 确保按照依赖顺序执行seeder，避免外键约束错误
2. **数据唯一性**: 在seeder中添加重复检查，避免重复导入
3. **环境区分**: 区分开发、测试、生产环境的数据量和内容
4. **备份机制**: 在生产环境执行前做好数据备份
5. **版本控制**: 新增的seeder文件需要纳入版本控制

## 更新记录
- 2024-01-XX: 初始创建配置项映射关系
- 2024-01-XX: 识别需要创建的seeder文件
