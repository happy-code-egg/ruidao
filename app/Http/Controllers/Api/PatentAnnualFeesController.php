<?php

namespace App\Http\Controllers\Api;

use App\Models\PatentAnnualFee;
use App\Models\PatentAnnualFeeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PatentAnnualFeesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return PatentAnnualFee::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'case_type' => 'required|string|max:100',
            'apply_type' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'start_date' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'has_fee_guide' => 'nullable|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'is_valid' => 'required|in:0,1',
            'updated_by' => 'nullable|string|max:100'
        ];

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'case_type.required' => '项目类型不能为空',
            'apply_type.required' => '申请类型不能为空',
            'country.required' => '国家（地区）不能为空',
            'start_date.required' => '起算日不能为空',
            'currency.required' => '币别不能为空',
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
            $query = PatentAnnualFee::query();

            // 搜索条件
            if ($request->filled('case_type')) {
                $query->byCaseType($request->case_type);
            }

            if ($request->filled('apply_type')) {
                $query->byApplyType($request->apply_type);
            }

            if ($request->filled('country')) {
                $query->byCountry($request->country);
            }

            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
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

            // 处理返回数据
            foreach ($data as $item) {
                $item->is_valid_text = $item->is_valid_text;
                $item->has_fee_guide_text = $item->has_fee_guide_text;
                $item->created_by = $item->creator->real_name ?? '';
                $item->updated_by = $item->updater->real_name ?? '';
                $item->created_at = $item->created_at;
                $item->updated_at = $item->updated_at;
            }

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取专利年费配置列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取年费详情
     */
    public function getFeeDetails($id)
    {
        try {
            $details = PatentAnnualFeeDetail::where('patent_annual_fee_id', $id)
                ->ordered()
                ->get();

            return json_success('获取成功', $details);

        } catch (\Exception $e) {
            log_exception($e, '获取年费详情失败');
            return json_fail('获取年费详情失败');
        }
    }

    /**
     * 创建年费详情
     */
    public function createFeeDetail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'patent_annual_fee_id' => 'required|integer|exists:patent_annual_fees,id',
                'stage_code' => 'required|string|max:100',
                'rank' => 'required|integer|min:1',
                'official_year' => 'nullable|integer|min:0',
                'official_month' => 'nullable|integer|min:0|max:11',
                'official_day' => 'nullable|integer|min:0|max:31',
                'start_year' => 'nullable|integer|min:0',
                'end_year' => 'nullable|integer|min:0',
                'base_fee' => 'nullable|numeric|min:0',
                'small_fee' => 'nullable|numeric|min:0',
                'micro_fee' => 'nullable|numeric|min:0',
                'authorization_fee' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $detail = PatentAnnualFeeDetail::create($request->all());

            return json_success('创建成功', $detail);

        } catch (\Exception $e) {
            log_exception($e, '创建年费详情失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 更新年费详情
     */
    public function updateFeeDetail(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stage_code' => 'required|string|max:100',
                'rank' => 'required|integer|min:1',
                'official_year' => 'nullable|integer|min:0',
                'official_month' => 'nullable|integer|min:0|max:11',
                'official_day' => 'nullable|integer|min:0|max:31',
                'start_year' => 'nullable|integer|min:0',
                'end_year' => 'nullable|integer|min:0',
                'base_fee' => 'nullable|numeric|min:0',
                'small_fee' => 'nullable|numeric|min:0',
                'micro_fee' => 'nullable|numeric|min:0',
                'authorization_fee' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $detail = PatentAnnualFeeDetail::findOrFail($id);
            $detail->update($request->all());

            return json_success('更新成功', $detail);

        } catch (\Exception $e) {
            log_exception($e, '更新年费详情失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除年费详情
     */
    public function deleteFeeDetail($id)
    {
        try {
            $detail = PatentAnnualFeeDetail::findOrFail($id);
            $detail->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除年费详情失败');
            return json_fail('删除失败');
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
        if (!$request->filled('sort_order')) {
            $maxSort = PatentAnnualFee::max('sort_order') ?? 0;
            $request->merge(['sort_order' => $maxSort + 1]);
        }

        // 设置默认缴费导览
        if (!$request->filled('has_fee_guide')) {
            $request->merge(['has_fee_guide' => 1]);
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
            $data = PatentAnnualFee::valid()
                ->ordered()
                ->select('id', 'case_type', 'apply_type', 'country', 'currency')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取专利年费配置选项失败');
            return json_fail('获取选项失败');
        }
    }
}
