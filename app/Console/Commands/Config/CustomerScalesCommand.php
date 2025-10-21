<?php

namespace App\Console\Commands\Config;

use App\Models\CustomerScale;

class CustomerScalesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:customer-scales';
    protected $description = '导入客户规模设置数据';

    protected function getExcelFileName(): string
    {
        return 'customer_scales.xlsx';
    }

    protected function getTableName(): string
    {
        return 'customer_scales';
    }

    protected function getModelClass(): string
    {
        return CustomerScale::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (customer_scales.xlsx):
- name: 规模名称
- code: 规模代码
- min_employees: 最少员工数
- max_employees: 最多员工数
- min_revenue: 最少营收
- max_revenue: 最多营收
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
*/
