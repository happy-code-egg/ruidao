<?php

namespace App\Http\Controllers\Api;

use App\Models\FeeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 费用配置控制器
 */
class FeeConfigController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return FeeConfig::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'case_type' => 'required|array|min:1',
            'business_type' => 'required|array|min:1',
            'apply_type' => 'required|array|min:1', 
            'country' => 'required|array|min:1',
            'fee_type' => 'required|string|max:100',
            'fee_name' => 'required|string|max:200',
            'fee_name_en' => 'nullable|string|max:200',
            'currency' => 'nullable|string|max:10',
            'fee_code' => 'nullable|string|max:100',
            'base_fee' => 'nullable|numeric|min:0',
            'small_entity_fee' => 'nullable|numeric|min:0',
            'micro_entity_fee' => 'nullable|numeric|min:0',
            'role' => 'nullable|array|min:1',
            'use_stage' => 'nullable|array|min:1',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id');
            if (!empty(request('fee_code'))) {
                $rules['fee_code'] .= '|unique:fee_configs,fee_code,' . $id;
            }
        } else {
            if (!empty(request('fee_code'))) {
                $rules['fee_code'] .= '|unique:fee_configs,fee_code';
            }
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'case_type.required' => '项目类型不能为空',
            'business_type.required' => '业务类型不能为空',
            'business_type.array' => '业务类型必须是数组',
            'business_type.min' => '请至少选择一个业务类型',
            'apply_type.required' => '申请类型不能为空', 
            'apply_type.array' => '申请类型必须是数组',
            'apply_type.min' => '请至少选择一个申请类型',
            'country.required' => '国家(地区)不能为空',
            'country.array' => '国家(地区)必须是数组',
            'country.min' => '请至少选择一个国家(地区)',
            'fee_type.required' => '费用类型不能为空',
            'fee_name.required' => '费用名称不能为空',
            'fee_code.unique' => '费用代码已存在',
            'base_fee.numeric' => '基础费用必须是数字',
            'base_fee.min' => '基础费用不能小于0',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }

    /**
     * 获取列表 - 重写以支持特定的搜索条件
     */
    public function index(Request $request)
    {
        try {
            $query = FeeConfig::query();

            // 项目类型搜索
            if ($request->has('case_type') && !empty($request->case_type)) {
                $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
                if (!in_array('all', $caseTypes)) {
                    $query->where(function($q) use ($caseTypes) {
                        foreach ($caseTypes as $type) {
                            $q->orWhereJsonContains('case_type', $type);
                        }
                    });
                }
            }

            // 业务类型搜索
            if ($request->has('business_type') && !empty($request->business_type)) {
                $businessTypes = is_array($request->business_type) ? $request->business_type : [$request->business_type];
                if (!in_array('all', $businessTypes)) {
                    $query->where(function($q) use ($businessTypes) {
                        foreach ($businessTypes as $type) {
                            $q->orWhereJsonContains('business_type', $type);
                        }
                    });
                }
            }

            // 申请类型搜索
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $applyTypes = is_array($request->apply_type) ? $request->apply_type : [$request->apply_type];
                if (!in_array('all', $applyTypes)) {
                    $query->where(function($q) use ($applyTypes) {
                        foreach ($applyTypes as $type) {
                            $q->orWhereJsonContains('apply_type', $type);
                        }
                    });
                }
            }

            // 国家(地区)搜索
            if ($request->has('country') && !empty($request->country)) {
                $countries = is_array($request->country) ? $request->country : [$request->country];
                if (!in_array('all', $countries)) {
                    $query->where(function($q) use ($countries) {
                        foreach ($countries as $country) {
                            $q->orWhereJsonContains('country', $country);
                        }
                    });
                }
            }

            // 费用类型搜索
            if ($request->has('fee_type') && !empty($request->fee_type)) {
                $query->where('fee_type', $request->fee_type);
            }

            // 费用名称搜索
            if ($request->has('fee_name') && !empty($request->fee_name)) {
                $query->where('fee_name', 'like', "%{$request->fee_name}%");
            }

            // 角色搜索
            if ($request->has('role') && !empty($request->role)) {
                $roles = is_array($request->role) ? $request->role : [$request->role];
                if (!in_array('all', $roles)) {
                    $query->where(function($q) use ($roles) {
                        foreach ($roles as $role) {
                            $q->orWhereJsonContains('role', $role);
                        }
                    });
                }
            }

            // 是否有效搜索
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', (bool)$request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据，按排序字段排序
            $data = $query->orderBy('sort')
                         ->orderBy('sort_order') 
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort,
                                 'caseType' => $item->case_type,
                                 'businessType' => $item->business_type,
                                 'applyType' => $item->apply_type,
                                 'country' => $item->country,
                                 'feeType' => $item->fee_type,
                                 'feeName' => $item->fee_name,
                                 'feeNameEn' => $item->fee_name_en,
                                 'currency' => $item->currency,
                                 'feeCode' => $item->fee_code,
                                 'baseFee' => $item->base_fee,
                                 'smallEntityFee' => $item->small_entity_fee,
                                 'microEntityFee' => $item->micro_entity_fee,
                                 'role' => $item->role,
                                 'useStage' => $item->use_stage,
                                 'isValid' => (bool)$item->is_valid,
                                 'sortOrder' => $item->sort_order,
                                 'updatedBy' => $item->updater->real_name ?? '',
                                 'updatedAt' => $item->updated_at,
                             ];
                         });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '获取费用配置列表失败：' . $e->getMessage(), [
                'title' => '费用配置管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updater'] = $data['updater'] ?? '系统记录';
            $data['sort'] = $data['sort'] ?? 1;
            $data['currency'] = $data['currency'] ?? 'CNY';
            $data['base_fee'] = $data['base_fee'] ?? 0;
            $data['small_entity_fee'] = $data['small_entity_fee'] ?? 0;
            $data['micro_entity_fee'] = $data['micro_entity_fee'] ?? 0;
            $data['sort_order'] = $data['sort_order'] ?? 0;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;

            // 处理数组字段，过滤掉'all'选项
            if (isset($data['business_type']) && is_array($data['business_type'])) {
                $data['business_type'] = array_values(array_filter($data['business_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['apply_type']) && is_array($data['apply_type'])) {
                $data['apply_type'] = array_values(array_filter($data['apply_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['case_type']) && is_array($data['case_type'])) {
                $data['case_type'] = array_values(array_filter($data['case_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['country']) && is_array($data['country'])) {
                $data['country'] = array_values(array_filter($data['country'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['role']) && is_array($data['role'])) {
                $data['role'] = array_values(array_filter($data['role'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['use_stage']) && is_array($data['use_stage'])) {
                $data['use_stage'] = array_values(array_filter($data['use_stage'], function($item) {
                    return $item !== 'all';
                }));
            }

            $data['created_by'] = auth()->user()->id;
            $data['updated_by'] = auth()->user()->id;

            $item = FeeConfig::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'caseType' => $item->case_type,
                'businessType' => $item->business_type,
                'applyType' => $item->apply_type,
                'country' => $item->country,
                'feeType' => $item->fee_type,
                'feeName' => $item->fee_name,
                'baseFee' => $item->base_fee,
                'isValid' => (bool)$item->is_valid,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '创建费用配置失败：' . $e->getMessage(), [
                'title' => '费用配置管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $item = FeeConfig::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updater'] = $data['updater'] ?? '系统记录';
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

            // 处理数组字段，过滤掉'all'选项
            if (isset($data['business_type']) && is_array($data['business_type'])) {
                $data['business_type'] = array_values(array_filter($data['business_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['apply_type']) && is_array($data['apply_type'])) {
                $data['apply_type'] = array_values(array_filter($data['apply_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['case_type']) && is_array($data['case_type'])) {
                $data['case_type'] = array_values(array_filter($data['case_type'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['country']) && is_array($data['country'])) {
                $data['country'] = array_values(array_filter($data['country'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['role']) && is_array($data['role'])) {
                $data['role'] = array_values(array_filter($data['role'], function($item) {
                    return $item !== 'all';
                }));
            }

            if (isset($data['use_stage']) && is_array($data['use_stage'])) {
                $data['use_stage'] = array_values(array_filter($data['use_stage'], function($item) {
                    return $item !== 'all';
                }));
            }

            unset($data['created_by']);
            $data['updated_by'] = auth()->user()->id;

            $item->update($data);

            return json_success('更新成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'caseType' => $item->case_type,
                'businessType' => $item->business_type,
                'applyType' => $item->apply_type,
                'country' => $item->country,
                'feeType' => $item->fee_type,
                'feeName' => $item->fee_name,
                'baseFee' => $item->base_fee,
                'isValid' => (bool)$item->is_valid,
                'updatedAt' => $item->updated_at,
            ]);

        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '更新费用配置失败：' . $e->getMessage(), [
                'title' => '费用配置管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = FeeConfig::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'caseType' => $item->case_type,
                'businessType' => $item->business_type,
                'applyType' => $item->apply_type,
                'country' => $item->country,
                'feeType' => $item->fee_type,
                'feeName' => $item->fee_name,
                'feeNameEn' => $item->fee_name_en,
                'currency' => $item->currency,
                'feeCode' => $item->fee_code,
                'baseFee' => $item->base_fee,
                'smallEntityFee' => $item->small_entity_fee,
                'microEntityFee' => $item->micro_entity_fee,
                'role' => $item->role,
                'useStage' => $item->use_stage,
                'isValid' => (bool)$item->is_valid,
                'sortOrder' => $item->sort_order,
                'updatedBy' => $item->updater->real_name ?? '',
                'updatedAt' => $item->updated_at,
            ]);

        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '获取费用配置详情失败：' . $e->getMessage(), [
                'title' => '费用配置管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取详情失败');
        }
    }
}