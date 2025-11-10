<?php

namespace App\Http\Controllers\Api;

use App\Models\IdTypes;
use App\Models\User;

class IdTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return IdTypes::class;
    }

   /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义证件类型数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - name (string): 证件类型名称，必填，字符串，最大100字符
 *   - code (string): 证件类型编码，必填，字符串，最大50字符，唯一性验证
 *   - description (string): 描述，可选，字符串
 *   - status (int): 状态，必填，只能是0或1
 *   - sort_order (int): 排序，可选，整数，最小值0
 *   - type_name (string): 类型名称，必填，字符串，最大100字符
 *   - type_code (string): 类型编码，必填，字符串，最大50字符
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'name' => 'required|string|max:100',           // 证件类型名称：必填，字符串，最大100字符
        'code' => 'required|string|max:50',            // 证件类型编码：必填，字符串，最大50字符
        'description' => 'nullable|string',             // 描述：可为空，字符串
        'status' => 'required|in:0,1',                 // 状态：必填，只能是0或1
        'sort_order' => 'nullable|integer|min:0',      // 排序：可为空，整数，最小值0
        'type_name' => 'required|string|max:100',      // 类型名称：必填，字符串，最大100字符
        'type_code' => 'required|string|max:50',       // 类型编码：必填，字符串，最大50字符
    ];

    // 根据是否为更新操作设置编码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性验证
        $id = request()->route('id');
        $rules['code'] .= '|unique:id_types,code,' . $id;
    } else {
        // 创建时，全局唯一性验证
        $rules['code'] .= '|unique:id_types,code';
    }

    // 返回验证规则
    return $rules;
}

/**
 * 获取验证错误消息 getValidationMessages
 *
 * 功能描述：定义验证失败时的错误消息，继承父类消息并可添加特定消息
 *
 * 传入参数：无
 *
 * 输出参数：
 * - array: 验证错误消息数组
 */
protected function getValidationMessages()
{
    // 合并父类的验证消息和当前类的特定验证消息
    return array_merge(parent::getValidationMessages(), [
        // 可以在这里添加特定的验证消息
    ]);
}

}
