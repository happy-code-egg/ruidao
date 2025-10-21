<?php

// 测试个人项目API
$baseUrl = 'http://localhost:8000/api';

echo "测试个人项目API...\n";

// 测试待处理项目API
$url = $baseUrl . '/personal-cases/pending?case_type=专利&page=1&limit=10';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应内容:\n";
echo $response . "\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ API调用成功！\n";
        if (isset($data['data']['list'])) {
            echo "返回项目数量: " . count($data['data']['list']) . "\n";
        }
    } else {
        echo "❌ API返回错误: " . ($data['message'] ?? '未知错误') . "\n";
    }
} else {
    echo "❌ HTTP请求失败\n";
}
