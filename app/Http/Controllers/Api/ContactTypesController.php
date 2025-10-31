<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactTypes;
use App\Models\User;

class ContactTypesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     * 
     * 功能说明：
     * - 返回联系人类型控制器对应的模型类
     * - 继承自BaseDataConfigController的抽象方法实现
     * - 用于基础控制器的CRUD操作中确定操作的数据模型
     * - 提供统一的模型类获取接口，便于基础控制器进行数据操作
     * 
     * 请求参数：
     * - 无需传入参数
     * 
     * 响应参数：
     * - string: 返回ContactTypes模型的完整类名
     *   - 用于基础控制器的增删改查操作
     *   - 确保操作的是contact_types数据表
     * 
     * 内部业务逻辑：
     * - 直接返回ContactTypes模型类的类名常量
     * - 配合基础控制器实现联系人类型的标准化数据管理
     * - 支持联系人类型的创建、读取、更新、删除等操作
     * - 管理客户联系人的分类信息（如：经办人、技术人员、财务人员、IPR、发明人等）
     * 
     * @return string 联系人类型模型的完整类名
     */
    protected function getModelClass()
    {
        return ContactTypes::class; // 返回联系人类型模型类，用于基础控制器的数据操作
    }

    /**
     * 获取联系人类型数据验证规则
     * 
     * 功能说明：
     * - 定义联系人类型数据的验证规则，确保数据完整性和有效性
     * - 支持创建和更新操作的不同验证需求
     * - 验证联系人类型的关键字段：名称、编码、类型名称、状态等
     * - 确保编码的唯一性，防止重复数据
     * 
     * 请求参数详细说明：
     * - $isUpdate (bool): 是否为更新操作标识
     *   - true: 更新操作，编码唯一性校验排除当前记录
     *   - false: 创建操作，编码唯一性校验全表
     * - name (string): 联系人类型名称，必填，最大100字符
     * - code (string): 联系人类型编码，必填，最大50字符，全表唯一
     * - description (string): 联系人类型描述，可选，文本类型
     * - status (int): 状态，必填，0-禁用，1-启用
     * - sort_order (int): 排序值，可选，非负整数，用于前端显示排序
     * - type_name (string): 类型名称，必填，最大100字符，用于分类标识
     * 
     * 响应参数详细说明：
     * - array: 返回Laravel验证规则数组
     *   - 包含所有字段的验证规则定义
     *   - 支持Laravel验证器直接使用
     *   - 根据操作类型动态调整唯一性验证
     * 
     * 内部业务逻辑详细说明：
     * - 基础验证：定义所有字段的基本验证规则（必填、类型、长度等）
     * - 数值验证：sort_order必须为非负整数，确保排序的合理性
     * - 枚举验证：status字段限制为0或1，确保状态值的有效性
     * - 唯一性验证：code字段在contact_types表中必须唯一
     *   - 创建时：检查整个表的唯一性
     *   - 更新时：排除当前记录ID，允许保持原编码不变
     * - 异常处理：所有验证规则都会被Laravel验证器自动处理
     * 
     * @param bool $isUpdate 是否为更新操作
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',           // 联系人类型名称：必填，字符串，最大100字符
            'code' => 'required|string|max:50',            // 联系人类型编码：必填，字符串，最大50字符
            'description' => 'nullable|string',            // 描述信息：可选，字符串类型
            'status' => 'required|in:0,1',                 // 状态：必填，0-禁用，1-启用
            'sort_order' => 'nullable|integer|min:0',      // 排序值：可选，非负整数
            'type_name' => 'required|string|max:100',      // 类型名称：必填，字符串，最大100字符
        ];

        // 根据操作类型设置编码唯一性验证规则
        if ($isUpdate) {
            $id = request()->route('id');                  // 获取当前更新记录的ID
            $rules['code'] .= '|unique:contact_types,code,' . $id;  // 更新时排除当前记录的唯一性检查
        } else {
            $rules['code'] .= '|unique:contact_types,code';         // 创建时检查全表唯一性
        }

        return $rules;  // 返回完整的验证规则数组
    }

    /**
     * 获取联系人类型数据验证消息
     * 
     * 功能说明：
     * - 定义联系人类型数据验证失败时的错误消息
     * - 继承父类BaseDataConfigController的通用验证消息
     * - 可以添加特定于联系人类型模块的自定义验证消息
     * - 为前端提供友好的错误提示信息
     * 
     * 请求参数：
     * - 无需传入参数
     * 
     * 响应参数详细说明：
     * - array: 验证消息数组，键为验证规则，值为错误消息
     *   - 继承父类的通用验证消息：
     *     - 'name.required' => '名称不能为空'
     *     - 'name.max' => '名称长度不能超过100个字符'
     *     - 'code.required' => '编码不能为空'
     *     - 'code.unique' => '编码已存在'
     *     - 'code.max' => '编码长度不能超过50个字符'
     *     - 'status.required' => '状态不能为空'
     *     - 'status.in' => '状态值无效'
     *     - 'sort_order.integer' => '排序必须是整数'
     *   - 可扩展添加联系人类型特定的验证消息，如：
     *     - 'type_name.required' => '类型名称不能为空'
     *     - 'type_name.max' => '类型名称长度不能超过100个字符'
     * 
     * 内部业务逻辑详细说明：
     * - 使用array_merge合并父类和子类的验证消息
     * - 父类提供基础字段的通用验证消息（name、code、status、sort_order等）
     * - 子类可以覆盖父类消息或添加特有字段的验证消息
     * - 支持多语言错误消息扩展
     * - 确保验证失败时提供清晰的错误提示
     * 
     * @return array 验证消息数组
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 联系人类型特有的验证消息可以在这里添加
            // 示例：
            // 'type_name.required' => '类型名称不能为空',
            // 'type_name.max' => '类型名称长度不能超过100个字符',
            // 'description.max' => '描述信息长度不能超过500个字符',
            // 目前使用父类的通用验证消息即可满足联系人类型的验证需求
        ]);
    }
}