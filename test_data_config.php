<?php

/**
 * 数据配置功能测试脚本
 * 用于验证四个页面的数据库表和基础数据是否正确创建
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 配置数据库连接
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'ema_demo'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== 数据配置功能测试 ===\n\n";

// 测试项目系数表
echo "1. 测试项目系数表 (case_coefficients):\n";
try {
    $count = Capsule::table('case_coefficients')->count();
    echo "   - 表存在，记录数: {$count}\n";
    
    if ($count > 0) {
        $sample = Capsule::table('case_coefficients')->first();
        echo "   - 示例数据: {$sample->name}\n";
    }
} catch (Exception $e) {
    echo "   - 错误: " . $e->getMessage() . "\n";
}

// 测试处理事项系数表
echo "\n2. 测试处理事项系数表 (process_coefficients):\n";
try {
    $count = Capsule::table('process_coefficients')->count();
    echo "   - 表存在，记录数: {$count}\n";
    
    if ($count > 0) {
        $sample = Capsule::table('process_coefficients')->first();
        echo "   - 示例数据: {$sample->name}\n";
    }
} catch (Exception $e) {
    echo "   - 错误: " . $e->getMessage() . "\n";
}

// 测试处理事项信息表
echo "\n3. 测试处理事项信息表 (process_informations):\n";
try {
    $count = Capsule::table('process_informations')->count();
    echo "   - 表存在，记录数: {$count}\n";
    
    if ($count > 0) {
        $sample = Capsule::table('process_informations')->first();
        echo "   - 示例数据: {$sample->process_name}\n";
    }
} catch (Exception $e) {
    echo "   - 错误: " . $e->getMessage() . "\n";
}

// 测试专利年费配置表
echo "\n4. 测试专利年费配置表 (patent_annual_fees):\n";
try {
    $count = Capsule::table('patent_annual_fees')->count();
    echo "   - 表存在，记录数: {$count}\n";
    
    if ($count > 0) {
        $sample = Capsule::table('patent_annual_fees')->first();
        echo "   - 示例数据: {$sample->case_type} - {$sample->country}\n";
    }
} catch (Exception $e) {
    echo "   - 错误: " . $e->getMessage() . "\n";
}

// 测试专利年费详情表
echo "\n5. 测试专利年费详情表 (patent_annual_fee_details):\n";
try {
    $count = Capsule::table('patent_annual_fee_details')->count();
    echo "   - 表存在，记录数: {$count}\n";
    
    if ($count > 0) {
        $sample = Capsule::table('patent_annual_fee_details')->first();
        echo "   - 示例数据: {$sample->stage_code} - 基础费用: {$sample->base_fee}\n";
    }
} catch (Exception $e) {
    echo "   - 错误: " . $e->getMessage() . "\n";
}

echo "\n=== 测试完成 ===\n";

/**
 * 辅助函数：获取环境变量
 */
function env($key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
