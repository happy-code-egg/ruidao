<?php

namespace App\Console\Commands\Config;

use App\Models\ProcessTypes;

class ProcessTypesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:process-types';
    protected $description = '导入处理事项类型设置数据';

    protected function getExcelFileName(): string
    {
        return 'process_types.xlsx';
    }

    protected function getTableName(): string
    {
        return 'process_types';
    }

    protected function getModelClass(): string
    {
        return ProcessTypes::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (process_types.xlsx):
- name: 处理事项类型名称
- code: 类型代码
- category: 类型分类
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- color: 显示颜色
*/
