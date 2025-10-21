<?php

namespace App\Console\Commands\Config;

use App\Models\Permission;

class PermissionsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:permissions';
    protected $description = '导入权限管理数据';

    protected function getExcelFileName(): string
    {
        return 'permissions.xlsx';
    }

    protected function getTableName(): string
    {
        return 'permissions';
    }

    protected function getModelClass(): string
    {
        return Permission::class;
    }

    protected function processData(array $data): array
    {
        return $this->addTimestamps($data);
    }
}

/*
Excel字段说明 (permissions.xlsx):
- name: 权限名称
- display_name: 显示名称
- description: 权限描述
- guard_name: 守护名称 (默认web)
- module: 模块
- action: 操作
- resource: 资源
- is_active: 是否启用 (1/0)
*/
