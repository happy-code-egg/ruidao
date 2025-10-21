<?php

// 测试具体的API端点
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 测试具体API端点 ===\n\n";

function testEndpoint($url, $name) {
    echo "🔍 测试 {$name}:\n";
    echo "   URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP状态码: {$httpCode}\n";
    
    if ($error) {
        echo "   CURL错误: {$error}\n";
    }
    
    if ($httpCode == 200) {
        // 提取JSON部分
        $headerSize = strpos($response, "\r\n\r\n");
        $jsonResponse = substr($response, $headerSize + 4);
        
        $data = json_decode($jsonResponse, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "   ✅ 成功 - 数据总数: {$data['data']['total']}\n";
        } else {
            echo "   ❌ API返回失败\n";
            if ($data && isset($data['message'])) {
                echo "   错误信息: {$data['message']}\n";
            }
        }
    } else {
        echo "   ❌ HTTP错误\n";
        // 显示部分响应内容用于调试
        $preview = substr($response, 0, 500);
        if (strpos($preview, 'Not Found') !== false) {
            echo "   错误类型: 404 Not Found - 路由不存在\n";
        } elseif (strpos($preview, 'Method Not Allowed') !== false) {
            echo "   错误类型: 405 Method Not Allowed\n";
        } elseif (strpos($preview, 'Internal Server Error') !== false) {
            echo "   错误类型: 500 Internal Server Error\n";
        }
    }
    echo "\n";
}

// 测试所有端点
testEndpoint($baseUrl . '/api/review/draft-list', '待提交（草稿）');
testEndpoint($baseUrl . '/api/review/pending-list', '待处理');
testEndpoint($baseUrl . '/api/review/to-be-start-list', '待开始');
testEndpoint($baseUrl . '/api/review/in-review-list', '审核中');
testEndpoint($baseUrl . '/api/review/completed-list', '审核完成');

echo "=== 测试完成 ===\n";
