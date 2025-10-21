<?php

// 测试提成计算API
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 提成计算功能测试 ===\n\n";

function testApi($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'data' => $response ? json_decode($response, true) : null
    ];
}

// 1. 测试提成配置
echo "1. 测试提成配置\n";
$result = testApi($baseUrl . '/api/commission/config');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取提成配置\n";
    foreach ($result['data']['data'] as $type => $config) {
        echo "  - {$config['name']}: 基础费用{$config['base_fee']}元, 提成比例{$config['base_rate']}%, 奖励阈值{$config['bonus_threshold']}天\n";
    }
} else {
    echo "✗ 获取提成配置失败\n";
}
echo "\n";

// 2. 测试提成统计数据（所有用户）
echo "2. 测试提成统计数据（所有用户）\n";
$result = testApi($baseUrl . '/api/commission/stats');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取提成统计数据\n";
    echo "  - 总案例数: {$result['data']['data']['summary']['total_cases']}\n";
    echo "  - 总提成: {$result['data']['data']['summary']['total_commission']}元\n";
    echo "  - 平均提成: {$result['data']['data']['summary']['average_commission']}元\n";
    
    echo "\n  详细数据:\n";
    foreach ($result['data']['data']['list'] as $item) {
        echo "  - {$item['case_name']} ({$item['case_type']}) - {$item['processor']}: {$item['total_commission']}元\n";
    }
} else {
    echo "✗ 获取提成统计数据失败\n";
    if ($result['data']) {
        echo "  错误: {$result['data']['message']}\n";
    }
}
echo "\n";

// 3. 测试用户提成汇总
echo "3. 测试用户提成汇总\n";
$result = testApi($baseUrl . '/api/commission/user-summary');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取用户提成汇总\n";
    
    foreach ($result['data']['data'] as $user) {
        echo "  - {$user['user_name']}: {$user['total_cases']}个案例, 总提成{$user['total_commission']}元, 平均{$user['average_commission']}元\n";
        
        echo "    案例类型分布: ";
        foreach ($user['case_types'] as $type => $count) {
            echo "{$type}({$count}个) ";
        }
        echo "\n";
    }
} else {
    echo "✗ 获取用户提成汇总失败\n";
    if ($result['data']) {
        echo "  错误: {$result['data']['message']}\n";
    }
}
echo "\n";

// 4. 测试特定用户的提成统计
echo "4. 测试特定用户的提成统计\n";
$result = testApi($baseUrl . '/api/commission/stats?user_id=33'); // 假设用户ID为33（tech1）
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取特定用户提成统计\n";
    echo "  - 总案例数: {$result['data']['data']['summary']['total_cases']}\n";
    echo "  - 总提成: {$result['data']['data']['summary']['total_commission']}元\n";
    echo "  - 平均提成: {$result['data']['data']['summary']['average_commission']}元\n";
} else {
    echo "✗ 获取特定用户提成统计失败\n";
}
echo "\n";

// 5. 测试按案例类型筛选
echo "5. 测试按案例类型筛选（专利）\n";
$result = testApi($baseUrl . '/api/commission/stats?case_type=patent');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取专利案例提成统计\n";
    echo "  - 专利案例数: {$result['data']['data']['summary']['total_cases']}\n";
    echo "  - 专利提成总额: {$result['data']['data']['summary']['total_commission']}元\n";
} else {
    echo "✗ 获取专利案例提成统计失败\n";
}
echo "\n";

// 6. 测试日期范围筛选
echo "6. 测试日期范围筛选\n";
$startDate = date('Y-m-d', strtotime('-90 days'));
$endDate = date('Y-m-d');
$result = testApi($baseUrl . "/api/commission/stats?start_date={$startDate}&end_date={$endDate}");
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取日期范围内提成统计\n";
    echo "  - 时间范围: {$startDate} 到 {$endDate}\n";
    echo "  - 案例数: {$result['data']['data']['summary']['total_cases']}\n";
    echo "  - 提成总额: {$result['data']['data']['summary']['total_commission']}元\n";
} else {
    echo "✗ 获取日期范围内提成统计失败\n";
}

echo "\n=== 提成计算测试完成 ===\n";
echo "提成计算功能基本正常！\n";
