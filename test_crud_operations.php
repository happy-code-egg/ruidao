<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// 创建Laravel应用实例
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$token = '115|yAIvbAj7TYQ98PRR4CzhilTEOhnfau4IRnnUfEcr';

function makeRequest($method, $url, $data = null, $token = null) {
    global $kernel;
    
    $content = $data ? json_encode($data) : null;
    $request = Request::create($url, $method, [], [], [], [], $content);
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');
    
    if ($token) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }
    
    $response = $kernel->handle($request);
    return json_decode($response->getContent(), true);
}

echo "=== 完整CRUD操作测试 ===\n\n";

// 测试客户规模CRUD
echo "1. 测试客户规模CRUD操作\n";
echo "------------------------\n";

// 创建
echo "创建新的客户规模...\n";
$createResponse = makeRequest('POST', '/api/data-config/customer-scales', [
    'scaleName' => 'CRUD测试企业',
    'isValid' => true,
    'sort' => 100
], $token);
echo "创建结果: " . ($createResponse['success'] ? '成功' : '失败') . "\n";
$createdId = $createResponse['data']['id'] ?? null;
echo "创建的ID: $createdId\n\n";

// 读取详情
if ($createdId) {
    echo "读取详情...\n";
    $detailResponse = makeRequest('GET', "/api/data-config/customer-scales/$createdId", null, $token);
    echo "读取结果: " . ($detailResponse['success'] ? '成功' : '失败') . "\n";
    echo "名称: " . ($detailResponse['data']['scaleName'] ?? 'N/A') . "\n\n";
    
    // 更新
    echo "更新记录...\n";
    $updateResponse = makeRequest('PUT', "/api/data-config/customer-scales/$createdId", [
        'scaleName' => 'CRUD测试企业(已更新)',
        'isValid' => false,
        'sort' => 101
    ], $token);
    echo "更新结果: " . ($updateResponse['success'] ? '成功' : '失败') . "\n";
    echo "更新后名称: " . ($updateResponse['data']['scaleName'] ?? 'N/A') . "\n\n";
    
    // 删除
    echo "删除记录...\n";
    $deleteResponse = makeRequest('DELETE', "/api/data-config/customer-scales/$createdId", null, $token);
    echo "删除结果: " . ($deleteResponse['success'] ? '成功' : '失败') . "\n\n";
}

// 测试商机状态CRUD
echo "2. 测试商机状态CRUD操作\n";
echo "------------------------\n";

// 创建
echo "创建新的商机状态...\n";
$createResponse = makeRequest('POST', '/api/data-config/business-statuses', [
    'statusName' => 'CRUD测试状态',
    'isValid' => true,
    'sort' => 100
], $token);
echo "创建结果: " . ($createResponse['success'] ? '成功' : '失败') . "\n";
$createdId = $createResponse['data']['id'] ?? null;
echo "创建的ID: $createdId\n\n";

// 读取详情
if ($createdId) {
    echo "读取详情...\n";
    $detailResponse = makeRequest('GET', "/api/data-config/business-statuses/$createdId", null, $token);
    echo "读取结果: " . ($detailResponse['success'] ? '成功' : '失败') . "\n";
    echo "名称: " . ($detailResponse['data']['statusName'] ?? 'N/A') . "\n\n";
    
    // 更新
    echo "更新记录...\n";
    $updateResponse = makeRequest('PUT', "/api/data-config/business-statuses/$createdId", [
        'statusName' => 'CRUD测试状态(已更新)',
        'isValid' => false,
        'sort' => 101
    ], $token);
    echo "更新结果: " . ($updateResponse['success'] ? '成功' : '失败') . "\n";
    echo "更新后名称: " . ($updateResponse['data']['statusName'] ?? 'N/A') . "\n\n";
    
    // 删除
    echo "删除记录...\n";
    $deleteResponse = makeRequest('DELETE', "/api/data-config/business-statuses/$createdId", null, $token);
    echo "删除结果: " . ($deleteResponse['success'] ? '成功' : '失败') . "\n\n";
}

// 测试业务服务类型CRUD
echo "3. 测试业务服务类型CRUD操作\n";
echo "----------------------------\n";

// 创建
echo "创建新的业务服务类型...\n";
$createResponse = makeRequest('POST', '/api/data-config/business-service-types', [
    'name' => 'CRUD测试服务',
    'code' => 'CRUD_TEST_SERVICE',
    'category' => 'CRUD测试',
    'description' => '这是CRUD测试服务',
    'status' => 1,
    'sort_order' => 100
], $token);
echo "创建结果: " . ($createResponse['success'] ? '成功' : '失败') . "\n";
$createdId = $createResponse['data']['id'] ?? null;
echo "创建的ID: $createdId\n\n";

// 读取详情
if ($createdId) {
    echo "读取详情...\n";
    $detailResponse = makeRequest('GET', "/api/data-config/business-service-types/$createdId", null, $token);
    echo "读取结果: " . ($detailResponse['success'] ? '成功' : '失败') . "\n";
    echo "名称: " . ($detailResponse['data']['name'] ?? 'N/A') . "\n\n";
    
    // 更新
    echo "更新记录...\n";
    $updateResponse = makeRequest('PUT', "/api/data-config/business-service-types/$createdId", [
        'name' => 'CRUD测试服务(已更新)',
        'code' => 'CRUD_TEST_SERVICE_UPDATED',
        'category' => 'CRUD测试更新',
        'description' => '这是更新后的CRUD测试服务',
        'status' => 0,
        'sort_order' => 101
    ], $token);
    echo "更新结果: " . ($updateResponse['success'] ? '成功' : '失败') . "\n";
    echo "更新后名称: " . ($updateResponse['data']['name'] ?? 'N/A') . "\n\n";
    
    // 删除
    echo "删除记录...\n";
    $deleteResponse = makeRequest('DELETE', "/api/data-config/business-service-types/$createdId", null, $token);
    echo "删除结果: " . ($deleteResponse['success'] ? '成功' : '失败') . "\n\n";
}

echo "=== CRUD操作测试完成 ===\n";
