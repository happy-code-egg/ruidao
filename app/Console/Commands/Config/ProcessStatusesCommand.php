<?php

namespace App\Console\Commands\Config;

use App\Models\ProcessStatus;

class ProcessStatusesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:process-statuses';
    protected $description = '导入处理事项状态设置数据';

    protected function getExcelFileName(): string
    {
        return 'process_statuses.xlsx';
    }

    protected function getTableName(): string
    {
        return 'process_statuses';
    }

    protected function getModelClass(): string
    {
        return ProcessStatus::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (process_statuses.xlsx):
- name: 状态名称
- code: 状态代码
- category: 状态分类
- color: 状态颜色
- description: 描述
- is_active: 是否启用 (1/0)
- sort: 排序
- is_final: 是否为最终状态 (1/0)
*/
