<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OurCompanies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OurCompaniesController extends Controller
{
    /**
     * 获取列表
     */
    public function index(Request $request)
    {
        try {
            $query = OurCompanies::query();

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('short_name', 'like', "%{$keyword}%")
                      ->orWhere('full_name', 'like', "%{$keyword}%")
                      ->orWhere('name', 'like', "%{$keyword}%");
                });
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'status' => $item->status,
                                'status_text' => $item->status_text,
                                'sort_order' => $item->sort_order,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                                'created_by' => $item->creator->real_name ?? '未知',
                                'updated_by' => $item->updater->real_name ?? '未知',
                                'name' => $item->name,
                                'code' => $item->code,
                                'short_name' => $item->short_name,
                                'full_name' => $item->full_name,
                                'credit_code' => $item->credit_code,
                                'address' => $item->address,
                                'contact_person' => $item->contact_person,
                                'contact_phone' => $item->contact_phone,
                                'tax_number' => $item->tax_number,
                                'bank' => $item->bank,
                                'account' => $item->account,
                                'invoice_phone' => $item->invoice_phone,
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
                \App\Models\Logs::TYPE_ERROR,
                '获取我方公司列表失败: ' . $e->getMessage(),
                [
                    'title' => '获取我方公司列表',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = OurCompanies::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item);

        } catch (\Exception $e) {
            $this->log(
                \App\Models\Logs::TYPE_ERROR,
                '获取我方公司详情失败: ' . $e->getMessage(),
                [
                    'title' => '获取我方公司详情',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
            return json_fail('获取详情失败');
        }
    }

    /**
     * 创建
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'short_name' => 'required|string|max:100|unique:our_companies,short_name',
                'full_name' => 'required|string|max:200',
                'credit_code' => 'required|string|max:50',
                'address' => 'nullable|string|max:255',
                'bank' => 'nullable|string|max:100',
                'account' => 'nullable|string|max:50',
                'invoice_phone' => 'nullable|string|max:20',
                'status' => 'required|in:0,1',
                'sort_order' => 'nullable|integer|min:0'
            ], [
                'short_name.required' => '我方公司简称不能为空',
                'short_name.unique' => '我方公司简称已存在',
                'full_name.required' => '我方公司全称不能为空',
                'credit_code.required' => '信用代码不能为空',
                'status.required' => '状态不能为空'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['name'] = $request->short_name; // 兼容字段
            $data['code'] = $request->code ?? strtolower(str_replace(' ', '_', $request->short_name));

            $data['created_by'] = auth()->user()->id;
            $data['updated_by'] = auth()->user()->id;

            $item = OurCompanies::create($data);

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            $this->log(
                \App\Models\Logs::TYPE_ERROR,
                '创建我方公司失败: ' . $e->getMessage(),
                [
                    'title' => '创建我方公司',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $item = OurCompanies::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'short_name' => 'required|string|max:100|unique:our_companies,short_name,' . $id,
                'full_name' => 'required|string|max:200',
                'credit_code' => 'required|string|max:50',
                'address' => 'nullable|string|max:255',
                'bank' => 'nullable|string|max:100',
                'account' => 'nullable|string|max:50',
                'invoice_phone' => 'nullable|string|max:20',
                'status' => 'required|in:0,1',
                'sort_order' => 'nullable|integer|min:0'
            ], [
                'short_name.required' => '我方公司简称不能为空',
                'short_name.unique' => '我方公司简称已存在',
                'full_name.required' => '我方公司全称不能为空',
                'credit_code.required' => '信用代码不能为空',
                'status.required' => '状态不能为空'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['name'] = $request->short_name; // 兼容字段
            $data['code'] = $request->code ?? strtolower(str_replace(' ', '_', $request->short_name));

            unset($data['created_by']);
            $data['updated_by'] = auth()->user()->id;

            $item->update($data);

            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            $this->log(
                \App\Models\Logs::TYPE_ERROR,
                '更新我方公司失败: ' . $e->getMessage(),
                [
                    'title' => '更新我方公司',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
            return json_fail('更新失败');
        }
    }

    /**
     * 删除
     */
    public function destroy($id)
    {
        try {
            $item = OurCompanies::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            $this->log(
                \App\Models\Logs::TYPE_ERROR,
                '删除我方公司失败: ' . $e->getMessage(),
                [
                    'title' => '删除我方公司',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
                return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(Request $request)
    {
        try {
            $data = OurCompanies::where('status', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->select('id', 'short_name as label', 'short_name as value')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            $this->log(
                \App\Models\Logs::TYPE_ERROR,
                '获取我方公司选项失败: ' . $e->getMessage(),
                [
                    'title' => '获取我方公司选项',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED 
                ]
            );
            return json_fail('获取选项列表失败');
        }
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'short_name.required' => '公司简称不能为空',
            'short_name.unique' => '公司简称已存在',
            'full_name.required' => '公司全称不能为空',
            'credit_code.required' => '信用代码不能为空',
        ]);
    }
}