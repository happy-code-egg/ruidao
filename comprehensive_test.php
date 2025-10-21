<?php

echo "=== 🔍 综合问题诊断 ===\n\n";

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
                echo "   📋 前3条数据:\n";
                for ($i = 0; $i < min(3, count($data['data']['list'])); $i++) {
                    $item = $data['data']['list'][$i];
                    $hash = substr(md5(json_encode($item)), 0, 8);
                    echo "     [{$i}] ID:{$item['id']} | {$item['caseName']} | 数据指纹:{$hash}\n";
                }
            }
        } else {
            echo "   ❌ API返回失败\n";
        }
    } else {
        echo "   ❌ HTTP错误 ({$httpCode})\n";
    }
    echo "\n";
    return $data ?? null;
}

echo "📋 测试各个核稿管理API:\n\n";

$toBeStartData = testAPI('/api/review/to-be-start-list', '待开始');
$inReviewData = testAPI('/api/review/in-review-list', '审核中');
$completedData = testAPI('/api/review/completed-list', '审核完成');

echo "🔍 数据对比分析:\n";
echo "─────────────────────────────────────────\n";

if ($toBeStartData && $completedData) {
    $toBeStartHash = md5(json_encode($toBeStartData['data']['list']));
    $completedHash = md5(json_encode($completedData['data']['list']));
    
    echo "待开始数据指纹: " . substr($toBeStartHash, 0, 16) . "\n";
    echo "审核完成数据指纹: " . substr($completedHash, 0, 16) . "\n";
    
    if ($toBeStartHash === $completedHash) {
        echo "❌ 问题确认: 待开始和审核完成返回完全相同的数据！\n";
        echo "🐛 这确实是一个严重的API问题\n";
    } else {
        echo "✅ 数据不同: API返回的数据是不同的\n";
        echo "💡 可能是前端缓存或其他显示问题\n";
    }
}

echo "\n🔍 测试Detail API:\n";
testAPI('/api/review/detail/7', '详情页面(ID:7)');

echo "\n💡 诊断结果:\n";
echo "1. 如果API数据不同但前端显示相同 → 前端缓存问题\n";
echo "2. 如果API数据相同 → 后端查询逻辑错误\n";
echo "3. Detail API如果正常 → 跳转功能应该工作\n";

echo "\n=== 诊断完成 ===\n";
