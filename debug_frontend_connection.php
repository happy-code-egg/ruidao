<?php

// 调试前端连接问题
$baseUrl = 'http://127.0.0.1:8018';

echo "=== 调试前端连接问题 ===\n\n";

function testApi($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Access-Control-Allow-Origin: *',
        'Access-Control-Allow-Methods: GET, POST, OPTIONS',
        'Access-Control-Allow-Headers: Content-Type'
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

// 1. 测试草稿列表API
echo "1. 测试草稿列表API\n";
$result = testApi($baseUrl . '/api/review/draft-list');
echo "HTTP状态码: {$result['http_code']}\n";
if ($result['data']) {
    echo "成功: " . ($result['data']['success'] ? '是' : '否') . "\n";
    echo "数据总数: {$result['data']['data']['total']}\n";
    echo "返回记录数: " . count($result['data']['data']['list']) . "\n";
    
    if (!empty($result['data']['data']['list'])) {
        $firstItem = $result['data']['data']['list'][0];
        echo "第一条记录:\n";
        echo "  - ID: {$firstItem['id']}\n";
        echo "  - 项目编号: {$firstItem['projectNumber']}\n";
        echo "  - 项目名称: {$firstItem['caseName']}\n";
        echo "  - 客户名称: {$firstItem['customerName']}\n";
        echo "  - 处理人: {$firstItem['processor']}\n";
        echo "  - 核稿人: {$firstItem['reviewer']}\n";
    }
} else {
    echo "API响应为空\n";
}
echo "\n";

// 2. 测试待处理列表API
echo "2. 测试待处理列表API\n";
$result = testApi($baseUrl . '/api/review/pending-list');
echo "HTTP状态码: {$result['http_code']}\n";
if ($result['data']) {
    echo "成功: " . ($result['data']['success'] ? '是' : '否') . "\n";
    echo "数据总数: {$result['data']['data']['total']}\n";
    echo "返回记录数: " . count($result['data']['data']['list']) . "\n";
} else {
    echo "API响应为空\n";
}
echo "\n";

// 3. 测试CORS设置
echo "3. 测试CORS设置\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/review/draft-list');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: http://localhost:8080'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "CORS测试HTTP状态码: {$httpCode}\n";
if (strpos($response, 'Access-Control-Allow-Origin') !== false) {
    echo "CORS头存在: 是\n";
} else {
    echo "CORS头存在: 否\n";
}
echo "\n";

// 4. 前端可能遇到的问题诊断
echo "4. 前端可能遇到的问题诊断\n";
echo "可能的问题：\n";
echo "- 前端服务器未启动或端口不正确\n";
echo "- CORS跨域问题\n";
echo "- API路径不正确\n";
echo "- JavaScript错误导致API调用失败\n";
echo "- 数据字段映射问题\n";
echo "\n";

echo "建议检查：\n";
echo "1. 确认前端开发服务器正在运行 (npm run dev)\n";
echo "2. 检查浏览器控制台是否有JavaScript错误\n";
echo "3. 检查浏览器网络面板查看API请求是否发送\n";
echo "4. 确认API基础路径配置正确\n";
echo "\n";

// 5. 创建更多测试数据
echo "5. 创建更多测试数据\n";
try {
    // 使用原始SQL创建更多草稿数据
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=ema_demo', 'postgres', '123456');
    
    // 更新更多记录为草稿状态
    $stmt = $pdo->prepare("UPDATE case_processes SET process_status = 0 WHERE id IN (2, 3)");
    $stmt->execute();
    
    echo "已创建更多草稿状态的测试数据\n";
    
    // 检查更新后的数据
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM case_processes WHERE process_status = 0");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "当前草稿状态数据总数: {$count}\n";
    
} catch (Exception $e) {
    echo "创建测试数据失败: " . $e->getMessage() . "\n";
}

echo "\n=== 调试完成 ===\n";
