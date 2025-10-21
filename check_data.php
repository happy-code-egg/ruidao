<?php

// 使用Laravel的方式检查数据
require_once __DIR__ . '/bootstrap/app.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== 🐛 检查数据问题 ===\n\n";

// 检查各状态的数据分布
$statuses = [
    0 => '草稿',
    1 => '待开始', 
    2 => '审核中',
    3 => '审核完成'
];

echo "📊 数据分布:\n";
foreach ($statuses as $status => $name) {
    $count = DB::table('case_processes')->where('process_status', $status)->count();
    echo "状态 {$status} ({$name}): {$count} 条记录\n";
}

echo "\n🔍 待开始(status=1)的具体数据:\n";
$toStart = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->select('cases.case_name', 'case_processes.process_status', 'case_processes.id')
    ->where('process_status', 1)
    ->get();

foreach($toStart as $item) {
    echo "ID: {$item->id}, 案件: {$item->case_name}, 状态: {$item->process_status}\n";
}

echo "\n🔍 审核完成(status=3)的具体数据:\n";
$completed = DB::table('case_processes')
    ->leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
    ->select('cases.case_name', 'case_processes.process_status', 'case_processes.id')
    ->where('process_status', 3)
    ->get();

foreach($completed as $item) {
    echo "ID: {$item->id}, 案件: {$item->case_name}, 状态: {$item->process_status}\n";
}

echo "\n=== 检查完成 ===\n";
