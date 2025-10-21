<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    /**
     * 获取代理机构列表
     */
    public function index(Request $request)
    {
        try {
            $query = Agency::query();

            // 搜索条件
            if ($request->has('agencyName') && !empty(trim($request->agencyName))) {
                $keyword = trim($request->agencyName);
                $query->where(function ($q) use ($keyword) {
                    $q->where('agency_name_cn', 'like', "%{$keyword}%")
                      ->orWhere('agency_name_en', 'like', "%{$keyword}%")
                      ->orWhere('agency_code', 'like', "%{$keyword}%");
                });
            }

            // 国家筛选
            if ($request->has('country') && !empty($request->country)) {
                $query->where('country', $request->country);
            }

            // 有效性筛选
            if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
                $query->where('is_valid', $request->isValid);
            }

            // 分页参数
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 10);

            $total = $query->count();
            $data = $query->orderBy('sort', 'asc')
                         ->orderBy('id', 'asc')
                         ->offset(($page - 1) * $pageSize)
                         ->limit($pageSize)
                         ->get();

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
                'message' => '获取代理机构列表失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 创建代理机构
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'agencyNameCn' => 'required|string|max:200',
            'agencyNameEn' => 'required|string|max:200',
            'country' => 'required|string|max:100',
            'agencyCode' => 'nullable|string|max:50|unique:agencies,agency_code',
            'socialCreditCode' => 'nullable|string|max:100',
            'createTime' => 'nullable|date',
            'agentType' => 'nullable|string|max:100',
            'isValid' => 'nullable|boolean',
            'isSupplier' => 'nullable|boolean'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'agencyNameCn.required' => '代理机构中文名称不能为空',
            'agencyNameEn.required' => '代理机构英文名称不能为空',
            'country.required' => '所属国家不能为空',
            'agencyCode.unique' => '机构代码已存在'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $now = now();

            $agency = Agency::create([
                'sort' => $request->sort,
                'agency_name_cn' => $request->agencyNameCn,
                'agency_name_en' => $request->agencyNameEn,
                'country' => $request->country,
                'social_credit_code' => $request->socialCreditCode,
                'agency_code' => $request->agencyCode,
                'create_time' => $request->createTime,
                'account' => $request->account,
                'password' => $request->password,
                'province' => $request->province,
                'city' => $request->city,
                'province_en' => $request->provinceEn,
                'city_en' => $request->cityEn,
                'address_cn' => $request->addressCn,
                'address_en' => $request->addressEn,
                'postcode' => $request->postcode,
                'manager' => $request->manager,
                'contact' => $request->contact,
                'modifier' => $request->modifier,
                'agent_type' => $request->agentType,
                'is_valid' => $request->get('isValid', true),
                'is_supplier' => $request->get('isSupplier', false),
                'requirements' => $request->requirements,
                'remark' => $request->remark,
                'creator' => $user ? $user->name : '',
                'creation_time' => $now,
                'created_by' => $user ? $user->id : null,
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构创建成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构创建失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取代理机构详情
     */
    public function show($id)
    {
        try {
            $agency = Agency::find($id);
            
            if (!$agency) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理机构不存在'
                ]);
            }

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理机构详情失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 更新代理机构
     */
    public function update(Request $request, $id)
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return response()->json([
                'code' => 404,
                'message' => '代理机构不存在'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'agencyNameCn' => 'required|string|max:200',
            'agencyNameEn' => 'required|string|max:200',
            'country' => 'required|string|max:100',
            'agencyCode' => 'nullable|string|max:50|unique:agencies,agency_code,' . $id,
            'socialCreditCode' => 'nullable|string|max:100',
            'createTime' => 'nullable|date',
            'agentType' => 'nullable|string|max:100',
            'isValid' => 'nullable|boolean',
            'isSupplier' => 'nullable|boolean'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'agencyNameCn.required' => '代理机构中文名称不能为空',
            'agencyNameEn.required' => '代理机构英文名称不能为空',
            'country.required' => '所属国家不能为空',
            'agencyCode.unique' => '机构代码已存在'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $now = now();

            $agency->update([
                'sort' => $request->sort,
                'agency_name_cn' => $request->agencyNameCn,
                'agency_name_en' => $request->agencyNameEn,
                'country' => $request->country,
                'social_credit_code' => $request->socialCreditCode,
                'agency_code' => $request->agencyCode,
                'create_time' => $request->createTime,
                'account' => $request->account,
                'password' => $request->password,
                'province' => $request->province,
                'city' => $request->city,
                'province_en' => $request->provinceEn,
                'city_en' => $request->cityEn,
                'address_cn' => $request->addressCn,
                'address_en' => $request->addressEn,
                'postcode' => $request->postcode,
                'manager' => $request->manager,
                'contact' => $request->contact,
                'modifier' => $user ? $user->name : '',
                'agent_type' => $request->agentType,
                'is_valid' => $request->get('isValid', true),
                'is_supplier' => $request->get('isSupplier', false),
                'requirements' => $request->requirements,
                'remark' => $request->remark,
                'update_time' => $now,
                'updated_by' => $user ? $user->id : null,
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构更新成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构更新失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 删除代理机构
     */
    public function destroy($id)
    {
        try {
            $agency = Agency::find($id);

            if (!$agency) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理机构不存在'
                ]);
            }

            DB::beginTransaction();

            $agencyName = $agency->agency_name_cn;
            $agency->delete();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构删除成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构删除失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取国家选项
     */
    public function getCountries()
    {
        try {
            $countries = [
                ['value' => '中国', 'label' => '中国'],
                ['value' => '美国', 'label' => '美国'],
                ['value' => '日本', 'label' => '日本'],
                ['value' => '韩国', 'label' => '韩国'],
                ['value' => '欧洲', 'label' => '欧洲'],
                ['value' => '英国', 'label' => '英国'],
                ['value' => '德国', 'label' => '德国'],
                ['value' => '法国', 'label' => '法国'],
                ['value' => '加拿大', 'label' => '加拿大'],
                ['value' => '澳大利亚', 'label' => '澳大利亚'],
                ['value' => '新加坡', 'label' => '新加坡'],
                ['value' => '香港', 'label' => '香港'],
                ['value' => '台湾', 'label' => '台湾'],
                ['value' => '澳门', 'label' => '澳门'],
            ];

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取国家列表失败: ' . $e->getMessage()
            ]);
        }
    }
}