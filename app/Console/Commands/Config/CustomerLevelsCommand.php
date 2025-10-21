<?php

namespace App\Console\Commands\Config;

use App\Models\CustomerLevel;

class CustomerLevelsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:customer-levels';
    protected $description = '导入客户等级设置数据';

    protected function getExcelFileName(): string
    {
        return 'customer_levels.xlsx';
    }

    protected function getTableName(): string
    {
        return 'customer_levels';
    }

    protected function getModelClass(): string
    {
        return CustomerLevel::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (customer_levels.xlsx):
- name: 等级名称
- code: 等级代码
- description: 描述
- discount_rate: 折扣比例
- min_amount: 最低金额要求
- color: 显示颜色
- sort: 排序
- is_active: 是否启用 (1/0)
*/
