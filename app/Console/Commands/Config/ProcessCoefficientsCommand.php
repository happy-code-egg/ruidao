<?php

namespace App\Console\Commands\Config;

use App\Models\ProcessCoefficient;

class ProcessCoefficientsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:process-coefficients';
    protected $description = '导入处理事项系数设置数据';

    protected function getExcelFileName(): string
    {
        return 'process_coefficients.xlsx';
    }

    protected function getTableName(): string
    {
        return 'process_coefficients';
    }

    protected function getModelClass(): string
    {
        return ProcessCoefficient::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (process_coefficients.xlsx):
- process_type: 处理事项类型
- case_type: 案件类型
- business_type: 业务类型
- coefficient: 系数值
- description: 描述
- is_active: 是否启用 (1/0)
- effective_date: 生效日期
- expiry_date: 失效日期
*/
