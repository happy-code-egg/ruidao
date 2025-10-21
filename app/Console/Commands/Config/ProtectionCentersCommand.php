<?php

namespace App\Console\Commands\Config;

use App\Models\ProtectionCenters;

class ProtectionCentersCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:protection-centers';
    protected $description = '导入保护中心设置数据';

    protected function getExcelFileName(): string
    {
        return 'protection_centers.xlsx';
    }

    protected function getTableName(): string
    {
        return 'protection_centers';
    }

    protected function getModelClass(): string
    {
        return ProtectionCenters::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (protection_centers.xlsx):
- name: 保护中心名称
- code: 中心代码
- province: 所在省份
- city: 所在城市
- address: 详细地址
- phone: 联系电话
- email: 邮箱
- website: 网站
- contact_person: 联系人
- business_scope: 业务范围
- is_active: 是否启用 (1/0)
*/
