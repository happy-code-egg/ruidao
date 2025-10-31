<?php

namespace App\Http\Controllers\Api;

use App\Models\Cities;
use App\Models\User;

class CitiesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 功能说明：
     * - 返回城市模型的完整类名
     * - 供父类BaseDataConfigController使用，用于实例化模型
     * - 实现父类的抽象方法，定义具体的业务模型
     * 
     * 返回参数：
     * - string：Cities模型的完整类名
     */
    protected function getModelClass()
    {
        return Cities::class;
    }

    /**
     * 获取验证规则
     * 
     * 功能说明：
     * - 定义城市数据的验证规则
     * - 支持新增和更新两种模式的验证
     * - 确保数据的完整性和唯一性
     * 
     * 请求参数：
     * - name (string, required): 城市名称，最大长度100字符
     * - code (string, required): 城市编码，最大长度50字符，全局唯一
     * - description (string, optional): 城市描述，可为空
     * - status (integer, required): 状态，0-禁用，1-启用
     * - sort_order (integer, optional): 排序值，最小值为0，默认为0
     * - city_name (string, required): 城市名称，最大长度100字符
     * - city_name_en (string, optional): 城市英文名称，最大长度100字符
     * - short_name (string, optional): 城市简称，最大长度10字符
     * 
     * 响应参数：
     * - array: 验证规则数组，包含以下字段的验证规则
     *   - id (bigint): 主键ID，自增
     *   - name (varchar(100)): 城市名称
     *   - code (varchar(50)): 城市编码，唯一索引
     *   - description (text): 城市描述
     *   - status (tinyint): 状态，0-禁用，1-启用，默认1
     *   - sort_order (int): 排序值，默认0
     *   - city_name (varchar(100)): 城市名称
     *   - city_name_en (varchar(100)): 城市英文名称
     *   - short_name (varchar(10)): 城市简称
     *   - created_by (bigint): 创建人ID
     *   - updated_by (bigint): 更新人ID
     *   - created_at (timestamp): 创建时间
     *   - updated_at (timestamp): 更新时间
     * 
     * @param bool $isUpdate 是否为更新操作，默认false
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 基础验证规则
        $rules = [
            'name' => 'required|string|max:100',           // 城市名称：必填，字符串，最大100字符
            'code' => 'required|string|max:50',            // 城市编码：必填，字符串，最大50字符
            'description' => 'nullable|string',            // 描述：可选，字符串
            'status' => 'required|in:0,1',                 // 状态：必填，只能是0或1
            'sort_order' => 'nullable|integer|min:0',      // 排序：可选，整数，最小值0
            'city_name' => 'required|string|max:100',      // 城市名称：必填，字符串，最大100字符
            'city_name_en' => 'nullable|string|max:100',   // 英文名称：可选，字符串，最大100字符
            'short_name' => 'nullable|string|max:10',      // 简称：可选，字符串，最大10字符
        ];

        // 根据操作类型设置编码唯一性验证
        if ($isUpdate) {
            // 更新时排除当前记录的编码唯一性检查
            $id = request()->route('id');
            $rules['code'] .= '|unique:cities,code,' . $id;
        } else {
            // 新增时检查编码全局唯一性
            $rules['code'] .= '|unique:cities,code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     * 
     * 功能说明：
     * - 定义城市数据验证失败时的错误消息
     * - 继承父类的通用验证消息
     * - 可以添加特定于城市模块的自定义验证消息
     * 
     * 返回参数：
     * - array: 验证消息数组，键为验证规则，值为错误消息
     *   - 继承父类BaseDataConfigController的通用验证消息
     *   - 可扩展添加城市特定的验证消息，如：
     *     - 'name.required' => '城市名称不能为空'
     *     - 'code.unique' => '城市编码已存在'
     *     - 'city_name.required' => '城市名称不能为空'
     * 
     * @return array 验证消息数组
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 可以在这里添加特定的验证消息
            // 示例：
            // 'name.required' => '城市名称不能为空',
            // 'code.unique' => '城市编码已存在，请使用其他编码',
            // 'city_name.required' => '城市名称不能为空',
            // 'status.in' => '状态值无效，只能是0（禁用）或1（启用）',
        ]);
    }
}