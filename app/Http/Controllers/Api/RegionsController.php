<?php

namespace App\Http\Controllers\Api;

use App\Models\Regions;
use App\Models\User;

/**
 * 地区（Regions）基础数据配置控制器
 *
 * 功能:
 * - 为父类 `BaseDataConfigController` 提供模型绑定与字段校验规则
 * - 统一各接口的入参校验信息（创建/更新场景）
 *
 * 路由说明:
 * - 当前控制器未在 `routes/api.php` 中直接看到绑定；如需对外接口，建议使用 `/api/data-config/regions*` 或 `/api/config/regions*` 路由规则并在路由文件中绑定
 * - 项目中已有与地区相关的其他接口（例如 `SearchController@getRegions` 及 `TechServiceRegionsController`），本控制器定位为通用数据配置的模型/校验提供者
 *
 * 依赖:
 * - 模型 `App\Models\Regions`
 * - 继承 `BaseDataConfigController`，使用其通用 CRUD 能力与统一响应结构
 */
class RegionsController extends BaseDataConfigController
{
    /**
     * 获取模型类
     *
     * 功能:
     * - 指定当前数据配置控制器使用的 Eloquent 模型类（FQCN）
     *
     * 返回参数:
     * - `string` 模型类名：`App\Models\Regions`
     *
     * 内部说明:
     * - 该方法由父类在运行时调用，不直接暴露为路由接口
     */
    protected function getModelClass()
    {
        return Regions::class;
    }

    /**
     * 获取创建/更新的校验规则
     *
     * 功能:
     * - 返回用于 `store`/`update` 的字段校验规则，根据 `$isUpdate` 场景处理唯一性
     *
     * 接口（参考父类提供的标准 CRUD）:
     * - POST `/api/data-config/regions`（创建）
     * - PUT `/api/data-config/regions/{id}`（更新）
     * - 如使用 `/api/config/regions*` 路由前缀亦同理
     *
     * 请求参数:
     * - `name` 必填，字符串，最长 100
     * - `code` 必填，字符串，最长 50，唯一（`regions.code`）
     * - `description` 可选，字符串
     * - `status` 必填，枚举 `0/1`
     * - `sort_order` 可选，整数，最小 0
     * - `region_name` 必填，字符串，最长 100
     * - `region_code` 可选，字符串，最长 50
     * - `parent_id` 可选，整数（父级地区 id）
     * - `level` 可选，整数，范围 1..5
     *
     * 返回参数:
     * - `array` 验证规则数组，供 `Validator` 使用
     *
     * 内部说明:
     * - 更新场景下对 `code` 的唯一性校验会排除当前记录 `id`
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'region_name' => 'required|string|max:100',
            'region_code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|integer',
            'level' => 'nullable|integer|min:1|max:5',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:regions,code,' . $id;
        } else {
            $rules['code'] .= '|unique:regions,code';
        }

        return $rules;
    }

    /**
     * 获取校验消息
     *
     * 功能:
     * - 返回字段校验的定制化消息，默认合并父类通用消息
     *
     * 返回参数:
     * - `array` 键值对形式的验证消息
     *
     * 内部说明:
     * - 可在此处覆盖或追加特定字段的错误提示，当前保持与父类一致
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 可以在这里添加特定的验证消息
        ]);
    }
}