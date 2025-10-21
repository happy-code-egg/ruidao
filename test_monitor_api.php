<?php

// 简单的API测试脚本
$baseUrl = 'http://127.0.0.1:8000';

// 测试事项监控API
echo "Testing Item Monitor API...\n";
$url = $baseUrl . '/api/case-monitor/item-monitor';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json',
        'timeout' => 10
    ]
]);

$result = file_get_contents($url, false, $context);
if ($result !== false) {
    $data = json_decode($result, true);
    echo "Item Monitor API Response:\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "Total items: " . ($data['data']['total'] ?? 0) . "\n";
} else {
    echo "Failed to connect to Item Monitor API\n";
}

echo "\n";

// 测试官费监控API
echo "Testing Fee Monitor API...\n";
$url = $baseUrl . '/api/case-monitor/fee-monitor';
$result = file_get_contents($url, false, $context);
if ($result !== false) {
    $data = json_decode($result, true);
    echo "Fee Monitor API Response:\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "Total items: " . ($data['data']['total'] ?? 0) . "\n";
} else {
    echo "Failed to connect to Fee Monitor API\n";
}

echo "\n";

// 测试异常官费API
echo "Testing Abnormal Fee API...\n";
$url = $baseUrl . '/api/case-monitor/abnormal-fee';
$result = file_get_contents($url, false, $context);
if ($result !== false) {
    $data = json_decode($result, true);
    echo "Abnormal Fee API Response:\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "Total items: " . ($data['data']['total'] ?? 0) . "\n";
} else {
    echo "Failed to connect to Abnormal Fee API\n";
}

echo "\nAPI tests completed.\n";
