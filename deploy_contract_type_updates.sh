#!/bin/bash

echo "==================================="
echo "部署合同类型更新"
echo "==================================="

# 运行迁移
echo "步骤 1: 运行数据库迁移..."
php artisan migrate

if [ $? -eq 0 ]; then
    echo "✓ 迁移成功完成"
else
    echo "✗ 迁移失败"
    exit 1
fi

# 运行种子数据更新
echo "步骤 2: 更新现有合同数据..."
php artisan db:seed --class=UpdateExistingContractsWithTypeSeeder

if [ $? -eq 0 ]; then
    echo "✓ 合同数据更新成功"
else
    echo "✗ 合同数据更新失败"
    exit 1
fi

# 清理缓存
echo "步骤 3: 清理应用缓存..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✓ 缓存清理完成"

echo "==================================="
echo "合同类型更新部署完成！"
echo ""
echo "主要更新内容："
echo "- 添加了 contract_type 字段（标准合同/非标合同）"
echo "- 修改了 service_type 字段支持JSON数组"
echo "- 更新了API验证规则"
echo "- 为现有合同设置了默认类型"
echo "==================================="
