<?php

namespace App\Console\Commands\Config;

use App\Models\BusinessServiceTypes;

class BusinessServiceTypesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:business-service-types';
    protected $description = '导入业务服务类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'business_service_types.xlsx';
    }

    protected function getTableName(): string
    {
        return 'business_service_types';
    }

    protected function getModelClass(): string
    {
        return BusinessServiceTypes::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (business_service_types.xlsx):
- name: 服务类型名称
- code: 服务类型代码
- category: 服务分类
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- parent_id: 父级ID
*/
