<?php

namespace App\Console\Commands\Config;

use App\Models\ProcessRule;

class ProcessRulesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:process-rules';
    protected $description = '导入处理事项规则数据';

    protected function getExcelFileName(): string
    {
        return 'process_rules.xlsx';
    }

    protected function getTableName(): string
    {
        return 'process_rules';
    }

    protected function getModelClass(): string
    {
        return ProcessRule::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (process_rules.xlsx):
- rule_name: 规则名称
- case_type: 案件类型
- business_type: 业务类型
- country: 国家
- process_type: 处理事项类型
- rule_condition: 规则条件
- rule_action: 规则动作
- is_active: 是否启用 (1/0)
- priority: 优先级
- description: 描述
*/
