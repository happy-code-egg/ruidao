<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessCoefficient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProcessCoefficientsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ProcessCoefficient::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'sort' => 'nullable|integer|min:1',
            'is_valid' => 'required|in:0,1',
            'updated_by' => 'nullable|string|max:100'
        ];

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '处理事项系数名称不能为空',
            'name.max' => '处理事项系数名称长度不能超过100个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'is_valid.required' => '是否有效不能为空',
            'is_valid.in' => '是否有效值无效',
        ]);
    }

    /**
     * 获取列表
     */
    public function index(Request $request)
    {
        try {
            $query = ProcessCoefficient::query();

            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('is_valid')) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->ordered();

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项系数列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建
     */
    public function store(Request $request)
    {
        $this->beforeStore($request);
        return parent::store($request);
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        $this->beforeUpdate($request, $id);
        return parent::update($request, $id);
    }

    /**
     * 创建前处理数据
     */
    protected function beforeStore(Request $request)
    {
        // 设置更新人
        if (!$request->filled('updated_by')) {
            $request->merge(['updated_by' => '系统']);
        }

        // 设置默认排序
        if (!$request->filled('sort')) {
            $maxSort = ProcessCoefficient::max('sort') ?? 0;
            $request->merge(['sort' => $maxSort + 1]);
        }
    }

    /**
     * 更新前处理数据
     */
    protected function beforeUpdate(Request $request, $id)
    {
        // 设置更新人
        if (!$request->filled('updated_by')) {
            $request->merge(['updated_by' => '系统']);
        }
    }

    /**
     * 获取选项数据
     */
    public function options(Request $request)
    {
        try {
            $data = ProcessCoefficient::valid()
                ->ordered()
                ->select('id', 'name', 'sort')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项系数选项失败');
            return json_fail('获取选项失败');
        }
    }

    /**
     * 批量更新状态
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:process_coefficients,id',
                'is_valid' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            ProcessCoefficient::whereIn('id', $request->ids)
                ->update([
                    'is_valid' => $request->is_valid,
                    'updated_by' => '系统',
                    'updated_at' => now()
                ]);

            return json_success('批量更新成功');

        } catch (\Exception $e) {
            log_exception($e, '批量更新处理事项系数状态失败');
            return json_fail('批量更新失败');
        }
    }
}
