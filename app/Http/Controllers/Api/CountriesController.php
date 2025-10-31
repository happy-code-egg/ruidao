<?php

namespace App\Http\Controllers\Api;

use App\Models\Countries;
use App\Models\User;

/**
 * 国家（Countries）数据配置控制器
 *
 * - 继承自 `BaseDataConfigController`，复用通用的配置项增删改查、分页、搜索等接口能力
 * - 通过覆盖模型类、验证规则与提示，完成对“国家配置项”的业务校验
 *
 * 该控制器主要用于：
 * - 提供后端接口供前端维护国家信息（名称、编码、英文名等）
 * - 在创建/更新时进行字段合法性验证与唯一性校验
 */
class CountriesController extends BaseDataConfigController
{
    /**
     * 获取当前控制器所对应的 Eloquent 模型类名
     *
     * @return string 模型类全名（FQCN）
     */
    protected function getModelClass()
    {
        // 返回绑定的模型类，使父类能够基于该模型执行通用的CRUD逻辑
        return Countries::class;
    }

    /**
     * 创建/更新接口的字段规则说明（用于接口文档）
     *
     * 功能说明：
     * - 该方法描述了“国家配置”在创建（POST）与更新（PUT）接口中需要的请求参数校验规则
     * - 实际接口由父类 BaseDataConfigController 提供（store/update），本处用于文档化参数与返回结构
     *
     * 路由参数（仅更新）
     * - id 路由参数：更新接口的资源ID，用于唯一性校验时排除当前记录
     *
     * 请求体参数（JSON）
     * @bodyParam name string required 配置名称，最大100字符。
     * @bodyParam code string required 配置编码，最大50字符，唯一；更新时对当前id做唯一性排除。
     * @bodyParam description string nullable 描述信息。
     * @bodyParam status int required 状态，0=禁用，1=启用。
     * @bodyParam sort_order int nullable 排序，最小0。
     * @bodyParam country_name string required 国家中文名称，最大100字符。
     * @bodyParam country_name_en string nullable 国家英文名称，最大100字符。
     * @bodyParam country_code string nullable 国家代码（如电话或ISO代码），最大10字符。
     *
     * 响应示例（父类统一返回结构）
     * @response 200 {
     *   "code": 0,
     *   "msg": "创建成功",
     *   "data": {
     *     "id": 1,
     *     "name": "国家配置",
     *     "code": "CN",
     *     "description": "中国相关配置",
     *     "status": 1,
     *     "sort_order": 0,
     *     "country_name": "中国",
     *     "country_name_en": "China",
     *     "country_code": "CN",
     *     "created_by": 1,
     *     "updated_by": 1
     *   },
     *   "success": true
     * }
     *
     * @response 200 {
     *   "code": 1,
     *   "msg": "编码已存在",
     *   "data": null,
     *   "success": false
     * }
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
            'sort_order' => 'nullable|integer|min:0',
            // 国家业务字段
            'country_name' => 'required|string|max:100',
            'country_name_en' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
        ];

        // code 字段需要唯一：更新场景排除当前记录，创建场景直接唯一
        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:countries,code,' . $id;
        } else {
            $rules['code'] .= '|unique:countries,code';
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
        // 合并父类的默认提示，同时为本模块预留更具体的提示位
        return array_merge(parent::getValidationMessages(), [
            // 例如：'country_name.required' => '国家名称不能为空',
        ]);
    }
}