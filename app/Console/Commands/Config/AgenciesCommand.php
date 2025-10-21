<?php

namespace App\Console\Commands\Config;

use App\Models\Agency;

class AgenciesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:agencies';
    protected $description = '导入代理机构设置数据';

    protected function getExcelFileName(): string
    {
        return 'agencies.xlsx';
    }

    protected function getTableName(): string
    {
        return 'agencies';
    }

    protected function getModelClass(): string
    {
        return Agency::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (agencies.xlsx):
- name: 代理机构名称
- code: 机构代码
- type: 机构类型
- country: 所属国家
- city: 所属城市
- address: 地址
- phone: 电话
- email: 邮箱
- website: 网站
- contact_person: 联系人
- is_active: 是否启用 (1/0)
- description: 描述
*/
