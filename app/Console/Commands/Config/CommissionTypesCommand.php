<?php

namespace App\Console\Commands\Config;

use App\Models\CommissionTypes;

class CommissionTypesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:commission-types';
    protected $description = '导入提成类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'commission_types.xlsx';
    }

    protected function getTableName(): string
    {
        return 'commission_types';
    }

    protected function getModelClass(): string
    {
        return CommissionTypes::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (commission_types.xlsx):
- name: 提成类型名称
- code: 提成类型代码
- rate: 提成比例
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- calculation_method: 计算方式
*/
