<?php

namespace App\Console\Commands\Config;

use App\Models\NotificationRule;

class NotificationRulesCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:notification-rules';
    protected $description = '导入通知书规则数据';

    protected function getExcelFileName(): string
    {
        return 'notification_rules.xlsx';
    }

    protected function getTableName(): string
    {
        return 'notification_rules';
    }

    protected function getModelClass(): string
    {
        return NotificationRule::class;
    }

    protected function processData(array $data): array
    {
        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (notification_rules.xlsx):
- rule_name: 规则名称
- notification_type: 通知类型
- case_type: 案件类型
- business_type: 业务类型
- country: 国家
- trigger_condition: 触发条件
- notification_content: 通知内容
- recipients: 接收人
- is_active: 是否启用 (1/0)
- priority: 优先级
- delay_days: 延迟天数
*/
