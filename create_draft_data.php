<?php

require_once 'vendor/autoload.php';

use App\Models\CaseProcess;

// 初始化Laravel应用
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 获取第一个处理事项并设置为草稿状态
$process = CaseProcess::first();
if ($process) {
    $process->process_status = 0; // 草稿状态
    $process->save();
    echo "已将处理事项 {$process->id} 设置为草稿状态\n";
} else {
    echo "没有找到处理事项\n";
}

// 再检查一次数据
echo "草稿状态数量: " . CaseProcess::where('process_status', 0)->count() . "\n";
echo "待处理状态数量: " . CaseProcess::where('process_status', 1)->count() . "\n";
echo "进行中状态数量: " . CaseProcess::where('process_status', 2)->count() . "\n";
echo "已完成状态数量: " . CaseProcess::where('process_status', 3)->count() . "\n";
