<?php

namespace App\Http\Controllers\Api;

use App\Models\Currencies;
use App\Models\User;

/**
 * 货币管理控制器
 * 
 * 继承自 BaseDataConfigController，提供货币信息的增删改查功能
 * 包括货币名称、货币代码、符号、汇率等信息的管理
 * 
 * @package App\Http\Controllers\Api
 * @author 系统管理员
 */
class CurrenciesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 返回当前控制器对应的模型类，用于基础控制器的通用操作
     * 
     * @return string 模型类名
     */
    protected function getModelClass()
    {
        return Currencies::class; // 返回货币模型类
    }

    /**
     * 获取验证规则
     * 
     * 定义货币数据的验证规则，包括创建和更新场景
     * 
     * @param bool $isUpdate 是否为更新操作，默认为false（创建操作）
     * 
     * @urlParam id integer 货币ID（仅更新时需要）
     * 
     * @bodyParam name string required 货币显示名称，最大长度100字符 Example: 人民币
     * @bodyParam code string required 货币编码，最大长度50字符，全局唯一 Example: CNY
     * @bodyParam description string 货币描述信息 Example: 中华人民共和国法定货币
     * @bodyParam status integer required 状态：0-禁用，1-启用 Example: 1
     * @bodyParam sort_order integer 排序值，数值越小越靠前 Example: 1
     * @bodyParam currency_name string required 货币全称，最大长度100字符 Example: 人民币
     * @bodyParam currency_code string required 国际货币代码，最大长度10字符 Example: CNY
     * @bodyParam symbol string 货币符号，最大长度10字符 Example: ¥
     * @bodyParam exchange_rate numeric 汇率，相对于基准货币的汇率 Example: 1.0000
     * 
     * @response 200 {
     *   "success": true,
     *   "code": 0,
     *   "msg": "操作成功",
     *   "data": {
     *     "id": 1,
     *     "name": "人民币",
     *     "code": "CNY",
     *     "description": "中华人民共和国法定货币",
     *     "status": 1,
     *     "sort_order": 1,
     *     "currency_name": "人民币",
     *     "currency_code": "CNY",
     *     "symbol": "¥",
     *     "exchange_rate": "1.0000",
     *     "created_at": "2024-01-01 00:00:00",
     *     "updated_at": "2024-01-01 00:00:00"
     *   }
     * }
     * 
     * @response 422 {
     *   "success": false,
     *   "code": 1,
     *   "msg": "验证失败",
     *   "errors": {
     *     "name": ["货币名称不能为空"],
     *     "code": ["货币编码已存在"]
     *   }
     * }
     * 
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 基础验证规则
        $rules = [
            'name' => 'required|string|max:100',           // 货币名称：必填，字符串，最大100字符
            'code' => 'required|string|max:50',            // 货币编码：必填，字符串，最大50字符
            'description' => 'nullable|string',            // 描述：可选，字符串
            'status' => 'required|in:0,1',                 // 状态：必填，只能是0或1
            'sort_order' => 'nullable|integer|min:0',      // 排序：可选，非负整数
            'currency_name' => 'required|string|max:100',  // 货币全称：必填，字符串，最大100字符
            'currency_code' => 'required|string|max:10',   // 国际货币代码：必填，字符串，最大10字符
            'symbol' => 'nullable|string|max:10',          // 货币符号：可选，字符串，最大10字符
            'exchange_rate' => 'nullable|numeric|min:0',   // 汇率：可选，数值，非负数
        ];

        // 根据操作类型设置编码唯一性验证
        if ($isUpdate) {
            // 更新时：排除当前记录的编码唯一性检查
            $id = request()->route('id');
            $rules['code'] .= '|unique:currencies,code,' . $id;
        } else {
            // 创建时：检查编码在整个表中的唯一性
            $rules['code'] .= '|unique:currencies,code';
        }

        return $rules;
    }

    /**
     * 获取验证错误消息
     * 
     * 定义自定义的验证错误消息，与父类消息合并
     * 
     * @return array 验证错误消息数组
     */
    protected function getValidationMessages()
    {
        // 合并父类验证消息，可在此添加货币特定的错误消息
        return array_merge(parent::getValidationMessages(), [
            // 可以在这里添加特定的验证消息
        ]);
    }
}