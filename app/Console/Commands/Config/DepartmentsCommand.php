<?php

namespace App\Console\Commands\Config;

use App\Models\Department;

class DepartmentsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:departments';
    protected $description = '导入部门管理数据';

    protected function getExcelFileName(): string
    {
        return 'departments.xlsx';
    }

    protected function getTableName(): string
    {
        return 'departments';
    }

    protected function getModelClass(): string
    {
        return Department::class;
    }

    protected function processData(array $data): array
    {
        return $this->addTimestamps($data);
    }
}

/*
Excel字段说明 (departments.xlsx):
- name: 部门名称
- code: 部门编码
- parent_id: 上级部门ID
- manager_id: 部门经理ID
- description: 部门描述
- sort: 排序
- is_active: 是否启用 (1/0)
- phone: 部门电话
- email: 部门邮箱
- address: 部门地址
*/
