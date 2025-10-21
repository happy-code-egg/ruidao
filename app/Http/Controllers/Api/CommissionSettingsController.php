<?php

namespace App\Http\Controllers\Api;

use App\Models\CommissionSettings;

class CommissionSettingsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CommissionSettings::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        return [
            'handler_level' => 'required|string|max:50',
            'case_type' => 'required|string|max:50',
            'business_type' => 'required|string', // 支持逗号分隔的字符串
            'application_type' => 'required|string', // 支持逗号分隔的字符串
            'case_coefficient' => 'required|string', // 支持逗号分隔的字符串
            'matter_coefficient' => 'required|string', // 支持逗号分隔的字符串
            'processing_matter' => 'required|string', // 支持逗号分隔的字符串
            'case_stage' => 'required|string|max:50',
            'commission_type' => 'required|string|max:50',
            'piece_ratio' => 'required|numeric|min:0|max:100',
            'piece_points' => 'required|integer|min:0',
            'country' => 'required|string|max:50',
            'rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'handler_level.required' => '处理人等级不能为空',
            'case_type.required' => '项目类型不能为空',
            'business_type.required' => '业务类型不能为空',
            'application_type.required' => '申请类型不能为空',
            'case_coefficient.required' => '项目系数不能为空',
            'matter_coefficient.required' => '处理事项系数不能为空',
            'processing_matter.required' => '处理事项不能为空',
            'case_stage.required' => '项目阶段不能为空',
            'commission_type.required' => '提成类型不能为空',
            'piece_ratio.required' => '按件比例不能为空',
            'piece_points.required' => '按件点数不能为空',
            'country.required' => '国家（地区）不能为空',
        ]);
    }

    /**
     * 列表查询重载：支持本表字段的关键词与精确筛选
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            // 关键词：在多字段中模糊匹配
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('handler_level', 'like', "%{$keyword}%")
                      ->orWhere('case_type', 'like', "%{$keyword}%")
                      ->orWhere('business_type', 'like', "%{$keyword}%")
                      ->orWhere('application_type', 'like', "%{$keyword}%")
                      ->orWhere('processing_matter', 'like', "%{$keyword}%")
                      ->orWhere('case_stage', 'like', "%{$keyword}%")
                      ->orWhere('commission_type', 'like', "%{$keyword}%")
                      ->orWhere('country', 'like', "%{$keyword}%");
                });
            }

            // 精确筛选
            if ($request->filled('handlerLevel')) {
                $query->where('handler_level', $request->get('handlerLevel'));
            }
            if ($request->filled('caseType')) {
                $query->where('case_type', $request->get('caseType'));
            }
            if ($request->filled('businessType')) {
                $query->where('business_type', $request->get('businessType'));
            }
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            $total = $query->count();

            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
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
            return json_fail('获取列表失败');
        }
    }

    /**
     * 选项列表：返回 id、name、code（组装）
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $items = $modelClass::enabled()->ordered()->get();

            $data = $items->map(function ($item) {
                $name = trim(($item->handler_level ?? '') . ' / ' . ($item->case_type ?? '') . ' / ' . ($item->business_type ?? '') . ' / ' . ($item->processing_matter ?? ''));
                $code = strtolower(str_replace([' ', '／', '/', '—', '-'], '_', $name));
                return [
                    'id' => $item->id,
                    'name' => $name,
                    'code' => $code,
                ];
            });

            return json_success('获取选项成功', $data);
        } catch (\Exception $e) {
            log_exception($e, '获取提成配置选项失败');
            return json_fail('获取选项列表失败');
        }
    }
}