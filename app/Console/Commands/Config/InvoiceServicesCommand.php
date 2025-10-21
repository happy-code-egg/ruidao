<?php

namespace App\Console\Commands\Config;

use App\Models\InvoiceService;

class InvoiceServicesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:invoice-services';
    protected $description = '导入开票服务类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'invoice_services.xlsx';
    }

    protected function getTableName(): string
    {
        return 'invoice_services';
    }

    protected function getModelClass(): string
    {
        return InvoiceService::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (invoice_services.xlsx):
- name: 服务类型名称
- code: 服务类型代码
- tax_rate: 税率
- category: 服务分类
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
*/
