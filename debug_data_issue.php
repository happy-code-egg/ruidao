<?php

// 调试待启动和审核完成数据相同的问题
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// 初始化数据库连接
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'ema_demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== 🐛 调试数据问题 ===\n\n";

echo "📊 检查CaseProcess表中的数据分布:\n";

// 检查各状态的数据分布
$statuses = [
    0 => '待提交（草稿）',
    1 => '待开始', 
    2 => '审核中',
    3 => '审核完成'
];

foreach ($statuses as $status => $name) {
    $count = DB::table('case_processes')->where('process_status', $status)->count();
    echo "状态 {$status} ({$name}): {$count} 条记录\n";
}

echo "\n🔍 详细检查待开始(status=1)的数据:\n";
$toBeStartData = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->leftJoin('customers', 'cases.customer_id', '=', 'customers.id') 
    ->leftJoin('users as assignee', 'case_processes.assigned_to', '=', 'assignee.id')
    ->leftJoin('users as reviewer', 'case_processes.reviewer', '=', 'reviewer.id')
    ->select(
        'case_processes.id',
        'case_processes.process_status',
        'cases.case_name',
        'assignee.name as processor',
        'reviewer.name as reviewer_name',
        'case_processes.assigned_to',
        'case_processes.reviewer as reviewer_id'
    )
    ->where('case_processes.process_status', 1)
    ->get();

foreach ($toBeStartData as $item) {
    echo "ID: {$item->id}, 案件: {$item->case_name}, 状态: {$item->process_status}, 处理人: {$item->processor}, 核稿人: {$item->reviewer_name}\n";
    echo "  assigned_to: {$item->assigned_to}, reviewer: {$item->reviewer_id}\n";
}

echo "\n🔍 详细检查审核完成(status=3)的数据:\n";
$completedData = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->leftJoin('customers', 'cases.customer_id', '=', 'customers.id')
    ->leftJoin('users as assignee', 'case_processes.assigned_to', '=', 'assignee.id')
    ->leftJoin('users as reviewer', 'case_processes.reviewer', '=', 'reviewer.id')
    ->select(
        'case_processes.id',
        'case_processes.process_status', 
        'cases.case_name',
        'assignee.name as processor',
        'reviewer.name as reviewer_name',
        'case_processes.assigned_to',
        'case_processes.reviewer as reviewer_id'
    )
    ->where('case_processes.process_status', 3)
    ->get();

foreach ($completedData as $item) {
    echo "ID: {$item->id}, 案件: {$item->case_name}, 状态: {$item->process_status}, 处理人: {$item->processor}, 核稿人: {$item->reviewer_name}\n";
    echo "  assigned_to: {$item->assigned_to}, reviewer: {$item->reviewer_id}\n";
}

echo "\n🔍 检查API筛选条件:\n";
echo "待开始API条件: process_status = 1 AND assigned_to IS NOT NULL AND reviewer IS NOT NULL\n";
echo "审核完成API条件: process_status = 3 AND assigned_to IS NOT NULL AND reviewer IS NOT NULL\n";

// 模拟API查询条件
echo "\n📋 待开始API实际查询结果:\n";
$toBeStartFiltered = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->leftJoin('customers', 'cases.customer_id', '=', 'customers.id')
    ->leftJoin('users as assignee', 'case_processes.assigned_to', '=', 'assignee.id')
    ->leftJoin('users as reviewer', 'case_processes.reviewer', '=', 'reviewer.id')
    ->select(
        'case_processes.id',
        'cases.case_name',
        'assignee.name as processor',
        'reviewer.name as reviewer_name'
    )
    ->where('case_processes.process_status', 1)
    ->whereNotNull('case_processes.assigned_to')
    ->whereNotNull('case_processes.reviewer')
    ->get();

foreach ($toBeStartFiltered as $item) {
    echo "- {$item->case_name} | 处理人: {$item->processor} | 核稿人: {$item->reviewer_name}\n";
}

echo "\n📋 审核完成API实际查询结果:\n";
$completedFiltered = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->leftJoin('customers', 'cases.customer_id', '=', 'customers.id')
    ->leftJoin('users as assignee', 'case_processes.assigned_to', '=', 'assignee.id')
    ->leftJoin('users as reviewer', 'case_processes.reviewer', '=', 'reviewer.id')
    ->select(
        'case_processes.id',
        'cases.case_name', 
        'assignee.name as processor',
        'reviewer.name as reviewer_name'
    )
    ->where('case_processes.process_status', 3)
    ->whereNotNull('case_processes.assigned_to')
    ->whereNotNull('case_processes.reviewer')
    ->get();

foreach ($completedFiltered as $item) {
    echo "- {$item->case_name} | 处理人: {$item->processor} | 核稿人: {$item->reviewer_name}\n";
}

echo "\n=== 调试完成 ===\n";
