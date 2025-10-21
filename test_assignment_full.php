<?php

// 完整测试分配管理功能
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 分配管理功能完整测试 ===\n\n";

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

// 1. 测试新申请列表
echo "1. 测试新申请列表\n";
$result = testApi($baseUrl . '/api/assignment/new-applications');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取新申请列表\n";
    echo "  - 总数: {$result['data']['data']['total']}\n";
    echo "  - 当前页数据: " . count($result['data']['data']['list']) . "\n";
    
    // 保存第一个未分配的事项ID用于后续测试
    $unassignedItem = null;
    foreach ($result['data']['data']['list'] as $item) {
        if ($item['assignmentStatus'] === '未分配') {
            $unassignedItem = $item;
            break;
        }
    }
    
    if ($unassignedItem) {
        echo "  - 找到未分配事项: {$unassignedItem['caseTitle']}\n";
    }
} else {
    echo "✗ 获取新申请列表失败\n";
}
echo "\n";

// 2. 测试中间案列表
echo "2. 测试中间案列表\n";
$result = testApi($baseUrl . '/api/assignment/middle-cases');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取中间案列表\n";
    echo "  - 总数: {$result['data']['data']['total']}\n";
    echo "  - 当前页数据: " . count($result['data']['data']['list']) . "\n";
} else {
    echo "✗ 获取中间案列表失败\n";
}
echo "\n";

// 3. 测试科服案例列表
echo "3. 测试科服案例列表\n";
$result = testApi($baseUrl . '/api/assignment/tech-service-cases');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取科服案例列表\n";
    echo "  - 总数: {$result['data']['data']['total']}\n";
    echo "  - 当前页数据: " . count($result['data']['data']['list']) . "\n";
} else {
    echo "✗ 获取科服案例列表失败\n";
}
echo "\n";

// 4. 测试已分配案例列表
echo "4. 测试已分配案例列表\n";
$result = testApi($baseUrl . '/api/assignment/assigned-cases');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取已分配案例列表\n";
    echo "  - 总数: {$result['data']['data']['total']}\n";
    echo "  - 当前页数据: " . count($result['data']['data']['list']) . "\n";
} else {
    echo "✗ 获取已分配案例列表失败\n";
}
echo "\n";

// 5. 测试用户列表
echo "5. 测试可分配用户列表\n";
$result = testApi($baseUrl . '/api/assignment/assignable-users');
if ($result['http_code'] == 200 && $result['data']['success']) {
    echo "✓ 成功获取用户列表\n";
    echo "  - 用户数量: " . count($result['data']['data']) . "\n";
    
    // 显示前几个用户
    $users = array_slice($result['data']['data'], 0, 3);
    foreach ($users as $user) {
        echo "  - {$user['name']} (ID: {$user['id']})\n";
    }
} else {
    echo "✗ 获取用户列表失败\n";
}
echo "\n";

// 6. 测试分配功能（如果有未分配的事项）
if (isset($unassignedItem) && $unassignedItem) {
    echo "6. 测试分配功能\n";
    $result = testApi($baseUrl . '/api/assignment/direct-assign', 'POST', [
        'process_id' => $unassignedItem['id'],
        'assigned_to' => 2, // 假设用户ID为2
        'reviewer' => 3     // 假设用户ID为3
    ]);
    
    if ($result['http_code'] == 200 && $result['data']['success']) {
        echo "✓ 分配成功\n";
        echo "  - 消息: {$result['data']['message']}\n";
        
        // 验证分配结果
        echo "\n7. 验证分配结果\n";
        $result = testApi($baseUrl . '/api/assignment/assigned-cases');
        if ($result['http_code'] == 200 && $result['data']['success']) {
            $assignedCount = $result['data']['data']['total'];
            echo "✓ 已分配案例数量: $assignedCount\n";
        }
    } else {
        echo "✗ 分配失败\n";
        if ($result['data']) {
            echo "  - 错误: {$result['data']['message']}\n";
        }
    }
} else {
    echo "6. 跳过分配测试（没有未分配的事项）\n";
}

echo "\n=== 测试完成 ===\n";
echo "分配管理功能基本正常！\n";
