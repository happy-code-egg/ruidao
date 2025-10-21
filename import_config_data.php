<?php
/**
 * EMA系统配置数据导入脚本
 * 
 * 此脚本用于快速导入新增的配置项数据
 * 包括：处理事项设置、文件分类设置、产品设置
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// 设置控制台颜色
function colorOutput($text, $color = 'white') {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m", 
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m"
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

echo colorOutput("\n=== EMA系统配置数据导入脚本 ===\n", 'cyan');
echo colorOutput("此脚本将导入以下配置数据：\n", 'white');
echo colorOutput("1. 处理事项设置 (ProcessInformationSeeder)\n", 'yellow');
echo colorOutput("2. 文件分类设置 (FileCategoriesSeeder)\n", 'yellow');
echo colorOutput("3. 产品设置 (ProductsSeeder)\n", 'yellow');

echo colorOutput("\n请选择导入方式：\n", 'white');
echo colorOutput("1. 导入所有新增配置数据\n", 'green');
echo colorOutput("2. 选择性导入特定配置\n", 'green');
echo colorOutput("3. 导入所有系统数据（包括现有的）\n", 'green');
echo colorOutput("4. 退出\n", 'red');

echo colorOutput("\n请输入选项 (1-4): ", 'cyan');
$choice = trim(fgets(STDIN));

switch ($choice) {
    case '1':
        echo colorOutput("\n开始导入所有新增配置数据...\n", 'green');
        
        echo colorOutput("导入处理事项设置...\n", 'yellow');
        system('php artisan db:seed --class=ProcessInformationSeeder');
        
        echo colorOutput("导入文件分类设置...\n", 'yellow');
        system('php artisan db:seed --class=FileCategoriesSeeder');
        
        echo colorOutput("导入产品设置...\n", 'yellow');
        system('php artisan db:seed --class=ProductsSeeder');
        
        echo colorOutput("\n✅ 所有新增配置数据导入完成！\n", 'green');
        break;
        
    case '2':
        echo colorOutput("\n请选择要导入的配置：\n", 'white');
        echo colorOutput("1. 处理事项设置\n", 'yellow');
        echo colorOutput("2. 文件分类设置\n", 'yellow');
        echo colorOutput("3. 产品设置\n", 'yellow');
        echo colorOutput("请输入选项 (1-3): ", 'cyan');
        
        $subChoice = trim(fgets(STDIN));
        
        switch ($subChoice) {
            case '1':
                echo colorOutput("导入处理事项设置...\n", 'yellow');
                system('php artisan db:seed --class=ProcessInformationSeeder');
                break;
            case '2':
                echo colorOutput("导入文件分类设置...\n", 'yellow');
                system('php artisan db:seed --class=FileCategoriesSeeder');
                break;
            case '3':
                echo colorOutput("导入产品设置...\n", 'yellow');
                system('php artisan db:seed --class=ProductsSeeder');
                break;
            default:
                echo colorOutput("无效选项！\n", 'red');
                exit(1);
        }
        
        echo colorOutput("\n✅ 选定配置数据导入完成！\n", 'green');
        break;
        
    case '3':
        echo colorOutput("\n⚠️  警告：这将导入所有系统数据，可能需要较长时间...\n", 'yellow');
        echo colorOutput("确认继续？(y/N): ", 'cyan');
        $confirm = trim(fgets(STDIN));
        
        if (strtolower($confirm) === 'y' || strtolower($confirm) === 'yes') {
            echo colorOutput("开始导入所有系统数据...\n", 'green');
            system('php artisan db:seed');
            echo colorOutput("\n✅ 所有系统数据导入完成！\n", 'green');
        } else {
            echo colorOutput("操作已取消。\n", 'yellow');
        }
        break;
        
    case '4':
        echo colorOutput("退出脚本。\n", 'yellow');
        exit(0);
        
    default:
        echo colorOutput("无效选项！请重新运行脚本。\n", 'red');
        exit(1);
}

echo colorOutput("\n=== 数据导入完成 ===\n", 'cyan');
echo colorOutput("您现在可以：\n", 'white');
echo colorOutput("1. 登录系统查看配置项是否正确导入\n", 'green');
echo colorOutput("2. 进入"配置"菜单验证数据\n", 'green');
echo colorOutput("3. 根据需要修改配置数据\n", 'green');

echo colorOutput("\n如有问题，请查看 DATA_IMPORT_GUIDE.md 获取详细说明。\n", 'blue');
echo colorOutput("或查看 CONFIG_ITEMS_MAPPING.md 了解配置项对应关系。\n", 'blue');
?>
