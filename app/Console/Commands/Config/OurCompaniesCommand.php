<?php

namespace App\Console\Commands\Config;

use App\Models\OurCompanies;

class OurCompaniesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:our-companies';
    protected $description = '导入我方公司设置数据';

    protected function getExcelFileName(): string
    {
        return 'our_companies.xlsx';
    }

    protected function getTableName(): string
    {
        return 'our_companies';
    }

    protected function getModelClass(): string
    {
        return OurCompanies::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (our_companies.xlsx):
- name: 公司名称
- code: 公司代码
- legal_name: 法定名称
- tax_number: 税号
- address: 地址
- phone: 电话
- email: 邮箱
- website: 网站
- legal_representative: 法定代表人
- is_active: 是否启用 (1/0)
- description: 描述
*/
