<?php

namespace App\Console\Commands\Config;

use App\Models\User;

class UsersCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:users';
    protected $description = '导入用户管理数据';

    protected function getExcelFileName(): string
    {
        return 'users.xlsx';
    }

    protected function getTableName(): string
    {
        return 'users';
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function processData(array $data): array
    {
        // 处理密码加密
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // 添加时间戳 - 有几种选择：
        // 1. 使用当前时间（默认）
        // return $this->addTimestamps($data);
        
        // 2. 使用自定义时间
        return $this->addCustomTimestamps($data, '2025-01-01 00:00:00');
        
        // 3. 指定特定时间戳
        // return $this->addTimestamps($data, '2025-01-01 10:30:00');
        
        // 4. 不添加时间戳，让模型自动处理
        // return $data;
    }
}

/*
Excel字段说明 (users.xlsx):
- name: 用户名
- email: 邮箱
- password: 密码
- real_name: 真实姓名
- nickname: 昵称
- phone: 手机号
- department_id: 部门ID
- is_active: 是否激活 (1/0)
- avatar: 头像
- last_login_at: 最后登录时间
- email_verified_at: 邮箱验证时间
*/
