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

echo "=== 搜索和分页功能测试 ===\n\n";

// 测试客户规模搜索
echo "1. 测试客户规模搜索功能\n";
echo "------------------------\n";

// 按名称搜索
echo "按名称搜索 '大型'...\n";
$searchResponse = makeRequest('GET', '/api/data-config/customer-scales?scaleName=大型&page=1&limit=5', null, $token);
echo "搜索结果数量: " . count($searchResponse['data']['list']) . "\n";
echo "总记录数: " . $searchResponse['data']['total'] . "\n\n";

// 按状态筛选
echo "筛选有效状态...\n";
$filterResponse = makeRequest('GET', '/api/data-config/customer-scales?isValid=1&page=1&limit=5', null, $token);
echo "筛选结果数量: " . count($filterResponse['data']['list']) . "\n";
echo "总记录数: " . $filterResponse['data']['total'] . "\n\n";

// 测试分页
echo "测试分页 (每页3条)...\n";
$page1Response = makeRequest('GET', '/api/data-config/customer-scales?page=1&limit=3', null, $token);
echo "第1页记录数: " . count($page1Response['data']['list']) . "\n";
echo "总页数: " . $page1Response['data']['pages'] . "\n";

$page2Response = makeRequest('GET', '/api/data-config/customer-scales?page=2&limit=3', null, $token);
echo "第2页记录数: " . count($page2Response['data']['list']) . "\n\n";

// 测试商机状态搜索
echo "2. 测试商机状态搜索功能\n";
echo "------------------------\n";

// 按名称搜索
echo "按名称搜索 '接触'...\n";
$searchResponse = makeRequest('GET', '/api/data-config/business-statuses?statusName=接触&page=1&limit=5', null, $token);
echo "搜索结果数量: " . count($searchResponse['data']['list']) . "\n";
echo "总记录数: " . $searchResponse['data']['total'] . "\n\n";

// 测试业务服务类型搜索
echo "3. 测试业务服务类型搜索功能\n";
echo "----------------------------\n";

// 按名称搜索
echo "按名称搜索 '专利'...\n";
$searchResponse = makeRequest('GET', '/api/data-config/business-service-types?name=专利&page=1&limit=5', null, $token);
echo "搜索结果数量: " . count($searchResponse['data']['list']) . "\n";
echo "总记录数: " . $searchResponse['data']['total'] . "\n\n";

// 按类别筛选
echo "按类别筛选 '专利'...\n";
$filterResponse = makeRequest('GET', '/api/data-config/business-service-types?category=专利&page=1&limit=5', null, $token);
echo "筛选结果数量: " . count($filterResponse['data']['list']) . "\n";
echo "总记录数: " . $filterResponse['data']['total'] . "\n\n";

// 按状态筛选
echo "筛选启用状态...\n";
$statusResponse = makeRequest('GET', '/api/data-config/business-service-types?status=1&page=1&limit=5', null, $token);
echo "筛选结果数量: " . count($statusResponse['data']['list']) . "\n";
echo "总记录数: " . $statusResponse['data']['total'] . "\n\n";

// 组合搜索
echo "组合搜索 (名称='专利' AND 类别='专利' AND 状态=1)...\n";
$comboResponse = makeRequest('GET', '/api/data-config/business-service-types?name=专利&category=专利&status=1&page=1&limit=10', null, $token);
echo "组合搜索结果数量: " . count($comboResponse['data']['list']) . "\n";
echo "总记录数: " . $comboResponse['data']['total'] . "\n\n";

echo "=== 搜索和分页功能测试完成 ===\n";
