<?php

// 测试修正后的核稿管理业务流程
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 核稿管理业务流程测试 ===\n\n";

function testApi($url, $method = 'GET', $data = []) {
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
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'data' => $response ? json_decode($response, true) : null
    ];
}

echo "🎯 正确的核稿管理业务流程：\n";
echo "1️⃣ 待提交（草稿） → 2️⃣ 待开始 → 3️⃣ 审核中 → 4️⃣ 审核完成\n\n";

// 测试各个阶段的API
$stages = [
    '1️⃣ 待提交（草稿）' => '/api/review/draft-list',
    '2️⃣ 待开始' => '/api/review/to-be-start-list', 
    '3️⃣ 审核中' => '/api/review/in-review-list',
    '4️⃣ 审核完成' => '/api/review/completed-list'
];

foreach ($stages as $stageName => $endpoint) {
    echo "📋 测试 {$stageName}\n";
    $result = testApi($baseUrl . $endpoint);
    
    if ($result['http_code'] == 200 && $result['data']['success']) {
        $data = $result['data']['data'];
        echo "✅ 成功 - 数据总数: {$data['total']}\n";
        
        if (!empty($data['list'])) {
            $firstItem = $data['list'][0];
            echo "   📄 示例项目: {$firstItem['caseName']}\n";
            echo "   👤 处理人: {$firstItem['processor']}\n";
            echo "   📝 核稿人: {$firstItem['reviewer']}\n";
        }
    } else {
        echo "❌ 失败\n";
        if ($result['data'] && isset($result['data']['message'])) {
            echo "   错误: {$result['data']['message']}\n";
        }
    }
    echo "\n";
}

// 业务流程说明
echo "📚 业务流程说明：\n";
echo "─────────────────────────────────────────────────\n";
echo "1️⃣ 待提交（草稿）:\n";
echo "   - 文件还在撰写中，未提交核稿\n";
echo "   - 有处理人，无核稿人\n";
echo "   - 状态: 0\n\n";

echo "2️⃣ 待开始:\n";
echo "   - 已提交，等待开始核稿\n";
echo "   - 有处理人，有核稿人\n";
echo "   - 状态: 1\n\n";

echo "3️⃣ 审核中:\n";
echo "   - 正在进行核稿审核\n";
echo "   - 核稿人正在审核中\n";
echo "   - 状态: 2\n\n";

echo "4️⃣ 审核完成:\n";
echo "   - 核稿审核已完成\n";
echo "   - 有完成时间记录\n";
echo "   - 状态: 3\n\n";

echo "=== 测试完成 ===\n";
