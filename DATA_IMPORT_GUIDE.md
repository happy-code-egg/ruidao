# EMA系统数据导入使用指南

## 概述
本指南详细说明如何使用Laravel Seeder系统为EMA知识产权管理系统导入基础配置数据。

## 新增的Seeder文件

### 1. ProcessInformationSeeder.php
**用途**: 导入处理事项设置的基础数据  
**对应配置项**: 处理事项设置 (/config/data/process-information)  
**数据内容**: 
- 专利相关处理事项（申请文件撰写、实质审查请求、答复审查意见等）
- 商标相关处理事项（申请文件准备、驳回复审、续展申请等）
- 版权相关处理事项（版权登记申请、版权补正等）
- 科技服务相关处理事项（高新认定、政府项目申报等）
- 通用处理事项（客户沟通、文件归档等）

### 2. FileCategoriesSeeder.php
**用途**: 导入文件大类小类设置的基础数据  
**对应配置项**: 文件大类小类设置 (/config/data/file-category)  
**数据内容**:
- 专利申请文件（申请书、权利要求书、说明书等）
- 专利程序文件（实质审查请求书、费用减缴请求书等）
- 专利答复文件（意见陈述书、修改页等）
- 商标申请文件（商标注册申请书、商标图样等）
- 商标程序文件（续展申请书、转让申请书等）
- 版权申请文件（著作权登记申请表、作品样本等）
- 科技服务文件（高新认定申请书、审计报告等）
- 合同文件、财务文件、客户资料、官方文件等

### 3. ProductsSeeder.php
**用途**: 导入产品设置的基础数据  
**对应配置项**: 产品设置 (/config/data/product)  
**数据内容**:
- 专利产品（发明专利申请、实用新型专利申请、外观设计专利申请等）
- 商标产品（商标注册申请、商标续展、商标转让等）
- 版权产品（软件著作权登记、作品著作权登记等）
- 科技服务产品（高新认定、专精特新认定、政府项目申报等）
- 国际业务产品（PCT申请、马德里申请等）
- 咨询服务产品（专利检索、知识产权培训等）

## 执行方法

### 1. 执行所有Seeder
```bash
# 进入项目目录
cd ema_api

# 执行所有种子文件
php artisan db:seed
```

### 2. 执行特定Seeder
```bash
# 执行处理事项设置数据导入
php artisan db:seed --class=ProcessInformationSeeder

# 执行文件分类数据导入
php artisan db:seed --class=FileCategoriesSeeder

# 执行产品设置数据导入
php artisan db:seed --class=ProductsSeeder
```

### 3. 重新执行Seeder（清空并重新导入）
```bash
# 刷新数据库并重新执行所有seeder
php artisan migrate:fresh --seed

# 或者先清空特定表再执行对应seeder
php artisan db:seed --class=ProcessInformationSeeder
```

## 数据导入顺序

按照依赖关系，建议的导入顺序如下：

### 第一阶段：系统基础数据
1. DepartmentsSeeder（部门）
2. UsersSeeder（用户）
3. RolesSeeder（角色）
4. PermissionsSeeder（权限）

### 第二阶段：基础配置数据
1. CountriesSeeder（国家）
2. ApplyTypesSeeder（申请类型）
3. ProcessStatusesSeeder（处理事项状态）
4. FeeConfigsSeeder（费用配置）

### 第三阶段：业务配置数据
1. ProcessInformationSeeder（处理事项设置）- **新增**
2. FileCategoriesSeeder（文件分类设置）- **新增**
3. ProductsSeeder（产品设置）- **新增**
4. 其他业务配置seeder...

## 数据验证

### 1. 检查数据是否导入成功
```bash
# 检查处理事项数据
php artisan tinker
>>> App\Models\ProcessInformation::count()
>>> App\Models\ProcessInformation::first()

# 检查文件分类数据
>>> App\Models\FileCategories::count()
>>> App\Models\FileCategories::first()

# 检查产品数据
>>> App\Models\Product::count()
>>> App\Models\Product::first()
```

### 2. 通过前端界面验证
- 登录系统
- 进入"配置"菜单
- 检查对应的配置项是否显示数据
- 验证数据的完整性和正确性

## 自定义数据

### 1. 修改Seeder文件
如需修改基础数据，可以直接编辑对应的Seeder文件：
- `database/seeders/ProcessInformationSeeder.php`
- `database/seeders/FileCategoriesSeeder.php`
- `database/seeders/ProductsSeeder.php`

### 2. 添加新数据
在对应的数组中添加新的数据项，格式参考现有数据。

### 3. 重新执行导入
修改后重新执行对应的seeder：
```bash
php artisan db:seed --class=ProcessInformationSeeder
```

## 注意事项

### 1. 数据重复处理
- 所有新增的seeder都包含重复检查逻辑
- 重复执行不会创建重复数据
- 如需强制重新导入，可先清空对应表

### 2. 数据依赖关系
- 确保用户表有ID为1的用户（用于created_by和updated_by字段）
- 部分数据可能依赖其他配置表的数据

### 3. 生产环境使用
- 生产环境执行前务必备份数据库
- 建议先在测试环境验证
- 可以选择性执行特定的seeder

### 4. 数据更新
- 修改现有数据建议通过管理界面操作
- 批量更新可以编写专门的数据迁移脚本

## 故障排除

### 1. 外键约束错误
```bash
# 可能原因：缺少依赖数据（如用户ID）
# 解决方案：先执行基础数据seeder
php artisan db:seed --class=UsersSeeder
```

### 2. 表不存在错误
```bash
# 可能原因：未执行数据库迁移
# 解决方案：先执行迁移
php artisan migrate
```

### 3. 权限错误
```bash
# 可能原因：数据库连接权限不足
# 解决方案：检查.env文件中的数据库配置
```

## 配置项完整对应关系

| 前端配置项 | Seeder文件 | 数据表 | 状态 |
|-----------|-----------|--------|------|
| 处理事项设置 | ProcessInformationSeeder.php | process_informations | ✅ 新增 |
| 文件大类小类设置 | FileCategoriesSeeder.php | file_categories | ✅ 新增 |
| 产品设置 | ProductsSeeder.php | products | ✅ 新增 |
| 用户管理 | UsersSeeder.php | users | ✅ 已存在 |
| 角色管理 | RolesSeeder.php | roles | ✅ 已存在 |
| 权限管理 | PermissionsSeeder.php | permissions | ✅ 已存在 |
| 部门管理 | DepartmentsSeeder.php | departments | ✅ 已存在 |
| 申请类型设置 | ApplyTypesSeeder.php | apply_types | ✅ 已存在 |
| 费用配置设置 | FeeConfigsSeeder.php | fee_configs | ✅ 已存在 |
| 项目系数设置 | CaseCoefficientsSeeder.php | case_coefficients | ✅ 已存在 |
| ...其他配置项... | ...对应seeder... | ...对应表... | ✅ 已存在 |

## 更新日志
- 2024-XX-XX: 创建ProcessInformationSeeder.php
- 2024-XX-XX: 创建FileCategoriesSeeder.php  
- 2024-XX-XX: 创建ProductsSeeder.php
- 2024-XX-XX: 更新DatabaseSeeder.php包含新增seeder
