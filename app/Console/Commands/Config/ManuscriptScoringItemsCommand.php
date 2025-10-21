<?php

namespace App\Console\Commands\Config;

use App\Models\ManuscriptScoringItems;

class ManuscriptScoringItemsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:manuscript-scoring-items';
    protected $description = '导入审核打分项设置数据';

    protected function getExcelFileName(): string
    {
        return 'manuscript_scoring_items.xlsx';
    }

    protected function getTableName(): string
    {
        return 'manuscript_scoring_items';
    }

    protected function getModelClass(): string
    {
        return ManuscriptScoringItems::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (manuscript_scoring_items.xlsx):
- name: 打分项名称
- code: 打分项代码
- category: 打分分类
- max_score: 最高分数
- weight: 权重
- description: 描述
- criteria: 评分标准
- is_active: 是否启用 (1/0)
- sort: 排序
*/
