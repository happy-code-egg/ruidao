<?php
/**
 * EMA系统配置数据批量导入脚本
 * 
 * 此脚本用于批量执行所有配置项的Command导入
 */

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

// 配置项Command列表（按依赖顺序排列）
$commands = [
    // 系统基础数据
    'basic' => [
        'config:departments' => '部门管理',
        'config:users' => '用户管理',
        'config:roles' => '角色管理',
        'config:permissions' => '权限管理',
    ],
    
    // 基础配置数据
    'foundation' => [
        'config:apply-types' => '申请类型设置',
        'config:process-statuses' => '处理事项状态设置',
        'config:process-types' => '处理事项类型设置',
        'config:fee-configs' => '费用配置设置',
    ],
    
    // 业务配置数据
    'business' => [
        'config:agencies' => '代理机构设置',
        'config:agents' => '代理师设置',
        'config:workflows' => '流程配置',
        'config:process-information' => '处理事项设置',
        'config:process-coefficients' => '处理事项系数设置',
        'config:case-coefficients' => '项目系数设置',
    ],
    
    // 详细配置数据
    'detailed' => [
        'config:file-categories' => '文件大类小类设置',
        'config:file-descriptions' => '文件描述设置',
        'config:products' => '产品设置',
        'config:customer-levels' => '客户等级设置',
        'config:customer-scales' => '客户规模设置',
        'config:invoice-services' => '开票服务类型设置',
        'config:parks' => '园区名称设置',
        'config:business-service-types' => '业务服务类型设置',
        'config:our-companies' => '我方公司设置',
        'config:commission-types' => '提成类型设置',
        'config:commission-settings' => '提成配置设置',
        'config:tech-service-types' => '科技服务类型设置',
        'config:tech-service-items' => '科技服务事项设置',
        'config:manuscript-scoring-items' => '审核打分项设置',
        'config:protection-centers' => '保护中心设置',
        'config:price-indices' => '价格指数设置',
        'config:innovation-indices' => '创新指数设置',
        'config:patent-annual-fees' => '专利年费配置',
        'config:notification-rules' => '通知书规则',
        'config:process-rules' => '处理事项规则',
    ]
];

echo colorOutput("\n=== EMA系统配置数据批量导入脚本 ===\n", 'cyan');
echo colorOutput("此脚本将按依赖顺序批量导入所有配置数据\n", 'white');

echo colorOutput("\n请选择导入方式：\n", 'white');
echo colorOutput("1. 导入所有配置数据\n", 'green');
echo colorOutput("2. 按分组导入\n", 'green');
echo colorOutput("3. 选择性导入特定配置\n", 'green');
echo colorOutput("4. 退出\n", 'red');

echo colorOutput("\n请输入选项 (1-4): ", 'cyan');
$choice = trim(fgets(STDIN));

switch ($choice) {
    case '1':
        echo colorOutput("\n开始导入所有配置数据...\n", 'green');
        importAllConfigs($commands);
        break;
        
    case '2':
        echo colorOutput("\n请选择要导入的分组：\n", 'white');
        echo colorOutput("1. 系统基础数据 (部门、用户、角色、权限)\n", 'yellow');
        echo colorOutput("2. 基础配置数据 (申请类型、状态、费用等)\n", 'yellow');
        echo colorOutput("3. 业务配置数据 (代理机构、流程、处理事项等)\n", 'yellow');
        echo colorOutput("4. 详细配置数据 (文件分类、产品、客户等)\n", 'yellow');
        echo colorOutput("请输入选项 (1-4): ", 'cyan');
        
        $groupChoice = trim(fgets(STDIN));
        $groupNames = ['basic', 'foundation', 'business', 'detailed'];
        
        if (isset($groupNames[$groupChoice - 1])) {
            $groupName = $groupNames[$groupChoice - 1];
            echo colorOutput("\n开始导入 {$groupName} 分组数据...\n", 'green');
            importGroup($commands[$groupName]);
        } else {
            echo colorOutput("无效选项！\n", 'red');
            exit(1);
        }
        break;
        
    case '3':
        echo colorOutput("\n可用的配置项：\n", 'white');
        $allCommands = [];
        $index = 1;
        foreach ($commands as $group => $groupCommands) {
            foreach ($groupCommands as $cmd => $desc) {
                echo colorOutput("{$index}. {$desc} ({$cmd})\n", 'yellow');
                $allCommands[$index] = ['cmd' => $cmd, 'desc' => $desc];
                $index++;
            }
        }
        
        echo colorOutput("\n请输入要导入的配置项编号（多个用逗号分隔）: ", 'cyan');
        $selection = trim(fgets(STDIN));
        $selectedIndexes = explode(',', $selection);
        
        $selectedCommands = [];
        foreach ($selectedIndexes as $idx) {
            $idx = (int)trim($idx);
            if (isset($allCommands[$idx])) {
                $selectedCommands[$allCommands[$idx]['cmd']] = $allCommands[$idx]['desc'];
            }
        }
        
        if (!empty($selectedCommands)) {
            echo colorOutput("\n开始导入选定的配置数据...\n", 'green');
            importGroup($selectedCommands);
        } else {
            echo colorOutput("没有选择有效的配置项！\n", 'red');
            exit(1);
        }
        break;
        
    case '4':
        echo colorOutput("退出脚本。\n", 'yellow');
        exit(0);
        
    default:
        echo colorOutput("无效选项！请重新运行脚本。\n", 'red');
        exit(1);
}

echo colorOutput("\n=== 导入完成 ===\n", 'cyan');
echo colorOutput("请检查：\n", 'white');
echo colorOutput("1. 登录系统验证配置项是否正确导入\n", 'green');
echo colorOutput("2. 检查数据的完整性和正确性\n", 'green');
echo colorOutput("3. 测试相关功能是否正常工作\n", 'green');

/**
 * 导入所有配置数据
 */
function importAllConfigs($commands) {
    $totalSuccess = 0;
    $totalFailed = 0;
    
    foreach ($commands as $groupName => $groupCommands) {
        echo colorOutput("\n--- 导入 {$groupName} 分组 ---\n", 'blue');
        $result = importGroup($groupCommands);
        $totalSuccess += $result['success'];
        $totalFailed += $result['failed'];
        
        // 分组间稍作停顿
        sleep(1);
    }
    
    echo colorOutput("\n总计：成功 {$totalSuccess} 个，失败 {$totalFailed} 个\n", 'magenta');
}

/**
 * 导入指定分组的配置数据
 */
function importGroup($groupCommands) {
    $success = 0;
    $failed = 0;
    
    foreach ($groupCommands as $command => $description) {
        echo colorOutput("正在导入: {$description}...", 'yellow');
        
        $output = [];
        $returnCode = 0;
        exec("php artisan {$command} 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo colorOutput(" ✅ 成功\n", 'green');
            $success++;
        } else {
            echo colorOutput(" ❌ 失败\n", 'red');
            echo colorOutput("错误信息: " . implode("\n", $output) . "\n", 'red');
            $failed++;
        }
        
        // 命令间稍作停顿
        usleep(500000); // 0.5秒
    }
    
    echo colorOutput("\n本组结果：成功 {$success} 个，失败 {$failed} 个\n", 'blue');
    
    return ['success' => $success, 'failed' => $failed];
}
?>
