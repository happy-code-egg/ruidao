<?php

// 单独测试异常官费导出
echo "Testing Abnormal Fee Export...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8018/api/case-monitor/abnormal-fee/export');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
if ($error) {
    echo "cURL Error: " . $error . "\n";
}

if ($httpCode == 200) {
    echo "✓ Abnormal Fee Export is working!\n";
    echo "Response size: " . strlen($response) . " bytes\n";
} else {
    echo "✗ Error response:\n";
    echo $response . "\n";
}

echo "\nTest completed.\n";
