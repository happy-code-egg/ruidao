<?php

namespace App\Console\Commands\Config;

use App\Models\FileCategories;

class FileCategoriesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:file-categories';
    protected $description = '导入文件大类小类设置数据';

    protected function getExcelFileName(): string
    {
        return 'file_categories.xlsx';
    }

    protected function getTableName(): string
    {
        return 'file_categories';
    }

    protected function getModelClass(): string
    {
        return FileCategories::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (file_categories.xlsx):
- main_category: 文件大类
- sub_category: 文件小类
- is_valid: 是否有效 (1/0)
- sort: 排序
*/
