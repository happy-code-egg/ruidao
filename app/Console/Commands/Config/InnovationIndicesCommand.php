<?php

namespace App\Console\Commands\Config;

use App\Models\InnovationIndices;

class InnovationIndicesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:innovation-indices';
    protected $description = '导入创新指数设置数据';

    protected function getExcelFileName(): string
    {
        return 'innovation_indices.xlsx';
    }

    protected function getTableName(): string
    {
        return 'innovation_indices';
    }

    protected function getModelClass(): string
    {
        return InnovationIndices::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (innovation_indices.xlsx):
- name: 指数名称
- code: 指数代码
- category: 指数分类
- base_value: 基准值
- current_value: 当前值
- change_rate: 变化率
- calculation_method: 计算方法
- weight: 权重
- is_active: 是否启用 (1/0)
- effective_date: 生效日期
*/
