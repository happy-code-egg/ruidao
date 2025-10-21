<?php

// 测试详情API
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 详情API测试 ===\n\n";

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

// 先获取一个处理事项的ID
echo "1. 获取处理事项列表以获取ID\n";
$result = testApi($baseUrl . '/api/assignment/new-applications');
if ($result['http_code'] == 200 && $result['data']['success']) {
    $list = $result['data']['data']['list'];
    if (!empty($list)) {
        $processId = $list[0]['id'];
        echo "✓ 找到处理事项ID: {$processId}\n";
        
        // 测试详情API
        echo "\n2. 测试处理事项详情API\n";
        $result = testApi($baseUrl . "/api/assignment/process-detail/{$processId}");
        
        if ($result['http_code'] == 200 && $result['data']['success']) {
            $detail = $result['data']['data'];
            echo "✓ 详情API测试成功\n";
            echo "  - 处理事项名称: {$detail['process_name']}\n";
            echo "  - 案件名称: {$detail['case']['case_name']}\n";
            echo "  - 客户名称: " . ($detail['customer'] ? $detail['customer']['customer_name'] : '无') . "\n";
            echo "  - 分配状态: {$detail['assignment_status']}\n";
            echo "  - 处理人: {$detail['processor']}\n";
            echo "  - 核稿人: {$detail['reviewer_name']}\n";
        } else {
            echo "✗ 详情API测试失败\n";
            echo "  HTTP Code: {$result['http_code']}\n";
            if ($result['data']) {
                echo "  错误信息: {$result['data']['message']}\n";
            }
        }
    } else {
        echo "✗ 没有找到处理事项\n";
    }
} else {
    echo "✗ 获取处理事项列表失败\n";
}

// 测试不存在的ID
echo "\n3. 测试不存在的ID\n";
$result = testApi($baseUrl . "/api/assignment/process-detail/99999");
if ($result['http_code'] == 404) {
    echo "✓ 正确处理不存在的ID（返回404）\n";
} else {
    echo "✗ 不存在ID的处理有问题\n";
    echo "  HTTP Code: {$result['http_code']}\n";
}

echo "\n=== 详情API测试完成 ===\n";
