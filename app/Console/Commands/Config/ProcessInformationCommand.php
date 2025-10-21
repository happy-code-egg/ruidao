<?php

namespace App\Console\Commands\Config;

use App\Models\ProcessInformation;

class ProcessInformationCommand extends BaseConfigImportCommand
{
    protected $signature = 'config:process-information';
    protected $description = '导入处理事项设置数据';

    protected function getExcelFileName(): string
    {
        return 'process_information.xlsx';
    }

    protected function getTableName(): string
    {
        return 'process_informations';
    }

    protected function getModelClass(): string
    {
        return ProcessInformation::class;
    }

    protected function processData(array $data): array
    {
        // 处理JSON字段
        if (isset($data['business_type']) && is_string($data['business_type'])) {
            $data['business_type'] = json_decode($data['business_type'], true) ?: [$data['business_type']];
        }
        if (isset($data['application_type']) && is_string($data['application_type'])) {
            $data['application_type'] = json_decode($data['application_type'], true) ?: [$data['application_type']];
        }
        if (isset($data['country']) && is_string($data['country'])) {
            $data['country'] = json_decode($data['country'], true) ?: [$data['country']];
        }
        if (isset($data['process_status']) && is_string($data['process_status'])) {
            $data['process_status'] = json_decode($data['process_status'], true) ?: [$data['process_status']];
        }

        return $this->addUserInfo($this->addTimestamps($data));
    }
}

/*
Excel字段说明 (process_information.xlsx):
- case_type: 案件类型
- business_type: 业务类型 (JSON数组格式，如: ["发明专利","实用新型专利"])
- application_type: 申请类型 (JSON数组格式)
- country: 国家 (JSON数组格式)
- process_name: 处理事项名称
- flow_completed: 流程是否完成 (1/0)
- proposal_inquiry: 提案询问 (1/0)
- data_updater_inquiry: 数据更新询问 (1/0)
- update_case_handler: 更新案件处理人 (1/0)
- process_status: 处理状态 (JSON数组格式)
- case_phase: 案件阶段
- process_type: 处理事项类型
- is_case_node: 是否为案件节点 (1/0)
- is_commission: 是否提成 (1/0)
- is_valid: 是否有效 (1/0)
- sort_order: 排序
- consultant_contract: 顾问合同 (1/0)
*/
