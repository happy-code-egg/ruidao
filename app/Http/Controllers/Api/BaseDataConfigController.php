<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 数据配置基础控制器
 */
abstract class BaseDataConfigController extends Controller
{
    /**
     * 获取模型类名
     */
    abstract protected function getModelClass();

    /**
     * 获取验证规则
     */
    abstract protected function getValidationRules($isUpdate = false);

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return [
            'name.required' => '名称不能为空',
            'name.max' => '名称长度不能超过100个字符',
            'code.required' => '编码不能为空',
            'code.unique' => '编码已存在',
            'code.max' => '编码长度不能超过50个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值无效',
            'sort_order.integer' => '排序必须是整数',
        ];
    }

    /**
     * 获取列表
     */
    public function index(Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
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
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取数据配置列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '获取数据配置详情失败');
            return json_fail('获取详情失败');
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

            $modelClass = $this->getModelClass();
            $data = $request->all();
            $data['created_by'] = Auth::user()->id ?? 1;
            $data['updated_by'] = $data['created_by'];
            $item = $modelClass::create($data);

            // 记录操作日志
            // $this->logOperation('create', $item->getTable(), $item->id, $request->all());

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '创建数据配置失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $oldData = $item->toArray();
            // 特定字段不更新
            $data = $request->all();
            unset($data['created_by']);
            $data['updated_by'] = Auth::user()->id ?? 1;
            $item->update($data);

            // 记录操作日志
            // $this->logOperation('update', $item->getTable(), $item->id, $request->all(), $oldData);

            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '更新数据配置失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除
     */
    public function destroy($id)
    {
        try {
            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $oldData = $item->toArray();
            $item->delete();

            // 记录操作日志
            // $this->logOperation('delete', $item->getTable(), $id, [], $oldData);

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除数据配置失败');
            return json_fail('删除失败');
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
                'status' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $modelClass = $this->getModelClass();
            $updated = $modelClass::whereIn('id', $request->ids)
                                 ->update(['status' => $request->status]);

            // 记录操作日志
            // $this->logOperation('batch_update_status', (new $modelClass)->getTable(), $request->ids, $request->all());

            return json_success("批量更新成功，共更新{$updated}条记录");

        } catch (\Exception $e) {
            log_exception($e, '批量更新状态失败');
            return json_fail('批量更新失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::enabled()->ordered();

            $data = $query->select('id', 'name', 'code')->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }

    /**
     * 记录操作日志
     */
    protected function logOperation($action, $table, $recordId, $newData = [], $oldData = [])
    {
        try {
            \App\Models\OperationLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'table_name' => $table,
                'record_id' => is_array($recordId) ? implode(',', $recordId) : $recordId,
                'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('记录操作日志失败: ' . $e->getMessage());
        }
    }
}
