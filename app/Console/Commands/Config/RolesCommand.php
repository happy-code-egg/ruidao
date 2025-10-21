<?php

namespace App\Console\Commands\Config;

use App\Models\Role;

class RolesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:roles';
    protected $description = '导入角色管理数据';

    protected function getExcelFileName(): string
    {
        return 'roles.xlsx';
    }

    protected function getTableName(): string
    {
        return 'roles';
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    protected function processData(array $data): array
    {
        return $this->addTimestamps($data);
    }
}

/*
Excel字段说明 (roles.xlsx):
- name: 角色名称
- display_name: 显示名称
- description: 角色描述
- is_active: 是否启用 (1/0)
- sort: 排序
- level: 角色级别
*/
