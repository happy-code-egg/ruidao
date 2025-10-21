# 工作流数据更新说明

## 概述

本次更新将所有工作流程从原来的3-5个节点扩展到8个节点，并根据业务需求更新了流程配置。

## 更新内容

### 1. 前端更新
- **文件**: `ema/src/views/config/system/workflow.vue`
- **功能**: 
  - 节点名称可编辑（在非查看模式下）
  - 所有流程扩展到8个节点
  - 更新了预定义工作流程数据

### 2. 后端数据库更新
- **Seeder文件**: `ema_api/database/seeders/WorkflowSeeder.php`
- **SQL脚本**: `ema_api/database/update_workflows.sql`
- **Artisan命令**: 创建了4个命令来分步更新数据

## 工作流程列表

| ID | 流程名称 | 代码 | 类型 | 描述 |
|----|----------|------|------|------|
| 1 | 合同流程 | CONTRACT_FLOW | 合同 | 合同审批流程，只有第一个节点可选到客户资料记录的业务员的主管审核 |
| 2 | 立案流程(商版专) | CASE_SIMPLE_FLOW | 专利 | 立案流程（商版专），根据合同的某个状态，使这里有可以开始处理的数据 |
| 3 | 立案流程（科服） | CASE_TECH_FLOW | 专利 | 立案流程（科服），根据合同的某个状态，使这里有可以开始处理的数据 |
| 4 | 配案流程 | ASSIGN_FLOW | 通用 | 配案流程，流动后，就给到某个人分配 |
| 5 | 核稿流程 | CHECK_FLOW | 通用 | 核稿流程，启动的时候，要选一个核稿人 |
| 6 | 递交流程 | SUBMIT_FLOW | 通用 | 递交流程，涉及到达流程初核这个节点，要记录日期的问题 |
| 7 | 案件更新 | CASE_UPDATE_FLOW | 通用 | 案件更新流程，涉及到，如果是处理事项改成完成，不要要执行完成规则的问题 |
| 8 | 请款 | PAYMENT_FLOW | 财务 | 请款流程，涉及，填写请款的时候，如果正在请款中，没有走完流程的，不能再次提交 |
| 9 | 收款 | RECEIVE_FLOW | 财务 | 收款流程 |
| 10 | 开票 | INVOICE_FLOW | 财务 | 开票流程，没有走完的流程，可以撤回和删除 |
| 11 | 支出 | EXPENSE_FLOW | 财务 | 支出流程，没有走完的流程，可以撤回和删除 |
| 12 | 缴费 | PAY_FEE_FLOW | 财务 | 缴费流程 |
| 13 | 运营提成 | OPERATION_COMMISSION_FLOW | 财务 | 运营提成流程，没有走完的流程，可以撤回和删除 |
| 14 | 商务提成 | BUSINESS_COMMISSION_FLOW | 财务 | 商务提成流程，没有走完的流程，可以撤回和删除 |

## 节点结构

每个工作流程现在包含8个节点：
1. **启动节点** - 流程启动（通常设为自动通过）
2. **节点2-7** - 业务处理节点（根据具体流程配置）
3. **结束节点** - 流程结束（通常设为自动通过）

### 节点属性
- `name`: 节点名称（可编辑）
- `type`: 节点类型（启动、审核、处理、分配、检查、确认、结束）
- `description`: 节点描述
- `assignee`: 处理人员列表
- `timeLimit`: 处理时限（小时）
- `required`: 是否必需（true=必需审核，false=自动通过）

## 如何更新数据库

### 方法1：使用批处理脚本（推荐）

**Windows:**
```bash
cd ema_api
update_workflows.bat
```

**Linux/Mac:**
```bash
cd ema_api
chmod +x update_workflows.sh
./update_workflows.sh
```

### 方法2：手动执行Artisan命令

```bash
cd ema_api

# 第1步：更新前3个工作流
php artisan workflows:update

# 第2步：插入工作流4-7
php artisan workflows:update-remaining

# 第3步：插入工作流8-11
php artisan workflows:update-final

# 第4步：插入工作流12-14
php artisan workflows:update-commission
```

### 方法3：直接执行SQL脚本

```bash
# 在MySQL中执行
mysql -u username -p database_name < ema_api/database/update_workflows.sql
```

### 方法4：使用Laravel Seeder

```bash
cd ema_api
php artisan db:seed --class=WorkflowSeeder
```

## 验证更新

更新完成后，可以通过以下方式验证：

1. **检查数据库记录数量**:
```sql
SELECT COUNT(*) FROM workflows;
-- 应该返回 14
```

2. **检查节点数量**:
```sql
SELECT name, JSON_LENGTH(nodes) as node_count FROM workflows;
-- 每个工作流应该有 8 个节点
```

3. **访问前端页面**:
   - 打开工作流配置页面
   - 检查是否显示14个工作流
   - 测试节点名称编辑功能

## 注意事项

1. **备份数据**: 更新前请备份现有的工作流数据
2. **权限检查**: 确保数据库用户有足够的权限执行TRUNCATE和INSERT操作
3. **依赖关系**: 如果有其他表引用工作流ID，请注意数据一致性
4. **缓存清理**: 更新后可能需要清理应用缓存

## 故障排除

### 常见问题

1. **权限不足**:
```
ERROR 1142 (42000): DROP command denied to user
```
解决方案：使用具有足够权限的数据库用户

2. **外键约束**:
```
ERROR 1451 (23000): Cannot delete or update a parent row
```
解决方案：临时禁用外键检查或先删除相关数据

3. **JSON格式错误**:
```
ERROR 3140 (22032): Invalid JSON text
```
解决方案：检查nodes字段的JSON格式是否正确

### 回滚操作

如果需要回滚到原来的数据，可以：

1. 从备份恢复数据
2. 或者重新运行原来的WorkflowSeeder

```bash
# 恢复到原始状态（如果有备份的seeder）
php artisan db:seed --class=OriginalWorkflowSeeder
```

## 联系支持

如果在更新过程中遇到问题，请联系技术支持团队。
