<?php

// 测试导出接口
$baseUrl = 'http://127.0.0.1:8018';

// 等待服务器启动
sleep(3);

echo "Testing Export Endpoints...\n\n";

// 测试事项监控导出
echo "1. Testing Item Monitor Export...\n";
$url = $baseUrl . '/api/case-monitor/item-monitor/export';
$postData = json_encode([]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => $postData,
        'timeout' => 30
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    if (strpos($http_response_header[0], '200') !== false) {
        echo "✓ Item Monitor Export endpoint is working\n";
        echo "Response size: " . strlen($result) . " bytes\n";
    } else {
        echo "✗ Item Monitor Export returned: " . $http_response_header[0] . "\n";
        echo "Response: " . substr($result, 0, 200) . "...\n";
    }
} else {
    echo "✗ Failed to connect to Item Monitor Export endpoint\n";
}

echo "\n";

// 测试官费监控导出
echo "2. Testing Fee Monitor Export...\n";
$url = $baseUrl . '/api/case-monitor/fee-monitor/export';
$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    if (strpos($http_response_header[0], '200') !== false) {
        echo "✓ Fee Monitor Export endpoint is working\n";
        echo "Response size: " . strlen($result) . " bytes\n";
    } else {
        echo "✗ Fee Monitor Export returned: " . $http_response_header[0] . "\n";
        echo "Response: " . substr($result, 0, 200) . "...\n";
    }
} else {
    echo "✗ Failed to connect to Fee Monitor Export endpoint\n";
}

echo "\n";

// 测试异常官费导出
echo "3. Testing Abnormal Fee Export...\n";
$url = $baseUrl . '/api/case-monitor/abnormal-fee/export';
$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    if (strpos($http_response_header[0], '200') !== false) {
        echo "✓ Abnormal Fee Export endpoint is working\n";
        echo "Response size: " . strlen($result) . " bytes\n";
    } else {
        echo "✗ Abnormal Fee Export returned: " . $http_response_header[0] . "\n";
        echo "Response: " . substr($result, 0, 200) . "...\n";
    }
} else {
    echo "✗ Failed to connect to Abnormal Fee Export endpoint\n";
}

echo "\n";

// 测试标记处理接口
echo "4. Testing Mark Processed Endpoint...\n";
$url = $baseUrl . '/api/case-monitor/abnormal-fee/mark-processed';
$postData = json_encode(['ids' => [1, 2, 3]]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => $postData,
        'timeout' => 10
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    $data = json_decode($result, true);
    if ($data && $data['success']) {
        echo "✓ Mark Processed endpoint is working\n";
        echo "Response: " . $data['message'] . "\n";
    } else {
        echo "✗ Mark Processed endpoint returned error\n";
        echo "Response: " . $result . "\n";
    }
} else {
    echo "✗ Failed to connect to Mark Processed endpoint\n";
}

echo "\nExport endpoint tests completed.\n";
