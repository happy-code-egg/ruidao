# PowerShell 部署脚本 - 合同类型更新

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "部署合同类型更新" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan

# 运行迁移
Write-Host "步骤 1: 运行数据库迁移..." -ForegroundColor Yellow
php artisan migrate

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ 迁移成功完成" -ForegroundColor Green
} else {
    Write-Host "✗ 迁移失败" -ForegroundColor Red
    exit 1
}

# 运行种子数据更新
Write-Host "步骤 2: 更新现有合同数据..." -ForegroundColor Yellow
php artisan db:seed --class=UpdateExistingContractsWithTypeSeeder

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ 合同数据更新成功" -ForegroundColor Green
} else {
    Write-Host "✗ 合同数据更新失败" -ForegroundColor Red
    exit 1
}

# 清理缓存
Write-Host "步骤 3: 清理应用缓存..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

Write-Host "✓ 缓存清理完成" -ForegroundColor Green

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "合同类型更新部署完成！" -ForegroundColor Green
Write-Host ""
Write-Host "主要更新内容：" -ForegroundColor Yellow
Write-Host "- 添加了 contract_type 字段（标准合同/非标合同）" -ForegroundColor White
Write-Host "- 修改了 service_type 字段支持JSON数组" -ForegroundColor White
Write-Host "- 更新了API验证规则" -ForegroundColor White
Write-Host "- 为现有合同设置了默认类型" -ForegroundColor White
Write-Host "===================================" -ForegroundColor Cyan
