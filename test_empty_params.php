<?php

echo "=== 测试空参数问题 ===\n";

// 测试各种参数组合
$testUrls = [
    "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10",
    "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10&name=",
    "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10&name=&code=",
    "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10&name=&code=&caseType=",
    "http://127.0.0.1:8018/api/workflow-config/list?page=1&limit=10&name=&code=&caseType=&isValid=",
];

foreach ($testUrls as $i => $url) {
    echo "\n测试 " . ($i + 1) . ": " . $url . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['count'])) {
            echo "结果: HTTP " . $httpCode . ", Count: " . $data['count'] . "\n";
        } else {
            echo "结果: HTTP " . $httpCode . ", 解析失败\n";
        }
    } else {
        echo "结果: HTTP " . $httpCode . ", 无响应\n";
    }
}

echo "\n=== 完成 ===\n";
