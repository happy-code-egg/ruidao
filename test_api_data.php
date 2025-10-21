<?php

echo "=== 🐛 直接测试API数据 ===\n\n";

function testAPI($endpoint, $name) {
    $url = "http://127.0.0.1:8018{$endpoint}";
    echo "🔍 测试 {$name}:\n";
    echo "   URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "   ✅ 成功 - 数据总数: {$data['data']['total']}\n";
            
            if (!empty($data['data']['list'])) {
                echo "   📋 数据详情:\n";
                foreach ($data['data']['list'] as $index => $item) {
                    echo "     [{$index}] ID:{$item['id']} | 案件:{$item['caseName']} | 处理人:{$item['processor']} | 核稿人:{$item['reviewer']}\n";
                }
            }
        } else {
            echo "   ❌ API返回失败\n";
        }
    } else {
        echo "   ❌ HTTP错误 ({$httpCode})\n";
    }
    echo "\n";
}

// 测试待开始和审核完成的API
testAPI('/api/review/to-be-start-list', '待开始');
testAPI('/api/review/completed-list', '审核完成');

echo "=== 测试完成 ===\n";
