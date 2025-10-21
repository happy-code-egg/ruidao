<?php

namespace App\Http\Controllers\Api;

use App\Models\ApplyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 申请类型配置控制器
 */
class ApplyTypeController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return ApplyType::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'country' => 'required|string|max:100',
            'case_type' => 'required|string|max:100', 
            'apply_type_name' => 'required|string|max:100',
            'apply_type_code' => 'required|string|max:50',
            'is_valid' => 'nullable|boolean',
            'update_user' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id') ?? request()->route('apply_type');
            $rules['apply_type_code'] .= '|unique:apply_types,apply_type_code,' . $id;
        } else {
            $rules['apply_type_code'] .= '|unique:apply_types,apply_type_code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'country.required' => '国家(地区)不能为空',
            'case_type.required' => '项目类型不能为空',
            'apply_type_name.required' => '申请类型名称不能为空',
            'apply_type_code.required' => '申请类型代码不能为空',
            'apply_type_code.unique' => '申请类型代码已存在',
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
            $query = ApplyType::query();

            // 国家(地区)搜索
            if ($request->has('country') && !empty($request->country)) {
                $countries = is_array($request->country) ? $request->country : [$request->country];
                if (!in_array('all', $countries)) {
                    $query->whereIn('country', $countries);
                }
            }

            // 项目类型搜索
            if ($request->has('case_type') && !empty($request->case_type)) {
                $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
                if (!in_array('all', $caseTypes)) {
                    $query->whereIn('case_type', $caseTypes);
                }
            }

            // 申请类型名称搜索
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $query->where('apply_type_name', 'like', "%{$request->apply_type}%");
            }

            // 是否有效搜索
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据，按排序字段排序
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort ?? 1,
                                 'country' => $item->country,
                                 'caseType' => $item->case_type,
                                 'applyTypeName' => $item->apply_type_name,
                                 'applyTypeCode' => $item->apply_type_code,
                                 'isValid' => (bool)$item->is_valid,
                                 'updateUser' => $item->update_user ?? '系统记录',
                                 'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
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
            log_exception($e, '获取申请类型配置列表失败');
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
            $data['update_user'] = $data['update_user'] ?? '系统记录';
            $data['sort'] = $data['sort'] ?? 1;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;

            $item = ApplyType::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "创建申请类型配置失败：{$e->getMessage()}", [
                'title' => '申请类型配置',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
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
            $item = ApplyType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['update_user'] = $data['update_user'] ?? '系统记录';
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

            $item->update($data);

            return json_success('更新成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            log_exception($e, '更新申请类型配置失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = ApplyType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort ?? 1,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user ?? '系统记录',
                'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取申请类型配置详情失败');
            return json_fail('获取详情失败');
        }
    }


    public function all($caseType)
    {
        $data = ApplyType::where('case_type', $caseType)->get();
        $data = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'value' => $item->apply_type_name,
                'label' => $item->apply_type_name,
            ];
        });
        return json_success('获取所有申请类型配置成功', $data);
    }

    public function allByCountry($country)
    {
        $data = ApplyType::where('country', $country)->get();
        $data = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'value' => $item->apply_type_name,
                'label' => $item->apply_type_name,
            ];
        });
        return json_success('获取所有申请类型配置成功', $data);
    }
}

