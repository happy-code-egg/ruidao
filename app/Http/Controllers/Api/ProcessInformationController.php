<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProcessInformationController extends Controller
{
    /**
     * 获取处理事项信息列表
     */
    public function index(Request $request)
    {
        try {
            $query = ProcessInformation::query();

            // 搜索条件
            if ($request->filled('case_type')) {
                $query->byCaseType($request->case_type);
            }

            if ($request->filled('business_type')) {
                $query->byBusinessType($request->business_type);
            }

            if ($request->filled('country')) {
                $query->byCountry($request->country);
            }

            if ($request->filled('process_name')) {
                $query->byProcessName($request->process_name);
            }

            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->orderBy('sort', 'asc')->orderBy('id', 'desc');

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->with(['creator:id,real_name', 'updater:id,real_name'])
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'case_type' => $item->case_type,
                                 'business_type' => is_array($item->business_type)
                                      ? $item->business_type
                                      : (empty($item->business_type) ? [] : explode(',', $item->business_type)),
                                 'business_type_text' => is_array($item->business_type)
                                      ? implode(',', $item->business_type)
                                      : $item->business_type,
                                 'application_type' => is_array($item->application_type)
                                      ? $item->application_type
                                      : (empty($item->application_type) ? [] : explode(',', $item->application_type)),
                                 'application_type_text' => is_array($item->application_type)
                                      ? implode(',', $item->application_type)
                                      : $item->application_type,
                                 'country' => is_array($item->country)
                                      ? $item->country
                                      : (empty($item->country) ? [] : explode(',', $item->country)),
                                 'country_text' => is_array($item->country)
                                      ? implode(',', $item->country)
                                      : $item->country,
                                 'process_name' => $item->process_name,
                                 'flow_completed' => $item->flow_completed,
                                 'proposal_inquiry' => $item->proposal_inquiry ? $item->proposal_inquiry : 0,
                                 'data_updater_inquiry' => $item->data_updater_inquiry ? $item->data_updater_inquiry : 0,
                                 'update_case_handler' => $item->update_case_handler ? $item->update_case_handler : 0,
                                 'process_status' => is_array($item->process_status)
                                      ? $item->process_status
                                      : (empty($item->process_status) ? [] : explode(',', $item->process_status)),
                                 'process_status_text' => is_array($item->process_status)
                                      ? implode(',', $item->process_status)
                                      : $item->process_status,
                                 'case_phase' => $item->case_phase ? $item->case_phase : '',
                                 'process_type' => $item->process_type,
                                 'is_case_node' => $item->is_case_node ? $item->is_case_node : 0,
                                 'is_commission' => $item->is_commission ? $item->is_commission : 0,
                                 'is_valid' => $item->is_valid,
                                 'sort_order' => $item->sort_order,
                                 'consultant_contract' => $item->consultant_contract,
                                 'created_by' => $item->creator->real_name ?? '',
                                 'updated_by' => $item->updater->real_name ?? '',
                                 'updated_at' => $item->updated_at,
                                 'id' => $item->id,
                             ];
                         });

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项信息列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建处理事项信息
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'case_type' => 'required|string|max:100',
                'business_type' => 'required|array',
                'application_type' => 'required|array',
                'country' => 'required|array',
                'process_name' => 'required|string|max:200',
                'flow_completed' => 'nullable|in:1,0',
                'proposal_inquiry' => 'required|in:1,0',
                'data_updater_inquiry' => 'required|in:1,0',
                'update_case_handler' => 'required|in:1,0',
                'process_status' => 'required|array',
                'case_phase' => 'nullable|string|max:100',
                'process_type' => 'required|string|max:100',
                'is_case_node' => 'required|in:1,0',
                'is_commission' => 'required|in:1,0',
                'is_valid' => 'required|boolean',
                'sort' => 'integer|min:1',
                'consultant_contract' => 'nullable|string|max:255',
            ], [
                'case_type.required' => '项目类型不能为空',
                'business_type.required' => '业务类型不能为空',
                'application_type.required' => '申请类型不能为空',
                'country.required' => '国家(地区)不能为空',
                'process_name.required' => '处理事项名称不能为空',
                'proposal_inquiry.required' => '提案是否可用不能为空',
                'data_updater_inquiry.required' => '数据更新是否可用不能为空',
                'update_case_handler.required' => '更新项目处理人不能为空',
                'process_status.required' => '处理状态不能为空',
                'process_type.required' => '处理事项类型不能为空',
                'is_case_node.required' => '是否项目节点不能为空',
                'is_commission.required' => '是否提成不能为空',
                'is_valid.required' => '请选择是否有效',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $item = ProcessInformation::create($data);

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '创建处理事项信息失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 获取处理事项信息详情
     */
    public function show($id)
    {
        try {
            $item = ProcessInformation::with(['creator:id,real_name', 'updater:id,real_name'])->find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $result = [
                'id' => $item->id,
                'case_type' => $item->case_type,
                'business_type' => $item->business_type,
                'application_type' => $item->application_type,
                'country' => $item->country,
                'process_name' => $item->process_name,
                'flow_completed' => $item->flow_completed,
                'proposal_inquiry' => $item->proposal_inquiry,
                'data_updater_inquiry' => $item->data_updater_inquiry,
                'update_case_handler' => $item->update_case_handler,
                'process_status' => $item->process_status,
                'case_phase' => $item->case_phase,
                'process_type' => $item->process_type,
                'is_case_node' => $item->is_case_node,
                'is_commission' => $item->is_commission,
                'is_valid' => $item->is_valid,
                'sort' => $item->sort,
                'consultant_contract' => $item->consultant_contract,
                'created_by' => $item->creator ? $item->creator->real_name : '',
                'updated_by' => $item->updater ? $item->updater->real_name : '',
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项信息详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 更新处理事项信息
     */
    public function update(Request $request, $id)
    {
        try {
            $item = ProcessInformation::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'case_type' => 'required|string|max:100',
                'business_type' => 'required|array',
                'application_type' => 'required|array',
                'country' => 'required|array',
                'process_name' => 'required|string|max:200',
                'flow_completed' => 'nullable|in:1,0',
                'proposal_inquiry' => 'required|in:1,0',
                'data_updater_inquiry' => 'required|in:1,0',
                'update_case_handler' => 'required|in:1,0',
                'process_status' => 'required|array',
                'case_phase' => 'nullable|string|max:100',
                'process_type' => 'required|string|max:100',
                'is_case_node' => 'required|in:1,0',
                'is_commission' => 'required|in:1,0',
                'is_valid' => 'required|boolean',
                'sort' => 'integer|min:1',
                'consultant_contract' => 'nullable|string|max:255',
            ], [
                'case_type.required' => '项目类型不能为空',
                'business_type.required' => '业务类型不能为空',
                'application_type.required' => '申请类型不能为空',
                'country.required' => '国家(地区)不能为空',
                'process_name.required' => '处理事项名称不能为空',
                'proposal_inquiry.required' => '提案是否可用不能为空',
                'data_updater_inquiry.required' => '数据更新是否可用不能为空',
                'update_case_handler.required' => '更新项目处理人不能为空',
                'process_status.required' => '处理状态不能为空',
                'process_type.required' => '处理事项类型不能为空',
                'is_case_node.required' => '是否项目节点不能为空',
                'is_commission.required' => '是否提成不能为空',
                'is_valid.required' => '请选择是否有效',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();

            $item->update($data);

            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '更新处理事项信息失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除处理事项信息
     */
    public function destroy($id)
    {
        try {
            $item = ProcessInformation::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除处理事项信息失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     */
    public function options()
    {
        try {
            $data = ProcessInformation::valid()
                                     ->ordered()
                                     ->select('id', 'process_name as name', 'case_type', 'business_type', 'country')
                                     ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }

    /**
     * 根据申请类型获取处理事项列表
     */
    public function getByApplyType(Request $request)
    {
        try {
            $applyType = $request->get('apply_type');
            if (empty($applyType)) {
                return response()->json([
                    'code' => 1,
                    'msg' => '申请类型不能为空',
                    'data' => null
                ]);
            }

            $query = ProcessInformation::valid()->ordered();

            // 根据申请类型筛选，支持模糊匹配
            $query->where(function ($q) use ($applyType) {
                $q->where('case_type', 'like', "%{$applyType}%")
                  ->orWhereJsonContains('application_type', $applyType)
                  ->orWhere('business_type', 'like', "%{$applyType}%");
            });

            $processItems = $query->select('id', 'process_name', 'case_type', 'business_type', 'application_type')
                                 ->get()
                                 ->map(function ($item) {
                                     return [
                                         'id' => $item->id,
                                         'value' => $item->process_name,
                                         'label' => $item->process_name,
                                         'case_type' => $item->case_type,
                                         'business_type' => $item->business_type_text,
                                         'application_type' => $item->application_type_text
                                     ];
                                 });

            return response()->json([
                'code' => 0,
                'msg' => '获取处理事项列表成功',
                'data' => $processItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取处理事项列表失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 获取筛选后的处理事项选项
     */
    public function getFilteredOptions(Request $request)
    {
        try {
            $query = ProcessInformation::valid()->ordered();

            // 按项目类型筛选
            if ($request->filled('case_type')) {
                $query->byCaseType($request->case_type);
            }

            // 按申请类型筛选
            if ($request->filled('application_type')) {
                $applicationType = $request->application_type;
                if (is_array($applicationType)) {
                    $query->where(function ($q) use ($applicationType) {
                        foreach ($applicationType as $type) {
                            $q->orWhereJsonContains('application_type', $type);
                        }
                    });
                } else {
                    $query->whereJsonContains('application_type', $applicationType);
                }
            }

            // 按处理事项类型筛选
            if ($request->filled('process_types')) {
                $processTypes = $request->process_types;
                if (is_array($processTypes)) {
                    $query->whereIn('process_type', $processTypes);
                } else {
                    $query->where('process_type', $processTypes);
                }
            }

            // 按关键词搜索
            if ($request->filled('keyword')) {
                $query->where('process_name', 'like', '%' . $request->keyword . '%');
            }

            $data = $query->select('id', 'process_name', 'process_type', 'case_type', 'business_type', 'application_type', 'country')
                         ->get();

            return json_success('获取筛选选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取筛选选项失败');
            return json_fail('获取筛选选项失败');
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
                'ids.*' => 'integer',
                'is_valid' => 'required|boolean'
            ], [
                'ids.required' => '请选择要更新的记录',
                'ids.array' => 'ids必须是数组',
                'is_valid.required' => '请选择状态',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $updated = ProcessInformation::whereIn('id', $request->ids)
                                        ->update([
                                            'is_valid' => $request->is_valid,
                                            'updated_by' => Auth::id()
                                        ]);

            return json_success("批量更新成功，共更新{$updated}条记录");

        } catch (\Exception $e) {
            log_exception($e, '批量更新状态失败');
            return json_fail('批量更新失败');
        }
    }
}
