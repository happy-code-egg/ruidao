<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 产品设置控制器
 */
class ProductController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return Product::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'product_code' => 'required|string|max:100',
            'project_type' => 'required|string|max:100',
            'apply_type' => 'required|string|max:100',
            'specification' => 'nullable|string|max:200',
            'product_name' => 'required|string|max:200',
            'official_fee' => 'nullable|numeric|min:0',
            'standard_price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'is_valid' => 'nullable|boolean',
            'update_user' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id') ?? request()->route('product');
            $rules['product_code'] .= '|unique:products,product_code,' . $id;
        } else {
            $rules['product_code'] .= '|unique:products,product_code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'product_code.required' => '产品编号不能为空',
            'product_code.unique' => '产品编号已存在',
            'project_type.required' => '项目类型不能为空',
            'apply_type.required' => '申请类型不能为空',
            'product_name.required' => '产品名称不能为空',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
            'official_fee.numeric' => '参考官费必须是数值',
            'official_fee.min' => '参考官费不能小于0',
            'standard_price.numeric' => '标准定价必须是数值',
            'standard_price.min' => '标准定价不能小于0',
            'min_price.numeric' => '最低售价必须是数值',
            'min_price.min' => '最低售价不能小于0',
        ]);
    }

    /**
     * 获取列表 - 重写以支持特定的搜索条件
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // 项目类型搜索
            if ($request->has('case_type') && !empty($request->case_type)) {
                $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
                if (!in_array('all', $caseTypes)) {
                    $query->whereIn('project_type', $caseTypes);
                }
            }

            // 申请类型搜索
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $query->where('apply_type', 'like', "%{$request->apply_type}%");
            }

            // 产品名称搜索
            if ($request->has('product_name') && !empty($request->product_name)) {
                $query->where('product_name', 'like', "%{$request->product_name}%");
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
            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort ?? 1,
                                 'productCode' => $item->product_code,
                                 'projectType' => $item->project_type,
                                 'applyType' => $item->apply_type,
                                 'specification' => $item->specification,
                                 'productName' => $item->product_name,
                                 'officialFee' => $item->official_fee,
                                 'standardPrice' => $item->standard_price,
                                 'minPrice' => $item->min_price,
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
            log_exception($e, '获取产品配置列表失败');
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

            $item = Product::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "创建产品配置失败：{$e->getMessage()}", [
                'title' => '产品配置',
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
            $item = Product::find($id);

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
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            log_exception($e, '更新产品配置失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = Product::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort ?? 1,
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user ?? '系统记录',
                'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取产品配置详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 删除
     */
    public function destroy($id)
    {
        try {
            $item = Product::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除产品配置失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     */
    public function options(Request $request = null)
    {
        try {
            $data = Product::where('is_valid', true)
                          ->orderBy('sort')
                          ->orderBy('id')
                          ->get()
                          ->map(function ($item) {
                              return [
                                  'id' => $item->id,
                                  'value' => $item->id,
                                  'label' => $item->product_name,
                                  'productCode' => $item->product_code,
                                  'projectType' => $item->project_type,
                                  'applyType' => $item->apply_type,
                              ];
                          });

            return json_success('获取选项列表成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取产品选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }
}
