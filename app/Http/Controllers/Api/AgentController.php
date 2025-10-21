<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    /**
     * 获取代理师列表
     */
    public function index(Request $request)
    {
        try {
            $query = Agent::query();

            // 搜索条件 - 支持前端的搜索参数
            if ($request->has('name') && !empty(trim($request->name))) {
                $keyword = trim($request->name);
                $query->where(function ($q) use ($keyword) {
                    $q->where('name_cn', 'like', "%{$keyword}%")
                      ->orWhere('name_en', 'like', "%{$keyword}%");
                });
            }

            // 执业证号搜索
            if ($request->has('licenseNumber') && !empty(trim($request->licenseNumber))) {
                $query->where('license_number', 'like', "%" . trim($request->licenseNumber) . "%");
            }

            // 所属机构搜索
            if ($request->has('agency') && !empty(trim($request->agency))) {
                $query->where('agency', 'like', "%" . trim($request->agency) . "%");
            }

            // 分页参数
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 10);

            $total = $query->count();
            $data = $query->orderBy('sort', 'asc')
                         ->orderBy('id', 'asc')
                         ->offset(($page - 1) * $pageSize)
                         ->limit($pageSize)
                         ->get()
                         ->map(function ($agent) {
                             return [
                                 'id' => $agent->id,
                                 'sort' => $agent->sort,
                                 'nameCn' => $agent->name_cn,
                                 'nameEn' => $agent->name_en,
                                 'lastNameCn' => $agent->last_name_cn,
                                 'firstNameCn' => $agent->first_name_cn,
                                 'lastNameEn' => $agent->last_name_en,
                                 'firstNameEn' => $agent->first_name_en,
                                 'licenseNumber' => $agent->license_number,
                                 'qualificationNumber' => $agent->qualification_number,
                                 'licenseDate' => $agent->license_date,
                                 'phone' => $agent->phone,
                                 'email' => $agent->email,
                                 'agency' => $agent->agency,
                                 'gender' => $agent->gender,
                                 'licenseExpiry' => $agent->license_expiry,
                                 'specialty' => $agent->specialty,
                                 'isDefaultAgent' => $agent->is_default_agent,
                                 'isValid' => $agent->is_valid,
                                 'creditRating' => $agent->credit_rating,
                                 'creator' => $agent->creator,
                                 'creationTime' => $agent->creation_time,
                                 'modifier' => $agent->modifier,
                                 'updateTime' => $agent->update_time
                             ];
                         });

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => [
                    'list' => $data,
                    'total' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理师列表失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 创建代理师
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'nameCn' => 'required|string|max:100',
            'nameEn' => 'nullable|string|max:100',
            'lastNameCn' => 'nullable|string|max:50',
            'firstNameCn' => 'nullable|string|max:50',
            'lastNameEn' => 'nullable|string|max:50',
            'firstNameEn' => 'nullable|string|max:50',
            'licenseNumber' => 'required|string|max:100|unique:agents,license_number',
            'qualificationNumber' => 'required|string|max:100|unique:agents,qualification_number',
            'licenseDate' => 'nullable|date',
            'agency' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'licenseExpiry' => 'nullable|date',
            'specialty' => 'nullable|string|max:255',
            'isDefaultAgent' => 'nullable|boolean',
            'isValid' => 'nullable|boolean',
            'creditRating' => 'nullable|string|max:50'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'nameCn.required' => '代理师中文姓名不能为空',
            'licenseNumber.required' => '执业证号不能为空',
            'licenseNumber.unique' => '执业证号已存在',
            'qualificationNumber.required' => '资格证号不能为空',
            'qualificationNumber.unique' => '资格证号已存在',
            'agency.required' => '请输入所属机构',
            'email.email' => '邮箱格式不正确',
            'gender.in' => '请选择正确的性别'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $agent = Agent::create([
                'sort' => $request->sort,
                'name_cn' => $request->nameCn,
                'name_en' => $request->nameEn,
                'last_name_cn' => $request->lastNameCn,
                'first_name_cn' => $request->firstNameCn,
                'last_name_en' => $request->lastNameEn,
                'first_name_en' => $request->firstNameEn,
                'license_number' => $request->licenseNumber,
                'qualification_number' => $request->qualificationNumber,
                'license_date' => $request->licenseDate,
                'agency' => $request->agency,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->get('gender', '男'),
                'license_expiry' => $request->licenseExpiry,
                'specialty' => $request->specialty,
                'is_default_agent' => $request->get('isDefaultAgent', false),
                'is_valid' => $request->get('isValid', true),
                'credit_rating' => $request->creditRating,
                'status' => 1,
                'creator' => auth()->user()->name ?? 'system',
                'creation_time' => now(),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理师创建成功',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理师创建失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取代理师详情
     */
    public function show($id)
    {
        try {
            $agent = Agent::find($id);
            
            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $data = [
                'id' => $agent->id,
                'sort' => $agent->sort,
                'nameCn' => $agent->name_cn,
                'nameEn' => $agent->name_en,
                'lastNameCn' => $agent->last_name_cn,
                'firstNameCn' => $agent->first_name_cn,
                'lastNameEn' => $agent->last_name_en,
                'firstNameEn' => $agent->first_name_en,
                'licenseNumber' => $agent->license_number,
                'qualificationNumber' => $agent->qualification_number,
                'licenseDate' => $agent->license_date,
                'phone' => $agent->phone,
                'email' => $agent->email,
                'agency' => $agent->agency,
                'gender' => $agent->gender,
                'licenseExpiry' => $agent->license_expiry,
                'specialty' => $agent->specialty,
                'isDefaultAgent' => $agent->is_default_agent,
                'isValid' => $agent->is_valid,
                'creditRating' => $agent->credit_rating,
                'creator' => $agent->creator,
                'creationTime' => $agent->creation_time,
                'modifier' => $agent->modifier,
                'updateTime' => $agent->update_time
            ];

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理师详情失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 更新代理师
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'nameCn' => 'required|string|max:100',
            'nameEn' => 'nullable|string|max:100',
            'lastNameCn' => 'nullable|string|max:50',
            'firstNameCn' => 'nullable|string|max:50',
            'lastNameEn' => 'nullable|string|max:50',
            'firstNameEn' => 'nullable|string|max:50',
            'licenseNumber' => 'required|string|max:100|unique:agents,license_number,' . $id,
            'qualificationNumber' => 'required|string|max:100|unique:agents,qualification_number,' . $id,
            'licenseDate' => 'nullable|date',
            'agency' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'licenseExpiry' => 'nullable|date',
            'specialty' => 'nullable|string|max:255',
            'isDefaultAgent' => 'nullable|boolean',
            'isValid' => 'nullable|boolean',
            'creditRating' => 'nullable|string|max:50'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'nameCn.required' => '代理师中文姓名不能为空',
            'licenseNumber.required' => '执业证号不能为空',
            'licenseNumber.unique' => '执业证号已存在',
            'qualificationNumber.required' => '资格证号不能为空',
            'qualificationNumber.unique' => '资格证号已存在',
            'agency.required' => '请输入所属机构',
            'email.email' => '邮箱格式不正确',
            'gender.in' => '请选择正确的性别'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();
            
            $agent = Agent::find($id);
            
            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $agent->update([
                'sort' => $request->sort,
                'name_cn' => $request->nameCn,
                'name_en' => $request->nameEn,
                'last_name_cn' => $request->lastNameCn,
                'first_name_cn' => $request->firstNameCn,
                'last_name_en' => $request->lastNameEn,
                'first_name_en' => $request->firstNameEn,
                'license_number' => $request->licenseNumber,
                'qualification_number' => $request->qualificationNumber,
                'license_date' => $request->licenseDate,
                'agency' => $request->agency,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->get('gender', '男'),
                'license_expiry' => $request->licenseExpiry,
                'specialty' => $request->specialty,
                'is_default_agent' => $request->get('isDefaultAgent', false),
                'is_valid' => $request->get('isValid', true),
                'credit_rating' => $request->creditRating,
                'modifier' => auth()->user()->name ?? 'system',
                'update_time' => now(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '更新成功',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '更新代理师失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 删除代理师
     */
    public function destroy($id)
    {
        try {
            $agent = Agent::find($id);
            
            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $agent->delete();

            return response()->json([
                'code' => 200,
                'message' => '删除成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '删除代理师失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取代理机构选项
     */
    public function getAgencies()
    {
        try {
            $agencies = Agency::where('is_valid', true)
                            ->select('id as value', 'agency_name_cn as label')
                            ->orderBy('sort', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $agencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理机构列表失败: ' . $e->getMessage()
            ]);
        }
    }
}
