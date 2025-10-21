<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// 启动Laravel应用
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 获取第一个用户
$user = User::first();

if (!$user) {
    echo "No users found\n";
    exit(1);
}

// 创建token
$token = $user->createToken('test')->plainTextToken;

echo "Token: " . $token . "\n";
echo "User: " . $user->real_name . " (" . $user->username . ")\n";
