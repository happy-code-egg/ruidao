<?php

namespace App\Console\Commands\Config;

use App\Models\CommissionSettings;

class CommissionSettingsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:commission-settings';
    protected $description = '导入提成配置设置数据';

    protected function getExcelFileName(): string
    {
        return 'commission_settings.xlsx';
    }

    protected function getTableName(): string
    {
        return 'commission_settings';
    }

    protected function getModelClass(): string
    {
        return CommissionSettings::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (commission_settings.xlsx):
- name: 配置名称
- commission_type: 提成类型
- role_type: 角色类型
- business_type: 业务类型
- rate: 提成比例
- min_amount: 最小金额
- max_amount: 最大金额
- is_active: 是否启用 (1/0)
- effective_date: 生效日期
- expiry_date: 失效日期
*/
