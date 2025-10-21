<?php

namespace App\Console\Commands\Config;

use App\Models\PatentAnnualFee;

class PatentAnnualFeesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:patent-annual-fees';
    protected $description = '导入专利年费配置数据';

    protected function getExcelFileName(): string
    {
        return 'patent_annual_fees.xlsx';
    }

    protected function getTableName(): string
    {
        return 'patent_annual_fees';
    }

    protected function getModelClass(): string
    {
        return PatentAnnualFee::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (patent_annual_fees.xlsx):
- country: 国家
- patent_type: 专利类型
- year: 年份
- fee_amount: 费用金额
- currency: 货币
- discount_rate: 减缴比例
- is_active: 是否启用 (1/0)
- effective_date: 生效日期
*/
