<?php

require_once __DIR__ . '/vendor/autoload.php';

// 初始化Laravel应用
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== 详细调试 ===\n";

// 1. 直接检查数据库
echo "1. 数据库查询测试:\n";
$workflows = \App\Models\Workflow::take(3)->get();
echo "数据库中的流程数量: " . \App\Models\Workflow::count() . "\n";
foreach ($workflows as $workflow) {
    echo "- " . $workflow->name . " (status: " . $workflow->status . ")\n";
}

// 2. 测试控制器方法 - 无参数
echo "\n2. 控制器测试 - 无参数:\n";
$controller = new \App\Http\Controllers\Api\WorkflowConfigController();
$request1 = new \Illuminate\Http\Request([]);
$response1 = $controller->getList($request1);
$data1 = json_decode($response1->getContent(), true);
echo "无参数请求结果: count=" . ($data1['count'] ?? 'N/A') . ", data_length=" . (isset($data1['data']) ? count($data1['data']) : 'N/A') . "\n";

// 3. 测试控制器方法 - 带空参数
echo "\n3. 控制器测试 - 带空参数:\n";
$request2 = new \Illuminate\Http\Request([
    'page' => 1,
    'limit' => 10,
    'name' => '',
    'code' => '',
    'caseType' => '',
    'isValid' => ''
]);
$response2 = $controller->getList($request2);
$data2 = json_decode($response2->getContent(), true);
echo "带空参数请求结果: count=" . ($data2['count'] ?? 'N/A') . ", data_length=" . (isset($data2['data']) ? count($data2['data']) : 'N/A') . "\n";

// 4. 检查查询构建
echo "\n4. 查询构建测试:\n";
$query = \App\Models\Workflow::query();

$name = '';
$code = '';
$caseType = '';
$isValid = '';

echo "参数值检查:\n";
echo "- name: '" . $name . "' (empty: " . (empty($name) ? 'true' : 'false') . ")\n";
echo "- code: '" . $code . "' (empty: " . (empty($code) ? 'true' : 'false') . ")\n";
echo "- caseType: '" . $caseType . "' (empty: " . (empty($caseType) ? 'true' : 'false') . ")\n";
echo "- isValid: '" . $isValid . "' (!== '': " . ($isValid !== '' ? 'true' : 'false') . ")\n";

// 模拟控制器中的查询逻辑
if (!empty($name)) {
    $query->where('name', 'like', "%{$name}%");
    echo "添加了name筛选\n";
}

if (!empty($code)) {
    $query->where('code', 'like', "%{$code}%");
    echo "添加了code筛选\n";
}

if (!empty($caseType)) {
    $query->where('case_type', $caseType);
    echo "添加了caseType筛选\n";
}

if ($isValid !== '') {
    $status = $isValid ? 1 : 0;
    $query->where('status', $status);
    echo "添加了status筛选: " . $status . "\n";
}

$count = $query->count();
echo "查询结果数量: " . $count . "\n";

echo "\n=== 调试完成 ===\n";
