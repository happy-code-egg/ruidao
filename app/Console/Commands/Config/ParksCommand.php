<?php

namespace App\Console\Commands\Config;

use App\Models\ParkConfig;

class ParksCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:parks';
    protected $description = '导入园区名称设置数据';

    protected function getExcelFileName(): string
    {
        return 'parks.xlsx';
    }

    protected function getTableName(): string
    {
        return 'parks_config';
    }

    protected function getModelClass(): string
    {
        return ParkConfig::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (parks.xlsx):
- name: 园区名称
- code: 园区代码
- city: 所在城市
- province: 所在省份
- address: 详细地址
- type: 园区类型
- level: 园区级别
- description: 描述
- is_active: 是否启用 (1/0)
*/
