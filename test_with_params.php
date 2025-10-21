<?php

// 测试带参数的请求
$url = "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10";

echo "测试URL: " . $url . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response length: " . strlen($response) . "\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "JSON解析成功:\n";
        echo "- Code: " . ($data['code'] ?? 'N/A') . "\n";
        echo "- Message: " . ($data['msg'] ?? 'N/A') . "\n";
        echo "- Count: " . ($data['count'] ?? 'N/A') . "\n";
        echo "- Data length: " . (isset($data['data']) ? count($data['data']) : 'N/A') . "\n";
    } else {
        echo "JSON解析失败，原始响应:\n";
        echo $response . "\n";
    }
} else {
    echo "没有响应数据\n";
}

echo "\n=== 测试带空参数的请求 ===\n";

$url2 = "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10&name=test";

echo "测试URL: " . $url2 . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response length: " . strlen($response) . "\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "JSON解析成功:\n";
        echo "- Code: " . ($data['code'] ?? 'N/A') . "\n";
        echo "- Count: " . ($data['count'] ?? 'N/A') . "\n";
    } else {
        echo "JSON解析失败\n";
    }
}
