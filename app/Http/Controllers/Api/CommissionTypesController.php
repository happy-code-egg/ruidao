<?php

namespace App\Http\Controllers\Api;

use App\Models\CommissionTypes;

class CommissionTypesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 功能说明：
     * - 返回提成类型控制器对应的模型类
     * - 继承自BaseDataConfigController的抽象方法实现
     * - 用于基础控制器的CRUD操作中确定操作的数据模型
     * - 提供统一的模型类获取接口，便于基础控制器进行数据操作
     * 
     * 请求参数：
     * - 无需传入参数
     * 
     * 响应参数：
     * - string: 返回CommissionTypes模型的完整类名
     *   - 用于基础控制器的增删改查操作
     *   - 确保操作的是commission_types数据表
     * 
     * 内部业务逻辑：
     * - 直接返回CommissionTypes模型类的类名常量
     * - 配合基础控制器实现提成类型的标准化数据管理
     * - 支持提成类型的创建、读取、更新、删除等操作
     * 
     * @return string 提成类型模型的完整类名
     */
    protected function getModelClass()
    {
        return CommissionTypes::class; // 返回提成类型模型类，用于基础控制器的数据操作
    }

    /**
     * 获取验证规则
     * 
     * 功能说明：
     * - 定义提成类型数据的验证规则，确保数据完整性和有效性
     * - 支持创建和更新两种操作模式的不同验证需求
     * - 验证提成类型的关键字段：名称、编码、比例、状态等
     * - 确保数据符合业务规则和数据库约束条件
     * 
     * 请求参数：
     * - $isUpdate (boolean): 是否为更新操作，默认false
     *   - true: 更新操作，需要排除当前记录的唯一性检查
     *   - false: 创建操作，执行完整的唯一性检查
     * - name (string): 提成类型名称，必填，最大100字符（如：基础提成、奖励提成、年度提成）
     * - code (string): 提成类型编码，可选，最大50字符（如：base_commission、bonus_commission）
     * - description (string): 描述信息，可选，文本类型（详细说明提成类型的用途和计算方式）
     * - status (integer): 状态，必填，枚举值0或1（0=禁用，1=启用）
     * - sort_order (integer): 排序序号，可选，最小值0（用于列表排序显示）
     * - rate (numeric): 提成比例，可选，数值范围0-100（百分比形式，如：5.5表示5.5%）
     * 
     * 响应参数：
     * - array: 验证规则数组，包含各字段的Laravel验证规则
     *   - 字段名 => 验证规则字符串（Laravel验证规则格式）
     *   - 支持必填验证、类型验证、长度验证、数值范围验证、枚举值验证、唯一性验证
     * 
     * 内部业务逻辑：
     * - 基础验证：验证必填字段、数据类型、字符长度等基本规则
     * - 数值验证：验证提成比例字段的合理范围（0-100%）
     * - 枚举验证：验证状态字段的有效值（启用/禁用）
     * - 唯一性验证：在更新操作时排除当前记录，避免自身冲突
     * - 兼容性支持：同时支持创建和更新操作的不同验证需求
     * 
     * @param boolean $isUpdate 是否为更新操作
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 定义基础验证规则
        $rules = [
            'name' => 'required|string|max:100',        // 提成类型名称：必填，字符串，最大100字符
            'code' => 'nullable|string|max:50',         // 提成类型编码：可选，字符串，最大50字符
            'description' => 'nullable|string',         // 描述信息：可选，文本类型
            'status' => 'required|in:0,1',              // 状态：必填，枚举值（0=禁用，1=启用）
            'sort_order' => 'nullable|integer|min:0'    // 排序序号：可选，整数，最小值0
        ];

        // 添加特定字段验证：提成比例字段
        $rules['rate'] = 'nullable|numeric|min:0|max:100'; // 提成比例：可选，数值，范围0-100（百分比）

        // 更新操作时的特殊处理：编码唯一性验证
        if ($isUpdate) {
            $id = request()->route('id');                           // 获取当前更新记录的ID
            $rules['code'] .= '|unique:commission_types,code,' . $id . ',id'; // 排除当前记录的唯一性检查
        }

        return $rules; // 返回完整的验证规则数组
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '提成类型名称不能为空',
        ]);
    }
}
