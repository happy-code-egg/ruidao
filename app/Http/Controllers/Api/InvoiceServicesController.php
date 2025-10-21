<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InvoiceServicesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return InvoiceService::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'service_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_valid' => 'nullable|in:0,1',
            'sort_order' => 'required|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['service_code'] .= '|unique:invoice_services,service_code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'service_name.required' => '开票服务内容名称不能为空',
            'service_name.max' => '开票服务内容名称长度不能超过200个字符',
            'sort_order.required' => '排序值不能为空',
            'sort_order.integer' => '排序值必须是整数',
            'sort_order.min' => '排序值不能小于0',
        ]);
    }

    /**
     * 重写index方法以支持特定的搜索条件
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = InvoiceService::query();

            // 开票服务内容名称搜索
            if ($request->has('service_name') && !empty($request->service_name)) {
                $query->where('service_name', 'like', '%' . $request->service_name . '%');
            }

            // 是否有效筛选
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'service_name' => $item->service_name,
                                'service_code' => $item->service_code,
                            'description' => $item->description,
                            'is_valid' => $item->is_valid,
                            'sort_order' => $item->sort_order,
                            'created_by' => $item->creator->real_name ?? '',
                            'updated_by' => $item->updater->real_name ?? '',
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
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
            $this->log(
                8,
                "获取开票服务类型列表失败：{$e->getMessage()}",
                [
                    'title' => '开票服务类型列表',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]
            );
            return json_fail('获取列表失败');
        }
    }

    
    /**
     * 创建
     */
    public function store(\Illuminate\Http\Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();

            $data['created_by'] = Auth::user()->id ?? 1;
            $data['updated_by'] = Auth::user()->id ?? 1;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : 1;

            $item = InvoiceService::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'service_name' => $item->service_name,
                'service_code' => $item->service_code,
                'description' => $item->description,
                'is_valid' => $item->is_valid,
                'created_by' => $item->creator->real_name ?? '',
                'updated_by' => $item->updater->real_name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
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
    public function update(\Illuminate\Http\Request $request, $id)
    {
        try {
            $item = InvoiceService::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['created_by'] = $item->created_by ?? 1;
            $data['updated_by'] = Auth::user()->id ?? 1;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

            $item->update($data);

            return json_success('更新成功', [
                'id' => $item->id,
                'service_name' => $item->service_name,
                'service_code' => $item->service_code,
                'description' => $item->description,
                'is_valid' => $item->is_valid,
                'created_by' => $item->creator->real_name ?? '',
                'updated_by' => $item->updater->real_name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);

        } catch (\Exception $e) {
            $this->log(
                8,
                "更新开票服务类型失败：{$e->getMessage()}",
                [
                    'title' => '开票服务类型',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]
            );
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = InvoiceService::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'service_name' => $item->service_name,
                'service_code' => $item->service_code,
                'description' => $item->description,
                'is_valid' => $item->is_valid,
                'created_by' => $item->creator->real_name ?? '',
                'updated_by' => $item->updater->real_name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);

        } catch (\Exception $e) {
            $this->log(
                8,
                "获取开票服务类型详情失败：{$e->getMessage()}",
                [
                    'title' => '开票服务类型',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]
            );
            return json_fail('获取详情失败');
        }
    }
}
