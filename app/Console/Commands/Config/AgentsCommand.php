<?php

namespace App\Console\Commands\Config;

use App\Models\Agent;

class AgentsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:agents';
    protected $description = '导入代理师设置数据';

    protected function getExcelFileName(): string
    {
        return 'agents.xlsx';
    }

    protected function getTableName(): string
    {
        return 'agents';
    }

    protected function getModelClass(): string
    {
        return Agent::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (agents.xlsx):
- name: 代理师姓名
- code: 代理师编号
- agency_id: 所属代理机构ID
- license_number: 执业证号
- qualification: 执业资格
- specialty: 专业领域
- phone: 电话
- email: 邮箱
- is_active: 是否启用 (1/0)
- description: 描述
*/
