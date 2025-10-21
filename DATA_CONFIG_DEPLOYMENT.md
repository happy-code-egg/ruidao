# 数据配置功能部署说明

本文档说明如何部署四个数据配置页面的功能：项目系数、处理事项系数、处理事项信息、专利年费配置。

## 功能概述

1. **项目系数设置** (`case-coefficient.vue`)
   - 基础的CRUD操作
   - 字段：名称、排序、是否有效、更新人、更新时间

2. **处理事项系数设置** (`process-coefficient.vue`)
   - 基础的CRUD操作
   - 字段：名称、排序、是否有效、更新人、更新时间

3. **处理事项信息设置** (`process-information.vue`)
   - 复杂表单，包含多选字段
   - 字段：项目类型、业务类型、申请类型(多选)、国家、处理事项名称等

4. **专利年费配置** (`patent-annual-fee.vue`)
   - 主表+子表结构
   - 主表：项目类型、申请类型、国家、起算日、币别等
   - 子表：年费详情（阶段、年度、费用等）

## 部署步骤

### 1. 数据库迁移

执行以下命令创建数据库表：

```bash
cd ema_api
php artisan migrate
```

新增的迁移文件：
- `2025_08_08_130001_create_case_coefficients_table.php`
- `2025_08_08_130002_create_process_coefficients_table.php`
- `2025_08_08_130003_create_process_informations_table.php`
- `2025_08_08_130004_create_patent_annual_fees_table.php`
- `2025_08_08_130005_create_patent_annual_fee_details_table.php`

### 2. 种子数据

执行以下命令插入模拟数据：

```bash
php artisan db:seed --class=CaseCoefficientsSeeder
php artisan db:seed --class=ProcessCoefficientsSeeder
php artisan db:seed --class=ProcessInformationsSeeder
php artisan db:seed --class=PatentAnnualFeesSeeder
```

或者执行完整的种子数据：

```bash
php artisan db:seed
```

### 3. 验证部署

运行测试脚本验证数据库表和数据：

```bash
php test_data_config.php
```

### 4. 前端构建

如果需要重新构建前端：

```bash
cd ema
npm run build
```

## 文件清单

### 后端文件

#### 数据库迁移文件
- `database/migrations/2025_08_08_130001_create_case_coefficients_table.php`
- `database/migrations/2025_08_08_130002_create_process_coefficients_table.php`
- `database/migrations/2025_08_08_130003_create_process_informations_table.php`
- `database/migrations/2025_08_08_130004_create_patent_annual_fees_table.php`
- `database/migrations/2025_08_08_130005_create_patent_annual_fee_details_table.php`

#### 模型文件
- `app/Models/CaseCoefficient.php` (已更新)
- `app/Models/ProcessCoefficient.php` (已更新)
- `app/Models/ProcessInformation.php` (已更新)
- `app/Models/PatentAnnualFee.php` (新建)
- `app/Models/PatentAnnualFeeDetail.php` (新建)

#### 控制器文件
- `app/Http/Controllers/Api/CaseCoefficientsController.php` (新建)
- `app/Http/Controllers/Api/ProcessCoefficientsController.php` (新建)
- `app/Http/Controllers/Api/ProcessInformationsController.php` (新建)
- `app/Http/Controllers/Api/PatentAnnualFeesController.php` (新建)

#### 种子文件
- `database/seeders/CaseCoefficientsSeeder.php`
- `database/seeders/ProcessCoefficientsSeeder.php`
- `database/seeders/ProcessInformationsSeeder.php`
- `database/seeders/PatentAnnualFeesSeeder.php`
- `database/seeders/DatabaseSeeder.php` (已更新)

#### 路由文件
- `routes/api.php` (已更新，新增API路由)

### 前端文件

#### API接口文件
- `src/api/data-config.js` (已更新，新增专利年费配置接口)

#### 页面文件
- `src/views/config/data/case-coefficient.vue` (已更新)
- `src/views/config/data/process-coefficient.vue` (已更新)
- `src/views/config/data/process-information.vue` (已更新)
- `src/views/config/data/patent-annual-fee.vue` (已更新)

## API接口说明

### 项目系数接口
- `GET /api/data-config/case-coefficients` - 获取列表
- `POST /api/data-config/case-coefficients` - 创建
- `GET /api/data-config/case-coefficients/{id}` - 获取详情
- `PUT /api/data-config/case-coefficients/{id}` - 更新
- `DELETE /api/data-config/case-coefficients/{id}` - 删除

### 处理事项系数接口
- `GET /api/data-config/process-coefficients` - 获取列表
- `POST /api/data-config/process-coefficients` - 创建
- `GET /api/data-config/process-coefficients/{id}` - 获取详情
- `PUT /api/data-config/process-coefficients/{id}` - 更新
- `DELETE /api/data-config/process-coefficients/{id}` - 删除

### 处理事项信息接口
- `GET /api/data-config/process-informations` - 获取列表
- `POST /api/data-config/process-informations` - 创建
- `GET /api/data-config/process-informations/{id}` - 获取详情
- `PUT /api/data-config/process-informations/{id}` - 更新
- `DELETE /api/data-config/process-informations/{id}` - 删除

### 专利年费配置接口
- `GET /api/data-config/patent-annual-fees` - 获取列表
- `POST /api/data-config/patent-annual-fees` - 创建
- `GET /api/data-config/patent-annual-fees/{id}` - 获取详情
- `PUT /api/data-config/patent-annual-fees/{id}` - 更新
- `DELETE /api/data-config/patent-annual-fees/{id}` - 删除
- `GET /api/data-config/patent-annual-fees/{id}/details` - 获取年费详情
- `POST /api/data-config/patent-annual-fee-details` - 创建年费详情
- `PUT /api/data-config/patent-annual-fee-details/{id}` - 更新年费详情
- `DELETE /api/data-config/patent-annual-fee-details/{id}` - 删除年费详情

## 注意事项

1. 所有字段名使用snake_case命名规范
2. 状态字段使用整数类型（1=有效，0=无效）
3. 多选字段使用JSON格式存储
4. 专利年费配置使用主表+子表的一对多关系
5. 所有接口都有统一的错误处理和响应格式
6. 前端页面保持原有的UI风格和交互模式

## 测试建议

1. 验证数据库表结构是否正确创建
2. 验证种子数据是否正确插入
3. 测试各个页面的CRUD功能
4. 测试搜索和分页功能
5. 测试专利年费配置的主表和子表联动功能
6. 验证表单验证是否正常工作
7. 测试多选字段的保存和显示
