<?php

namespace App\Console\Commands\Config;

use App\Models\TechServiceTypes;

class TechServiceTypesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:tech-service-types';
    protected $description = '导入科技服务类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'tech_service_types.xlsx';
    }

    protected function getTableName(): string
    {
        return 'tech_service_types';
    }

    protected function getModelClass(): string
    {
        return TechServiceTypes::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (tech_service_types.xlsx):
- name: 服务类型名称
- code: 服务类型代码
- category: 服务分类
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- parent_id: 父级ID
*/
