# 合同类型字段更新说明

## 概述

本次更新为合同系统添加了**标准合同/非标合同**的分类功能，并相应地调整了服务类型字段的处理逻辑。

## 更新内容

### 1. 数据库变更

#### 新增字段
- `contracts.contract_type` - 合同类型字段
  - 类型：`VARCHAR(20)`
  - 默认值：`'standard'`
  - 可选值：`'standard'` (标准合同) 或 `'non-standard'` (非标合同)

#### 字段修改
- `contracts.service_type` - 服务类型字段
  - 原类型：`VARCHAR(255)`
  - 新类型：`JSON`
  - 用途：
    - 标准合同：存储单个服务类型字符串（JSON格式）
    - 非标合同：存储多个服务类型的数组

### 2. 后端模型更新

#### Contract 模型新增功能
- 添加了 `contract_type` 到 `$fillable` 数组
- 添加了 `service_type` 的 JSON 类型转换
- 新增常量：
  ```php
  const TYPE_STANDARD = 'standard';
  const TYPE_NON_STANDARD = 'non-standard';
  ```
- 新增辅助方法：
  - `getServiceTypeTextAttribute()` - 获取服务类型显示文本
  - `isStandardContract()` - 检查是否为标准合同
  - `isNonStandardContract()` - 检查是否为非标合同
  - `validateServiceTypeFormat()` - 验证服务类型格式

### 3. API 控制器更新

#### 验证规则更新
- 添加了 `contract_type` 必填验证
- 添加了 `service_type` 的动态验证：
  - 标准合同：必须为字符串
  - 非标合同：必须为非空数组

#### 数据处理优化
- 自动处理服务类型格式转换
- 更新了搜索功能以支持 JSON 字段查询
- 添加了合同类型筛选功能

### 4. 前端界面更新

#### 新增功能
- 合同类型选择字段（标准合同/非标合同）
- 根据合同类型动态切换服务类型选择方式：
  - 标准合同：单选下拉框
  - 非标合同：多选下拉框（支持标签折叠）

#### 用户体验优化
- 智能切换：选择合同类型后自动切换服务类型选择模式
- 清晰提示：未选择合同类型时禁用服务类型选择
- 数据兼容：支持现有数据的向后兼容

## 部署说明

### 1. 运行迁移

```bash
php artisan migrate
```

### 2. 更新现有数据

```bash
php artisan db:seed --class=UpdateExistingContractsWithTypeSeeder
```

### 3. 清理缓存

```bash
php artisan cache:clear
php artisan config:clear
```

### 4. 使用部署脚本（推荐）

```bash
chmod +x deploy_contract_type_updates.sh
./deploy_contract_type_updates.sh
```

## 数据兼容性

### 现有数据处理
- 所有现有合同自动设置为"标准合同"类型
- 现有的服务类型字符串数据自动转换为 JSON 格式
- 保持完全的向后兼容性

### API 兼容性
- 新的 API 版本要求 `contract_type` 字段
- 支持旧格式的 `service_type` 数据自动转换
- 搜索和筛选功能完全兼容新旧数据格式

## 前端集成说明

### 新增字段
```javascript
// 表单数据结构
form: {
  contract_type: '', // 'standard' 或 'non-standard'
  service_type: '', // 字符串（标准）或数组（非标）
  // ... 其他字段
}
```

### 动态验证
```javascript
// 合同类型变更处理
handleContractTypeChange(contractType) {
  if (contractType === 'standard') {
    this.form.service_type = '' // 重置为字符串
  } else if (contractType === 'non-standard') {
    this.form.service_type = [] // 重置为数组
  }
}
```

### 显示处理
```javascript
// 服务类型显示文本
getServiceTypeText() {
  if (Array.isArray(this.form.serviceType)) {
    return this.form.serviceType.join('、')
  }
  return this.form.serviceType
}
```

## 注意事项

1. **数据备份**：部署前请备份 `contracts` 表数据
2. **测试验证**：建议在测试环境先验证所有功能
3. **缓存清理**：部署后必须清理应用缓存
4. **前端更新**：确保前端代码同步更新

## 支持

如有问题，请检查：
1. 迁移是否成功执行
2. 种子数据是否正确运行
3. 缓存是否已清理
4. 前端代码是否同步更新

## 更新历史

- **2025-02-01**: 初始版本，添加合同类型字段和服务类型 JSON 支持
