<?php

// 使用cURL测试导出接口
echo "Testing Export Endpoints with cURL...\n\n";

function testEndpoint($url, $method = 'POST', $data = []) {
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
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

$baseUrl = 'http://127.0.0.1:8018';

// 测试事项监控导出
echo "1. Testing Item Monitor Export...\n";
$result = testEndpoint($baseUrl . '/api/case-monitor/item-monitor/export');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['error']) {
    echo "cURL Error: " . $result['error'] . "\n";
}
if ($result['response']) {
    echo "Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// 测试官费监控导出
echo "2. Testing Fee Monitor Export...\n";
$result = testEndpoint($baseUrl . '/api/case-monitor/fee-monitor/export');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['error']) {
    echo "cURL Error: " . $result['error'] . "\n";
}
if ($result['response']) {
    echo "Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// 测试异常官费导出
echo "3. Testing Abnormal Fee Export...\n";
$result = testEndpoint($baseUrl . '/api/case-monitor/abnormal-fee/export');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['error']) {
    echo "cURL Error: " . $result['error'] . "\n";
}
if ($result['response']) {
    echo "Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// 测试标记处理
echo "4. Testing Mark Processed...\n";
$result = testEndpoint($baseUrl . '/api/case-monitor/abnormal-fee/mark-processed', 'POST', ['ids' => [1, 2, 3]]);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['error']) {
    echo "cURL Error: " . $result['error'] . "\n";
}
if ($result['response']) {
    echo "Response: " . $result['response'] . "\n";
}

echo "\ncURL tests completed.\n";
