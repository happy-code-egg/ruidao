<?php

// 测试修复后的人员选项API

$baseUrl = 'http://127.0.0.1:8018/api'; // 根据实际情况调整

echo "测试人员选项API修复...\n";

$url = $baseUrl . "/search/files/options?type=staff";
echo "请求URL: $url\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'timeout' => 30
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ API调用成功！\n";
        echo "返回的人员数量: " . count($data['data']) . "\n";
        
        if (count($data['data']) > 0) {
            echo "前3个人员示例:\n";
            for ($i = 0; $i < min(3, count($data['data'])); $i++) {
                $staff = $data['data'][$i];
                echo "  - ID: {$staff['value']}, 姓名: {$staff['label']}\n";
            }
        }
    } else {
        echo "❌ API返回错误: " . ($data['message'] ?? '未知错误') . "\n";
    }
} else {
    echo "❌ 请求失败，请检查:\n";
    echo "1. 后端服务是否运行在 127.0.0.1:8018\n";
    echo "2. 数据库连接是否正常\n";
    echo "3. users表是否存在且有数据\n";
}

echo "\n测试完成！\n";
