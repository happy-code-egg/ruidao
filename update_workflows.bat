@echo off
echo 开始更新工作流数据...
echo.

echo 第1步：更新前3个工作流（合同、立案流程）
php artisan workflows:update
if %errorlevel% neq 0 (
    echo 第1步失败，停止执行
    pause
    exit /b 1
)
echo.

echo 第2步：插入工作流4-7（配案、核稿、递交、案件更新）
php artisan workflows:update-remaining
if %errorlevel% neq 0 (
    echo 第2步失败，停止执行
    pause
    exit /b 1
)
echo.

echo 第3步：插入工作流8-11（请款、收款、开票、支出）
php artisan workflows:update-final
if %errorlevel% neq 0 (
    echo 第3步失败，停止执行
    pause
    exit /b 1
)
echo.

echo 第4步：插入工作流12-14（缴费、运营提成、商务提成）
php artisan workflows:update-commission
if %errorlevel% neq 0 (
    echo 第4步失败，停止执行
    pause
    exit /b 1
)
echo.

echo 所有工作流数据更新完成！
echo 共创建了14个工作流，每个流程包含8个节点
echo.
pause
