<?php

// 测试修复后的核稿管理页面API调用
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 🔧 核稿管理页面修复验证 ===\n\n";

function testEndpoint($url, $name, $expectedCount = null) {
    echo "🔍 测试 {$name}:\n";
    echo "   URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ CURL错误: {$error}\n";
        return false;
    }
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            $total = $data['data']['total'];
            echo "   ✅ 成功 - 数据总数: {$total}\n";
            
            if ($expectedCount !== null && $total == $expectedCount) {
                echo "   ✅ 数据量正确 (期望: {$expectedCount})\n";
            } elseif ($expectedCount !== null) {
                echo "   ⚠️  数据量不匹配 (期望: {$expectedCount}, 实际: {$total})\n";
            }
            
            if ($total > 0) {
                $firstItem = $data['data']['list'][0];
                echo "   📄 示例项目: {$firstItem['caseName']}\n";
                echo "   👤 处理人: {$firstItem['processor']}\n";
                echo "   📝 核稿人: {$firstItem['reviewer']}\n";
            }
            return true;
        } else {
            echo "   ❌ API返回失败\n";
            if ($data && isset($data['message'])) {
                echo "   错误信息: {$data['message']}\n";
            }
            return false;
        }
    } else {
        echo "   ❌ HTTP错误 ({$httpCode})\n";
        return false;
    }
}

echo "📋 正在验证核稿管理各页面的API调用...\n\n";

// 测试各个页面对应的API
$tests = [
    ['待提交（草稿）', '/api/review/draft-list', 2],
    ['待开始', '/api/review/to-be-start-list', 3], 
    ['审核中', '/api/review/in-review-list', 2],
    ['审核完成', '/api/review/completed-list', 3]
];

$passedTests = 0;
$totalTests = count($tests);

foreach ($tests as $test) {
    [$name, $endpoint, $expectedCount] = $test;
    $success = testEndpoint($baseUrl . $endpoint, $name, $expectedCount);
    if ($success) {
        $passedTests++;
    }
    echo "\n";
}

// 总结
echo "📊 测试结果总结:\n";
echo "─────────────────────────────────────────\n";
echo "✅ 通过测试: {$passedTests}/{$totalTests}\n";

if ($passedTests == $totalTests) {
    echo "🎉 所有API接口正常工作！\n";
    echo "✅ 核稿管理页面修复成功\n";
    echo "✅ 前端页面现在应该能正常显示数据\n";
} else {
    echo "⚠️  部分API接口有问题，需要进一步检查\n";
}

echo "\n🎯 前端页面路径:\n";
echo "- 待开始: /case/review-management/to-be-start\n";
echo "- 审核中: /case/review-management/in-review\n";  
echo "- 审核完成: /case/review-management/completed\n";

echo "\n💡 提示:\n";
echo "1. 确保前端开发服务器正在运行 (npm run dev)\n";
echo "2. 访问上述页面查看数据显示效果\n";
echo "3. 检查浏览器开发者工具的Network面板确认API请求\n";

echo "\n=== 验证完成 ===\n";
