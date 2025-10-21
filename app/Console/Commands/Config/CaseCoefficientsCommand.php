<?php

namespace App\Console\Commands\Config;

use App\Models\CaseCoefficient;

class CaseCoefficientsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:case-coefficients';
    protected $description = '导入项目系数设置数据';

    protected function getExcelFileName(): string
    {
        return 'case_coefficients.xlsx';
    }

    protected function getTableName(): string
    {
        return 'case_coefficients';
    }

    protected function getModelClass(): string
    {
        return CaseCoefficient::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (case_coefficients.xlsx):
- case_type: 案件类型
- business_type: 业务类型
- project_type: 项目类型
- coefficient: 系数值
- description: 描述
- is_active: 是否启用 (1/0)
- effective_date: 生效日期
- expiry_date: 失效日期
*/
