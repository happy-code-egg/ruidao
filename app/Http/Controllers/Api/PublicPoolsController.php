<?php

namespace App\Http\Controllers\Api;

use App\Models\PublicPools;
use App\Models\User;

/**
 * 公共池（PublicPools）基础数据配置控制器
 *
 * 功能:
 * - 为父类 `BaseDataConfigController` 提供模型绑定与创建/更新的字段校验规则
 * - 统一字段校验消息（可复用父类并在当前控制器中扩展）
 *
 * 路由说明:
 * - 在 `routes/api.php` 未检索到本控制器的直接路由绑定
 * - 如需对外提供 CRUD 与选项接口，推荐遵循项目规范：
 *   - `/api/data-config/public-pools*` 或 `/api/config/public-pools*`
 *   - 需在路由文件中按父类约定绑定（index/store/show/update/destroy/options/batch-status 等）
 *
 * 统一响应:
 * - 项目使用 `json_success` / `json_fail`（位于 `app/Helpers/json.php`）进行统一返回
 * - 成功：`{ code: 0, msg, data, success: true }`
 * - 失败：`{ code: 1, msg, data, success: false }`
 *
 * 依赖:
 * - 模型 `App\Models\PublicPools`
 * - 继承 `BaseDataConfigController` 的通用 CRUD 能力
 */
class PublicPoolsController extends BaseDataConfigController
{
    /**
     * 获取模型类
     *
     * 功能:
     * - 指定当前数据配置控制器使用的 Eloquent 模型类（FQCN）
     *
     * 返回参数:
     * - `string` 模型类名：`App\Models\PublicPools`
     *
     * 内部说明:
     * - 由父类在运行时调用，用于通用 CRUD 的模型实例化
     */
    protected function getModelClass()
    {
        return PublicPools::class;
    }

    /**
     * 获取创建/更新的校验规则
     *
     * 功能:
     * - 返回用于 `store`/`update` 的字段校验规则，更新场景根据 `id` 放宽唯一性
     *
     * 接口（参考父类的标准 CRUD）:
     * - POST `/api/data-config/public-pools`（创建）
     * - PUT `/api/data-config/public-pools/{id}`（更新）
     * - 如使用 `/api/config/public-pools*` 路由前缀亦同理
     *
     * 请求参数:
     * - `name` 必填，字符串，最长 100
     * - `code` 必填，字符串，最长 50，唯一（`public_pools.code`）
     * - `description` 可选，字符串
     * - `status` 必填，枚举 `0/1`
     * - `sort_order` 可选，整数，最小 0
     * - `pool_name` 必填，字符串，最长 100
     * - `pool_type` 可选，字符串，最长 50
     * - `capacity` 可选，整数，最小 0
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
            'pool_name' => 'required|string|max:100',
            'pool_type' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:0',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:public_pools,code,' . $id;
        } else {
            $rules['code'] .= '|unique:public_pools,code';
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