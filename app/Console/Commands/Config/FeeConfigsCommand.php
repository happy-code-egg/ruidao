<?php

namespace App\Console\Commands\Config;

use App\Models\FeeConfig;

class FeeConfigsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:fee-configs';
    protected $description = '导入费用配置设置数据';

    protected function getExcelFileName(): string
    {
        return 'fee_configs.xlsx';
    }

    protected function getTableName(): string
    {
        return 'fee_configs';
    }

    protected function getModelClass(): string
    {
        return FeeConfig::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (fee_configs.xlsx):
- name: 费用名称
- code: 费用代码
- category: 费用类别
- amount: 费用金额
- currency: 货币单位
- country: 适用国家
- case_type: 案件类型
- is_active: 是否启用 (1/0)
- description: 描述
*/
