<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseTypes;
use App\Models\User;

class CaseTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CaseTypes::class;
    }

    /**
     * 获取验证规则
     * 
     * 请求参数：
     * - name（案件类型名称）：必填，字符串，最大100字符
     * - code（案件类型编码）：必填，字符串，最大50字符，唯一性验证
     * - description（描述）：可选，字符串
     * - status（状态）：必填，整数，0=禁用，1=启用
     * - sort_order（排序）：可选，整数，最小值0
     * - type_name（类型名称）：必填，字符串，最大100字符
     * - agency_name（代理机构名称）：可选，字符串，最大200字符
     * 
     * 返回参数：
     * - id（主键ID）：整数，自增主键
     * - name（案件类型名称）：字符串，案件类型的显示名称
     * - code（案件类型编码）：字符串，案件类型的唯一标识码
     * - description（描述）：字符串，案件类型的详细描述
     * - status（状态）：整数，0=禁用，1=启用
     * - sort_order（排序）：整数，用于列表排序
     * - type_name（类型名称）：字符串，案件的具体类型名称
     * - agency_name（代理机构名称）：字符串，关联的代理机构名称
     * - created_by（创建人ID）：整数，创建记录的用户ID
     * - updated_by（更新人ID）：整数，最后更新记录的用户ID
     * - created_at（创建时间）：时间戳，记录创建时间
     * - updated_at（更新时间）：时间戳，记录最后更新时间
     * 
     * @param bool $isUpdate 是否为更新操作，影响唯一性验证规则
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 定义基础验证规则
        $rules = [
            'name' => 'required|string|max:100',           
            'code' => 'required|string|max:50',            
            'description' => 'nullable|string',            
            'status' => 'required|in:0,1',                
            'sort_order' => 'nullable|integer|min:0',     
            'type_name' => 'required|string|max:100',      
            'agency_name' => 'nullable|string|max:200',   
        ];

        // 根据操作类型设置唯一性验证规则
        if ($isUpdate) {
            // 更新操作：排除当前记录的ID，避免自己和自己冲突
            $id = request()->route('id');
            $rules['code'] .= '|unique:case_types,code,' . $id;
        } else {
            // 创建操作：检查编码在整个表中的唯一性
            $rules['code'] .= '|unique:case_types,code';
        }
        
        return $rules;
    }

    /**
     * 获取验证消息
     * 
     * 功能说明：
     * - 继承父类的基础验证消息
     * - 可以在此添加案件类型特有的验证消息
     * - 用于自定义验证失败时的错误提示文本
     * 
     * 返回参数：
     * - array：验证消息数组，键为验证规则，值为错误提示文本
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 案件类型特有的验证消息可以在这里添加
            // 例如：'type_name.required' => '案件类型名称不能为空',
            // 目前使用父类的通用验证消息即可满足需求
        ]);
    }
}