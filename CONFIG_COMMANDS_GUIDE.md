# EMA系统配置项Command使用指南

## 概述
本指南详细说明如何使用Laravel Artisan Command批量导入EMA知识产权管理系统的配置数据。

## Command架构设计

### 基础架构
- **基类**: `BaseConfigImportCommand` - 提供通用的Excel导入功能
- **子类**: 各配置项的具体Command - 继承基类并实现特定逻辑
- **导入方式**: Excel文件导入，使用FastExcel库
- **数据处理**: 清空表 → 导入数据 → 处理数据 → 插入数据

### 命名规范
- **Command类名**: `{配置项英文名}Command`
- **Command签名**: `config:{配置项短名}`
- **Excel文件名**: `{配置项英文名}.xlsx`

---

## 系统设置Command

### 1. 用户管理
```bash
# 导入用户数据
php artisan config:users

# Excel文件: excel/users.xlsx
# 功能: 导入系统用户基础数据，自动加密密码
```

### 2. 角色管理
```bash
# 导入角色数据
php artisan config:roles

# Excel文件: excel/roles.xlsx
# 功能: 导入用户角色定义数据
```

### 3. 权限管理
```bash
# 导入权限数据
php artisan config:permissions

# Excel文件: excel/permissions.xlsx
# 功能: 导入系统权限配置数据
```

### 4. 部门管理
```bash
# 导入部门数据
php artisan config:departments

# Excel文件: excel/departments.xlsx
# 功能: 导入组织架构数据
```

### 5. 通知书规则
```bash
# 导入通知规则数据
php artisan config:notification-rules

# Excel文件: excel/notification_rules.xlsx
# 功能: 导入通知规则配置数据
```

### 6. 处理事项规则
```bash
# 导入处理事项规则数据
php artisan config:process-rules

# Excel文件: excel/process_rules.xlsx
# 功能: 导入业务处理规则数据
```

### 7. 代理机构设置
```bash
# 导入代理机构数据
php artisan config:agencies

# Excel文件: excel/agencies.xlsx
# 功能: 导入代理机构信息数据
```

### 8. 代理师设置
```bash
# 导入代理师数据
php artisan config:agents

# Excel文件: excel/agents.xlsx
# 功能: 导入代理师信息数据
```

### 9. 流程配置
```bash
# 导入流程配置数据
php artisan config:workflows

# Excel文件: excel/workflows.xlsx
# 功能: 导入工作流程定义数据
```

---

## 数据配置Command

### 10. 申请类型设置
```bash
# 导入申请类型数据
php artisan config:apply-types

# Excel文件: excel/apply_types.xlsx
# 功能: 导入知识产权申请类型数据
```

### 11. 处理事项状态设置
```bash
# 导入处理状态数据
php artisan config:process-statuses

# Excel文件: excel/process_statuses.xlsx
# 功能: 导入处理状态定义数据
```

### 12. 费用配置设置
```bash
# 导入费用配置数据
php artisan config:fee-configs

# Excel文件: excel/fee_configs.xlsx
# 功能: 导入费用标准配置数据
```

### 13. 项目系数设置
```bash
# 导入项目系数数据
php artisan config:case-coefficients

# Excel文件: excel/case_coefficients.xlsx
# 功能: 导入项目难度系数数据
```

### 14. 处理事项设置
```bash
# 导入处理事项数据
php artisan config:process-information

# Excel文件: excel/process_information.xlsx
# 功能: 导入处理事项详细信息数据
```

### 15. 处理事项系数设置
```bash
# 导入处理事项系数数据
php artisan config:process-coefficients

# Excel文件: excel/process_coefficients.xlsx
# 功能: 导入处理事项系数数据
```

### 16. 专利年费配置
```bash
# 导入专利年费数据
php artisan config:patent-annual-fees

# Excel文件: excel/patent_annual_fees.xlsx
# 功能: 导入专利年费标准数据
```

### 17. 客户等级设置
```bash
# 导入客户等级数据
php artisan config:customer-levels

# Excel文件: excel/customer_levels.xlsx
# 功能: 导入客户分级标准数据
```

### 18. 开票服务类型设置
```bash
# 导入开票服务类型数据
php artisan config:invoice-services

# Excel文件: excel/invoice_services.xlsx
# 功能: 导入发票服务类型数据
```

### 19. 园区名称设置
```bash
# 导入园区数据
php artisan config:parks

# Excel文件: excel/parks.xlsx
# 功能: 导入产业园区信息数据
```

### 20. 业务服务类型设置
```bash
# 导入业务服务类型数据
php artisan config:business-service-types

# Excel文件: excel/business_service_types.xlsx
# 功能: 导入业务服务分类数据
```

### 21. 客户规模设置
```bash
# 导入客户规模数据
php artisan config:customer-scales

# Excel文件: excel/customer_scales.xlsx
# 功能: 导入客户规模分类数据
```

### 22. 文件大类小类设置
```bash
# 导入文件分类数据
php artisan config:file-categories

# Excel文件: excel/file_categories.xlsx
# 功能: 导入文件分类体系数据
```

### 23. 文件描述设置
```bash
# 导入文件描述数据
php artisan config:file-descriptions

# Excel文件: excel/file_descriptions.xlsx
# 功能: 导入文件描述模板数据
```

### 24. 处理事项类型设置
```bash
# 导入处理事项类型数据
php artisan config:process-types

# Excel文件: excel/process_types.xlsx
# 功能: 导入处理事项分类数据
```

### 25. 我方公司设置
```bash
# 导入我方公司数据
php artisan config:our-companies

# Excel文件: excel/our_companies.xlsx
# 功能: 导入本公司信息数据
```

### 26. 提成类型设置
```bash
# 导入提成类型数据
php artisan config:commission-types

# Excel文件: excel/commission_types.xlsx
# 功能: 导入提成分类数据
```

### 27. 提成配置设置
```bash
# 导入提成配置数据
php artisan config:commission-settings

# Excel文件: excel/commission_settings.xlsx
# 功能: 导入提成计算规则数据
```

### 28. 科技服务类型设置
```bash
# 导入科技服务类型数据
php artisan config:tech-service-types

# Excel文件: excel/tech_service_types.xlsx
# 功能: 导入科技服务分类数据
```

### 29. 科技服务事项设置
```bash
# 导入科技服务事项数据
php artisan config:tech-service-items

# Excel文件: excel/tech_service_items.xlsx
# 功能: 导入科技服务项目数据
```

### 30. 审核打分项设置
```bash
# 导入审核打分项数据
php artisan config:manuscript-scoring-items

# Excel文件: excel/manuscript_scoring_items.xlsx
# 功能: 导入审核评分标准数据
```

### 31. 保护中心设置
```bash
# 导入保护中心数据
php artisan config:protection-centers

# Excel文件: excel/protection_centers.xlsx
# 功能: 导入知识产权保护中心数据
```

### 32. 价格指数设置
```bash
# 导入价格指数数据
php artisan config:price-indices

# Excel文件: excel/price_indices.xlsx
# 功能: 导入价格指数配置数据
```

### 33. 创新指数设置
```bash
# 导入创新指数数据
php artisan config:innovation-indices

# Excel文件: excel/innovation_indices.xlsx
# 功能: 导入创新指数配置数据
```

### 34. 产品设置
```bash
# 导入产品数据
php artisan config:products

# Excel文件: excel/products.xlsx
# 功能: 导入产品信息配置数据
```

---

## 批量执行方案

### 方案一：按依赖顺序执行
```bash
# 1. 基础系统数据
php artisan config:departments
php artisan config:users
php artisan config:roles
php artisan config:permissions

# 2. 基础配置数据
php artisan config:apply-types
php artisan config:process-statuses
php artisan config:process-types
php artisan config:fee-configs

# 3. 业务配置数据
php artisan config:agencies
php artisan config:agents
php artisan config:workflows
php artisan config:process-information

# 4. 其他配置数据
php artisan config:file-categories
php artisan config:products
php artisan config:customer-levels
# ... 其他配置项
```

### 方案二：创建批量执行脚本
创建 `import_all_configs.sh`：
```bash
#!/bin/bash
echo "开始导入所有配置数据..."

# 系统基础配置
php artisan config:departments
php artisan config:users
php artisan config:roles
php artisan config:permissions

# 业务基础配置
php artisan config:apply-types
php artisan config:process-statuses
php artisan config:fee-configs
php artisan config:case-coefficients

# 业务详细配置
php artisan config:process-information
php artisan config:process-coefficients
php artisan config:file-categories
php artisan config:products

# 其他配置（按需添加）
# php artisan config:agencies
# php artisan config:agents
# ...

echo "所有配置数据导入完成！"
```

### 方案三：使用Laravel调度
在 `app/Console/Kernel.php` 中定义：
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('config:import-all')->daily();
}
```

---

## Command执行结果

### 成功输出示例
```
开始导入 users 数据...
清空表: users
读取Excel文件: /path/to/excel/users.xlsx
读取到 10 条数据
导入完成!
成功: 10 条
```

### 错误处理示例
```
开始导入 users 数据...
Excel文件不存在: /path/to/excel/users.xlsx
```

```
开始导入 users 数据...
清空表: users
读取Excel文件: /path/to/excel/users.xlsx
读取到 10 条数据
数据插入失败: SQLSTATE[23000]: Integrity constraint violation
数据内容: {"name":"","email":"invalid-email"}
导入完成!
成功: 8 条
失败: 2 条
```

---

## 高级用法

### 1. 自定义数据处理
继承 `BaseConfigImportCommand` 并重写 `processData` 方法：
```php
protected function processData(array $data): array
{
    // 自定义数据处理逻辑
    if (isset($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }
    
    return $this->addTimestamps($data);
}
```

### 2. 添加验证逻辑
```php
protected function validateData(array $data): bool
{
    // 自定义验证逻辑
    if (empty($data['name'])) {
        return false;
    }
    
    return true;
}
```

### 3. 自定义错误处理
```php
public function handle()
{
    try {
        parent::handle();
    } catch (\Exception $e) {
        $this->error("导入失败: " . $e->getMessage());
        // 发送邮件通知
        // 记录详细日志
        return 1;
    }
}
```

---

## 注意事项

### 1. 数据依赖关系
- 确保按正确的顺序执行Command
- 外键关联的数据必须先导入
- 建议先导入基础数据，再导入业务数据

### 2. 性能优化
- 大批量数据建议分批导入
- 可以临时禁用外键检查提高性能
- 考虑使用数据库事务

### 3. 错误处理
- 导入前备份重要数据
- 检查Excel文件格式和内容
- 注意PHP内存和执行时间限制

### 4. 安全考虑
- 验证Excel文件来源
- 过滤敏感数据
- 记录操作日志

---

## 故障排除

### 常见问题

1. **Excel文件不存在**
   - 检查文件路径是否正确
   - 确认文件名是否与Command中定义一致

2. **数据格式错误**
   - 检查Excel第一行是否为字段名
   - 确认数据类型是否匹配

3. **外键约束错误**
   - 检查关联数据是否存在
   - 按依赖顺序导入数据

4. **内存不足**
   - 调整PHP内存限制
   - 分批处理大文件

5. **权限问题**
   - 检查数据库连接权限
   - 确认文件读取权限

### 调试技巧
```bash
# 开启详细输出
php artisan config:users -v

# 查看具体错误
php artisan config:users --debug

# 检查日志
tail -f storage/logs/laravel.log
```

---

## 扩展开发

如需添加新的配置项Command：

1. **创建Command类**
```php
php artisan make:command Config/NewConfigCommand
```

2. **继承基类并实现必要方法**
```php
class NewConfigCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:new-config';
    protected $description = '导入新配置数据';

    protected function getExcelFileName(): string
    {
        return 'new_config.xlsx';
    }

    protected function getTableName(): string
    {
        return 'new_configs';
    }

    protected function getModelClass(): string
    {
        return NewConfig::class;
    }
}
```

3. **准备Excel模板文件**
4. **测试Command功能**
5. **更新文档说明**

---

## 总结

通过这套Command系统，您可以：
- 快速批量导入所有配置数据
- 使用标准化的Excel模板格式
- 享受统一的错误处理和日志记录
- 轻松扩展新的配置项导入功能

建议在生产环境使用前，先在测试环境充分验证数据的正确性和完整性。
