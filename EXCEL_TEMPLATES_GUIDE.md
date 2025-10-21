# EMA系统Excel模板导入指南

## 概述
本指南详细说明了EMA知识产权管理系统中所有配置项的Excel导入模板格式和字段说明。

## Excel文件存放位置
所有Excel文件都应放在项目根目录的 `excel/` 文件夹下。

## 通用Excel格式要求

### 基本要求
1. **第一行必须是字段名称**（表头）
2. **从第二行开始是数据**
3. **字段名称必须与数据库字段完全一致**
4. **空值字段可以留空，但不要删除列**
5. **日期格式统一使用：YYYY-MM-DD HH:MM:SS**
6. **布尔值使用：1（是/启用）或 0（否/禁用）**

### 文件命名规范
Excel文件名应与Command中定义的文件名一致，格式为：`配置项英文名.xlsx`

---

## 系统设置配置项

### 1. 用户管理 (users.xlsx)
**Command**: `php artisan config:users`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 用户名 | admin |
| email | string | 是 | 邮箱 | admin@example.com |
| password | string | 是 | 密码（明文，系统会自动加密） | 123456 |
| real_name | string | 否 | 真实姓名 | 张三 |
| nickname | string | 否 | 昵称 | 管理员 |
| phone | string | 否 | 手机号 | 13800138000 |
| department_id | integer | 否 | 部门ID | 1 |
| is_active | integer | 否 | 是否激活 | 1 |
| avatar | string | 否 | 头像 | /avatars/admin.jpg |
| last_login_at | datetime | 否 | 最后登录时间 | 2024-01-01 10:00:00 |
| email_verified_at | datetime | 否 | 邮箱验证时间 | 2024-01-01 10:00:00 |

### 2. 角色管理 (roles.xlsx)
**Command**: `php artisan config:roles`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 角色名称 | admin |
| display_name | string | 是 | 显示名称 | 系统管理员 |
| description | string | 否 | 角色描述 | 系统管理员角色 |
| is_active | integer | 否 | 是否启用 | 1 |
| sort | integer | 否 | 排序 | 1 |
| level | integer | 否 | 角色级别 | 1 |

### 3. 权限管理 (permissions.xlsx)
**Command**: `php artisan config:permissions`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 权限名称 | user.create |
| display_name | string | 是 | 显示名称 | 创建用户 |
| description | string | 否 | 权限描述 | 创建新用户的权限 |
| guard_name | string | 否 | 守护名称 | web |
| module | string | 否 | 模块 | user |
| action | string | 否 | 操作 | create |
| resource | string | 否 | 资源 | user |
| is_active | integer | 否 | 是否启用 | 1 |

### 4. 部门管理 (departments.xlsx)
**Command**: `php artisan config:departments`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 部门名称 | 技术部 |
| code | string | 否 | 部门编码 | TECH |
| parent_id | integer | 否 | 上级部门ID | 1 |
| manager_id | integer | 否 | 部门经理ID | 2 |
| description | string | 否 | 部门描述 | 负责技术开发 |
| sort | integer | 否 | 排序 | 1 |
| is_active | integer | 否 | 是否启用 | 1 |
| phone | string | 否 | 部门电话 | 010-12345678 |
| email | string | 否 | 部门邮箱 | tech@example.com |
| address | string | 否 | 部门地址 | 北京市朝阳区 |

### 5. 通知书规则 (notification_rules.xlsx)
**Command**: `php artisan config:notification-rules`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| rule_name | string | 是 | 规则名称 | 专利答复通知 |
| notification_type | string | 是 | 通知类型 | 审查意见 |
| case_type | string | 否 | 案件类型 | 专利 |
| business_type | string | 否 | 业务类型 | 发明专利 |
| country | string | 否 | 国家 | 中国 |
| trigger_condition | string | 否 | 触发条件 | 收到审查意见通知书 |
| notification_content | text | 否 | 通知内容 | 您的专利申请收到审查意见 |
| recipients | string | 否 | 接收人 | 代理师,客户 |
| is_active | integer | 否 | 是否启用 | 1 |
| priority | integer | 否 | 优先级 | 1 |
| delay_days | integer | 否 | 延迟天数 | 0 |

---

## 数据配置项

### 6. 申请类型设置 (apply_types.xlsx)
**Command**: `php artisan config:apply-types`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 申请类型名称 | 发明专利 |
| code | string | 否 | 申请类型代码 | INVENTION |
| category | string | 否 | 类别 | 专利 |
| description | string | 否 | 描述 | 发明专利申请 |
| is_active | integer | 否 | 是否启用 | 1 |
| sort | integer | 否 | 排序 | 1 |
| parent_id | integer | 否 | 父级ID | 0 |

### 7. 处理事项状态设置 (process_statuses.xlsx)
**Command**: `php artisan config:process-statuses`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 状态名称 | 待处理 |
| code | string | 否 | 状态代码 | PENDING |
| category | string | 否 | 状态分类 | 处理中 |
| color | string | 否 | 状态颜色 | #ff9900 |
| description | string | 否 | 描述 | 等待处理的状态 |
| is_active | integer | 否 | 是否启用 | 1 |
| sort | integer | 否 | 排序 | 1 |
| is_final | integer | 否 | 是否为最终状态 | 0 |

### 8. 费用配置设置 (fee_configs.xlsx)
**Command**: `php artisan config:fee-configs`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| name | string | 是 | 费用名称 | 发明专利申请费 |
| code | string | 否 | 费用代码 | INVENTION_APP |
| category | string | 否 | 费用类别 | 官费 |
| amount | decimal | 是 | 费用金额 | 900.00 |
| currency | string | 否 | 货币单位 | CNY |
| country | string | 否 | 适用国家 | 中国 |
| case_type | string | 否 | 案件类型 | 专利 |
| is_active | integer | 否 | 是否启用 | 1 |
| description | string | 否 | 描述 | 发明专利申请官费 |

### 9. 项目系数设置 (case_coefficients.xlsx)
**Command**: `php artisan config:case-coefficients`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| case_type | string | 是 | 案件类型 | 专利 |
| business_type | string | 否 | 业务类型 | 发明专利 |
| project_type | string | 否 | 项目类型 | 新申请 |
| coefficient | decimal | 是 | 系数值 | 1.5 |
| description | string | 否 | 描述 | 发明专利难度系数 |
| is_active | integer | 否 | 是否启用 | 1 |
| effective_date | date | 否 | 生效日期 | 2024-01-01 |
| expiry_date | date | 否 | 失效日期 | 2024-12-31 |

### 10. 处理事项设置 (process_information.xlsx)
**Command**: `php artisan config:process-information`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| case_type | string | 是 | 案件类型 | 专利 |
| business_type | string | 否 | 业务类型（JSON数组） | ["发明专利","实用新型专利"] |
| application_type | string | 否 | 申请类型（JSON数组） | ["新申请","PCT申请"] |
| country | string | 否 | 国家（JSON数组） | ["中国","美国"] |
| process_name | string | 是 | 处理事项名称 | 专利申请文件撰写 |
| flow_completed | integer | 否 | 流程是否完成 | 0 |
| proposal_inquiry | integer | 否 | 提案询问 | 1 |
| data_updater_inquiry | integer | 否 | 数据更新询问 | 1 |
| update_case_handler | integer | 否 | 更新案件处理人 | 1 |
| process_status | string | 否 | 处理状态（JSON数组） | ["草稿","待提交"] |
| case_phase | string | 否 | 案件阶段 | 申请阶段 |
| process_type | string | 否 | 处理事项类型 | 撰写类 |
| is_case_node | integer | 否 | 是否为案件节点 | 1 |
| is_commission | integer | 否 | 是否提成 | 1 |
| is_valid | integer | 否 | 是否有效 | 1 |
| sort_order | integer | 否 | 排序 | 1 |
| consultant_contract | integer | 否 | 顾问合同 | 1 |

---

## 其他配置项

### 文件分类设置 (file_categories.xlsx)
**Command**: `php artisan config:file-categories`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| main_category | string | 是 | 文件大类 | 专利申请文件 |
| sub_category | string | 是 | 文件小类 | 发明专利申请书 |
| is_valid | integer | 否 | 是否有效 | 1 |
| sort | integer | 否 | 排序 | 1 |

### 产品设置 (products.xlsx)
**Command**: `php artisan config:products`

| 字段名 | 类型 | 必填 | 说明 | 示例 |
|--------|------|------|------|------|
| sort | integer | 否 | 排序 | 1 |
| product_code | string | 是 | 产品编码 | PT001 |
| project_type | string | 是 | 项目类型 | 专利 |
| apply_type | string | 是 | 申请类型 | 发明专利 |
| specification | string | 否 | 规格说明 | 标准版 |
| product_name | string | 是 | 产品名称 | 发明专利申请（标准版） |
| official_fee | decimal | 否 | 官费 | 900.00 |
| standard_price | decimal | 是 | 标准价格 | 8000.00 |
| min_price | decimal | 否 | 最低价格 | 6000.00 |
| is_valid | integer | 否 | 是否有效 | 1 |
| update_user | integer | 否 | 更新人ID | 1 |

---

## Excel模板制作建议

### 1. 数据准备
- 先在系统中手动创建几条示例数据
- 导出现有数据作为模板参考
- 确保数据格式正确

### 2. 批量导入
- 建议先小批量测试（10-20条数据）
- 验证导入结果无误后再大批量导入
- 重要数据请先备份

### 3. 常见问题
- **日期格式错误**：使用 YYYY-MM-DD HH:MM:SS 格式
- **布尔值错误**：使用 1 或 0，不要使用 true/false
- **JSON字段格式**：使用标准JSON格式，如 ["值1","值2"]
- **外键关联**：确保关联的ID在相关表中存在

### 4. 数据验证
导入完成后建议：
1. 检查数据总数是否正确
2. 验证关键字段是否正确
3. 测试前端界面显示是否正常
4. 检查关联关系是否正确

---

## 注意事项

1. **Excel文件编码**：建议使用UTF-8编码保存Excel文件
2. **数据备份**：导入前请备份现有数据
3. **权限检查**：确保有足够的数据库操作权限
4. **内存限制**：大批量数据导入时注意PHP内存限制
5. **事务处理**：导入过程中如出错会回滚，不会产生脏数据

## 技术支持

如遇到导入问题，请检查：
1. Excel文件格式是否正确
2. 字段名称是否与要求一致
3. 数据类型是否匹配
4. 必填字段是否有值
5. 外键关联是否存在

更多技术支持请参考系统日志或联系开发团队。
