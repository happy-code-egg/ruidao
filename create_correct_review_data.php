<?php

require_once 'vendor/autoload.php';

// 初始化Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "=== 创建正确的核稿管理测试数据 ===\n\n";

// 清除现有的状态数据，重新设置
DB::beginTransaction();

try {
    // 1. 待提交（草稿）- 状态0：文件还在撰写中，未提交核稿
    DB::table('case_processes')->where('id', 1)->update([
        'process_status' => 0,
        'assigned_to' => 1,  // 有处理人
        'reviewer' => null   // 没有核稿人
    ]);

    DB::table('case_processes')->where('id', 2)->update([
        'process_status' => 0,
        'assigned_to' => 2,
        'reviewer' => null
    ]);

    // 2. 待开始 - 状态1：已提交，等待开始核稿
    DB::table('case_processes')->where('id', 3)->update([
        'process_status' => 1,
        'assigned_to' => 1,
        'reviewer' => 2  // 已指定核稿人，等待开始
    ]);

    DB::table('case_processes')->where('id', 4)->update([
        'process_status' => 1,
        'assigned_to' => 2,
        'reviewer' => 1
    ]);

    // 3. 审核中 - 状态2：正在进行核稿审核
    DB::table('case_processes')->where('id', 5)->update([
        'process_status' => 2,
        'assigned_to' => 1,
        'reviewer' => 2
    ]);

    DB::table('case_processes')->where('id', 6)->update([
        'process_status' => 2,
        'assigned_to' => 2,
        'reviewer' => 1
    ]);

    // 4. 审核完成 - 状态3：核稿审核已完成
    DB::table('case_processes')->where('id', 7)->update([
        'process_status' => 3,
        'assigned_to' => 1,
        'reviewer' => 2,
        'completion_date' => now()
    ]);

    DB::table('case_processes')->where('id', 8)->update([
        'process_status' => 3,
        'assigned_to' => 2,
        'reviewer' => 1,
        'completion_date' => now()
    ]);

    DB::table('case_processes')->where('id', 9)->update([
        'process_status' => 3,
        'assigned_to' => 1,
        'reviewer' => 2,
        'completion_date' => now()
    ]);

    DB::commit();

    echo "✅ 核稿管理测试数据创建成功！\n\n";

    // 统计各状态数据
    echo "📊 各状态数据统计：\n";
    echo "- 待提交（草稿）: " . DB::table('case_processes')->where('process_status', 0)->count() . " 个\n";
    echo "- 待开始: " . DB::table('case_processes')->where('process_status', 1)->count() . " 个\n";
    echo "- 审核中: " . DB::table('case_processes')->where('process_status', 2)->count() . " 个\n";
    echo "- 审核完成: " . DB::table('case_processes')->where('process_status', 3)->count() . " 个\n";

    echo "\n🎯 核稿管理业务流程：\n";
    echo "1. 待提交（草稿）→ 2. 待开始 → 3. 审核中 → 4. 审核完成\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ 创建数据失败: " . $e->getMessage() . "\n";
}
