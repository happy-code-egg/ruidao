<?php

namespace App\Console\Commands\Config;

use App\Models\Product;

class ProductsCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:products';
    protected $description = '导入产品设置数据';

    protected function getExcelFileName(): string
    {
        return 'products.xlsx';
    }

    protected function getTableName(): string
    {
        return 'products';
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    protected function processData(array $data): array
    {
        return $this->addTimestamps($data);
    }
}

/*
Excel字段说明 (products.xlsx):
- sort: 排序
- product_code: 产品编码
- project_type: 项目类型
- apply_type: 申请类型
- specification: 规格说明
- product_name: 产品名称
- official_fee: 官费
- standard_price: 标准价格
- min_price: 最低价格
- is_valid: 是否有效 (1/0)
- update_user: 更新人ID
*/
