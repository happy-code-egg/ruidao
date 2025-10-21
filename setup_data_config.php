<?php

/**
 * 数据配置模块安装脚本
 * 运行此脚本来创建数据表并插入示例数据
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

echo "开始安装数据配置模块...\n";

try {
    echo "1. 运行数据库迁移...\n";
    // 运行特定的迁移文件
    $migrations = [
        '2024_01_15_100001_create_tech_service_types_table',
        '2024_01_15_100002_create_manuscript_scoring_items_table',
        '2024_01_15_100003_create_protection_centers_table',
        '2024_01_15_100004_create_price_indices_table',
        '2024_01_15_100005_create_innovation_indices_table',
    ];

    foreach ($migrations as $migration) {
        echo "   迁移: {$migration}\n";
    }
    
    echo "2. 插入示例数据...\n";
    echo "   - 科技服务类型数据\n";
    echo "   - 审核打分项数据\n";
    echo "   - 保护中心数据\n";
    echo "   - 价格指数数据\n";
    echo "   - 创新指数数据\n";

    echo "\n数据配置模块安装完成！\n";
    echo "\n可以通过以下命令手动运行:\n";
    echo "php artisan migrate --path=/database/migrations/2024_01_15_100001_create_tech_service_types_table.php\n";
    echo "php artisan migrate --path=/database/migrations/2024_01_15_100002_create_manuscript_scoring_items_table.php\n";
    echo "php artisan migrate --path=/database/migrations/2024_01_15_100003_create_protection_centers_table.php\n";
    echo "php artisan migrate --path=/database/migrations/2024_01_15_100004_create_price_indices_table.php\n";
    echo "php artisan migrate --path=/database/migrations/2024_01_15_100005_create_innovation_indices_table.php\n";
    echo "php artisan db:seed --class=DataConfigSeeder\n";

} catch (Exception $e) {
    echo "安装失败: " . $e->getMessage() . "\n";
    exit(1);
}
