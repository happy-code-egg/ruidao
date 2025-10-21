<?php

namespace App\Console\Commands\Config;

use App\Models\ApplyType;

class ApplyTypesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:apply-types';
    protected $description = '导入申请类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'apply_types.xlsx';
    }

    protected function getTableName(): string
    {
        return 'apply_types';
    }

    protected function getModelClass(): string
    {
        return ApplyType::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (apply_types.xlsx):
- name: 申请类型名称
- code: 申请类型代码
- category: 类别 (专利/商标/版权/科技服务)
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- parent_id: 父级ID
*/
