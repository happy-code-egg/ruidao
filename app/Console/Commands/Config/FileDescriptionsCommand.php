<?php

namespace App\Console\Commands\Config;

use App\Models\FileDescriptions;

class FileDescriptionsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:file-descriptions';
    protected $description = '导入文件描述设置数据';

    protected function getExcelFileName(): string
    {
        return 'file_descriptions.xlsx';
    }

    protected function getTableName(): string
    {
        return 'file_descriptions';
    }

    protected function getModelClass(): string
    {
        return FileDescriptions::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (file_descriptions.xlsx):
- name: 文件描述名称
- code: 文件描述代码
- category: 文件分类
- template: 描述模板
- is_active: 是否启用 (1/0)
- sort: 排序
*/
