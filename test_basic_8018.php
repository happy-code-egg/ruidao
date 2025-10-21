<?php

// 测试基本连接到端口8018
echo "Testing basic Laravel connection on port 8018...\n";

$baseUrl = 'http://127.0.0.1:8018';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$result = @file_get_contents($baseUrl, false, $context);
if ($result !== false) {
    echo "✓ Connection successful to $baseUrl\n";
    echo "Response length: " . strlen($result) . " bytes\n";
    
    // 测试一个简单的API路由
    echo "\nTesting API route...\n";
    $apiUrl = $baseUrl . '/api/case-monitor/item-monitor';
    $apiContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/json',
            'timeout' => 10
        ]
    ]);
    
    $apiResult = @file_get_contents($apiUrl, false, $apiContext);
    if ($apiResult !== false) {
        echo "✓ API endpoint accessible\n";
        $data = json_decode($apiResult, true);
        if ($data && isset($data['success'])) {
            echo "✓ API returned valid JSON response\n";
            echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        } else {
            echo "✗ API returned invalid response: " . substr($apiResult, 0, 100) . "\n";
        }
    } else {
        echo "✗ Failed to access API endpoint\n";
    }
} else {
    echo "✗ Failed to connect to $baseUrl\n";
}

echo "\nConnection test completed.\n";
