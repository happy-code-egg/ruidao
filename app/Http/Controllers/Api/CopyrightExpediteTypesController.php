<?php

namespace App\Http\Controllers\Api;

use App\Models\CopyrightExpediteTypes;

/**
 * 版权加快类型配置控制器
 *
 * - 继承自 `BaseDataConfigController`，复用通用的配置项增删改查、分页、搜索等接口能力
 * - 通过覆盖模型类、验证规则与提示，完成对“版权加快类型”配置项的业务校验
 *
 * 该控制器主要用于：
 * - 提供后端接口供前端配置“版权加快类型”（如加快天数、加收费等）
 * - 在创建/更新时进行字段合法性验证与唯一性校验
 */
class CopyrightExpediteTypesController extends BaseDataConfigController
{
    /**
     * 获取当前控制器所对应的 Eloquent 模型类名
     *
     * @return string 模型类全名（FQCN）
     */
    protected function getModelClass()
    {
        // 返回绑定的模型类，使父类能够基于该模型执行通用的CRUD逻辑
        return CopyrightExpediteTypes::class;
    }

    /**
     * 返回创建/更新时的字段验证规则
     *
     * @param bool $isUpdate 是否为更新操作（影响唯一性验证的写法）
     * @return array 验证规则数组，供 `Validator` 使用
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 通用字段规则
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 业务特定字段：加快所需天数（必须为正整数）、额外费用（非负数）
        $rules['days'] = 'required|integer|min:1';
        $rules['extra_fee'] = 'required|numeric|min:0';

        // code 字段需要唯一：更新场景排除当前记录，创建场景直接唯一
        if ($isUpdate) {
            // 从路由参数中获取当前记录ID，用于唯一性排除
            $id = request()->route('id');
            $rules['code'] .= '|unique:copyright_expedite_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:copyright_expedite_types,code';
        }

        return $rules;
    }

    /**
     * 返回字段验证自定义消息
     *
     * @return array 自定义消息数组，将与父类默认消息合并
     */
    protected function getValidationMessages()
    {
        // 合并父类的默认提示，同时提供本模块更具体的提示文案
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '版权加快类型名称不能为空',
            'code.required' => '版权加快类型编码不能为空',
            'code.unique' => '版权加快类型编码已存在',
        ]);
    }
}
