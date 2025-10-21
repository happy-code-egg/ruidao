<?php

require_once 'vendor/autoload.php';

// 初始化Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\CaseProcess;
use Illuminate\Support\Facades\DB;

echo "=== 调试待开始API问题 ===\n\n";

// 1. 检查原始数据
echo "1. 检查数据库原始数据:\n";
$rawCount = DB::table('case_processes')
    ->where('process_status', 1)
    ->whereNotNull('assigned_to')
    ->whereNotNull('reviewer')
    ->count();
echo "符合条件的原始记录数: {$rawCount}\n\n";

// 2. 使用模型查询
echo "2. 使用CaseProcess模型查询:\n";
$modelCount = CaseProcess::where('process_status', 1)
    ->whereNotNull('assigned_to')
    ->whereNotNull('reviewer')
    ->count();
echo "模型查询记录数: {$modelCount}\n\n";

// 3. 带关联查询
echo "3. 带关联查询:\n";
$withRelationCount = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
    ->where('process_status', 1)
    ->whereNotNull('assigned_to')
    ->whereNotNull('reviewer')
    ->count();
echo "带关联查询记录数: {$withRelationCount}\n\n";

// 4. 获取具体记录
echo "4. 获取具体记录:\n";
$records = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
    ->where('process_status', 1)
    ->whereNotNull('assigned_to')
    ->whereNotNull('reviewer')
    ->get();

foreach ($records as $record) {
    echo "- ID: {$record->id}\n";
    echo "  状态: {$record->process_status}\n";
    echo "  处理人ID: {$record->assigned_to}\n";
    echo "  核稿人ID: {$record->reviewer}\n";
    echo "  案件: " . ($record->case ? $record->case->case_name : 'N/A') . "\n";
    echo "  处理人: " . ($record->assignedUser ? $record->assignedUser->real_name : 'N/A') . "\n";
    echo "  核稿人: " . ($record->reviewerUser ? $record->reviewerUser->real_name : 'N/A') . "\n\n";
}

// 5. 测试分页
echo "5. 测试分页查询:\n";
$paginated = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
    ->where('process_status', 1)
    ->whereNotNull('assigned_to')
    ->whereNotNull('reviewer')
    ->paginate(10);

echo "分页总数: {$paginated->total()}\n";
echo "当前页记录数: {$paginated->count()}\n";

if ($paginated->count() > 0) {
    echo "第一条记录: {$paginated->first()->case->case_name}\n";
} else {
    echo "分页查询无结果\n";
}

echo "\n=== 调试完成 ===\n";
