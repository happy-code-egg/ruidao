<?php

namespace App\Console\Commands\Config;

use App\Models\TechServiceItem;

class TechServiceItemsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:tech-service-items';
    protected $description = '导入科技服务事项设置数据';

    protected function getExcelFileName(): string
    {
        return 'tech_service_items.xlsx';
    }

    protected function getTableName(): string
    {
        return 'tech_service_items';
    }

    protected function getModelClass(): string
    {
        return TechServiceItem::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (tech_service_items.xlsx):
- name: 服务事项名称
- code: 事项代码
- service_type_id: 服务类型ID
- description: 描述
- requirements: 申请要求
- materials: 所需材料
- process_time: 处理时间
- fee: 服务费用
- is_active: 是否启用 (1/0)
- sort: 排序
*/
