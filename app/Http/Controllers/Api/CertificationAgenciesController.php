<?php

namespace App\Http\Controllers\Api;

use App\Models\CertificationAgencies;
use App\Models\User;

class CertificationAgenciesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 功能说明：
     * - 返回认证机构模型的完整类名
     * - 供父类BaseDataConfigController使用，用于实例化模型
     * - 实现父类的抽象方法，定义具体的业务模型
     * 
     * 返回参数：
     * - string：CertificationAgencies模型的完整类名
     */
    protected function getModelClass()
    {
        return CertificationAgencies::class;
    }

    /**
     * 获取验证规则
     * 
     * 请求参数：
     * - name（认证机构名称）：必填，字符串，最大100字符
     * - code（认证机构编码）：必填，字符串，最大50字符，唯一性验证
     * - description（描述）：可选，字符串
     * - status（状态）：必填，整数，0=禁用，1=启用
     * - sort_order（排序）：可选，整数，最小值0
     * - agency_name（代理机构名称）：必填，字符串，最大200字符
     * - agency_code（代理机构编码）：可选，字符串，最大50字符
     * - contact_info（联系信息）：可选，字符串
     * 
     * 返回参数：
     * - id（主键ID）：整数，自增主键
     * - name（认证机构名称）：字符串，认证机构的显示名称
     * - code（认证机构编码）：字符串，认证机构的唯一标识码
     * - description（描述）：字符串，认证机构的详细描述
     * - status（状态）：整数，0=禁用，1=启用
     * - sort_order（排序）：整数，用于列表排序
     * - agency_name（代理机构名称）：字符串，关联的代理机构名称
     * - agency_code（代理机构编码）：字符串，代理机构的编码标识
     * - contact_info（联系信息）：字符串，认证机构的联系方式信息
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
            'name' => 'required|string|max:100',           // 认证机构名称：必填，字符串，最大100字符
            'code' => 'required|string|max:50',            // 认证机构编码：必填，字符串，最大50字符
            'description' => 'nullable|string',            // 描述：可选，字符串
            'status' => 'required|in:0,1',                 // 状态：必填，0=禁用，1=启用
            'sort_order' => 'nullable|integer|min:0',      // 排序：可选，整数，最小值0
            'agency_name' => 'required|string|max:200',    // 代理机构名称：必填，字符串，最大200字符
            'agency_code' => 'nullable|string|max:50',     // 代理机构编码：可选，字符串，最大50字符
            'contact_info' => 'nullable|string',           // 联系信息：可选，字符串
        ];

        // 根据操作类型设置唯一性验证规则
        if ($isUpdate) {
            // 更新操作：排除当前记录的ID，避免自己和自己冲突
            $id = request()->route('id');
            $rules['code'] .= '|unique:certification_agencies,code,' . $id;
        } else {
            // 创建操作：检查编码在整个表中的唯一性
            $rules['code'] .= '|unique:certification_agencies,code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     * 
     * 功能说明：
     * - 继承父类的基础验证消息
     * - 可以在此添加认证机构特有的验证消息
     * - 用于自定义验证失败时的错误提示文本
     * 
     * 返回参数：
     * - array：验证消息数组，键为验证规则，值为错误提示文本
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 认证机构特有的验证消息可以在这里添加
            // 例如：'agency_name.required' => '代理机构名称不能为空',
            // 目前使用父类的通用验证消息即可满足需求
        ]);
    }
}