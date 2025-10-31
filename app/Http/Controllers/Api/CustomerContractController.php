<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerContract;
use App\Models\Customer;
use App\Models\BusinessOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 客户合同管理控制器
 * 
 * 负责处理客户合同的增删改查操作，包括合同列表查询、合同创建、
 * 合同详情获取、合同更新和合同删除等功能
 * 
 * @package App\Http\Controllers\Api
 */
class CustomerContractController extends Controller
{
    /**
     * 获取合同列表
     * 
     * 支持多种筛选条件的合同列表查询，包括客户信息、商机信息、
     * 合同基本信息、金额范围、时间范围等筛选条件
     * 
     * @param Request $request 请求对象
     * 
     * 请求参数：
     * @param int $customer_id 客户ID（可选）
     * @param int $business_opportunity_id 商机ID（可选）
     * @param string $customer_name 客户名称（可选，支持模糊搜索）
     * @param string $contract_no 合同编号（可选，支持模糊搜索）
     * @param string $contract_name 合同名称（可选，支持模糊搜索）
     * @param string $contract_type 合同类型（可选）
     * @param string $status 合同状态（可选）
     * @param int $business_person_id 业务负责人ID（可选）
     * @param string $payment_method 付款方式（可选）
     * @param float $amount_min 合同金额最小值（可选）
     * @param float $amount_max 合同金额最大值（可选）
     * @param string $sign_date_start 签约开始日期（可选，格式：Y-m-d）
     * @param string $sign_date_end 签约结束日期（可选，格式：Y-m-d）
     * @param string $start_date_start 合同开始日期范围开始（可选，格式：Y-m-d）
     * @param string $start_date_end 合同开始日期范围结束（可选，格式：Y-m-d）
     * @param string $end_date_start 合同结束日期范围开始（可选，格式：Y-m-d）
     * @param string $end_date_end 合同结束日期范围结束（可选，格式：Y-m-d）
     * @param int $page_size 每页数量（可选，默认10）
     * 
     * 返回参数：
     * @return array success 操作状态（true/false）
     * @return string message 操作消息
     * @return array data 数据对象
     * @return array data.list 合同列表
     * @return int data.list[].id 合同ID
     * @return int data.list[].customer_id 客户ID
     * @return string data.list[].customer_name 客户名称
     * @return string data.list[].customerName 客户名称（驼峰格式）
     * @return int data.list[].business_opportunity_id 商机ID
     * @return int data.list[].businessOpportunityId 商机ID（驼峰格式）
     * @return string data.list[].business_name 商机名称
     * @return string data.list[].businessName 商机名称（驼峰格式）
     * @return string data.list[].contract_no 合同编号
     * @return string data.list[].contractNo 合同编号（驼峰格式）
     * @return string data.list[].contract_name 合同名称
     * @return string data.list[].contractName 合同名称（驼峰格式）
     * @return float data.list[].contract_amount 合同金额
     * @return float data.list[].contractAmount 合同金额（驼峰格式）
     * @return string data.list[].sign_date 签约日期
     * @return string data.list[].signDate 签约日期（驼峰格式）
     * @return string data.list[].start_date 合同开始日期
     * @return string data.list[].startDate 合同开始日期（驼峰格式）
     * @return string data.list[].end_date 合同结束日期
     * @return string data.list[].endDate 合同结束日期（驼峰格式）
     * @return string data.list[].contract_type 合同类型
     * @return string data.list[].contractType 合同类型（驼峰格式）
     * @return string data.list[].status 合同状态
     * @return string data.list[].business_person 业务负责人姓名
     * @return string data.list[].businessPerson 业务负责人姓名（驼峰格式）
     * @return int data.list[].business_person_id 业务负责人ID
     * @return int data.list[].businessPersonId 业务负责人ID（驼峰格式）
     * @return string data.list[].contract_content 合同内容
     * @return string data.list[].contractContent 合同内容（驼峰格式）
     * @return string data.list[].payment_method 付款方式
     * @return string data.list[].paymentMethod 付款方式（驼峰格式）
     * @return float data.list[].paid_amount 已付金额
     * @return float data.list[].paidAmount 已付金额（驼峰格式）
     * @return float data.list[].unpaid_amount 未付金额
     * @return float data.list[].unpaidAmount 未付金额（驼峰格式）
     * @return string data.list[].business_staff 业务员姓名
     * @return string data.list[].businessStaff 业务员姓名（驼峰格式）
     * @return string data.list[].remark 备注
     * @return string data.list[].create_user 创建人姓名
     * @return string data.list[].createUser 创建人姓名（驼峰格式）
     * @return string data.list[].created_at 创建时间
     * @return string data.list[].create_time 创建时间
     * @return string data.list[].createTime 创建时间（驼峰格式）
     * @return string data.list[].update_user 更新人姓名
     * @return string data.list[].updateUser 更新人姓名（驼峰格式）
     * @return string data.list[].updated_at 更新时间
     * @return string data.list[].update_time 更新时间
     * @return string data.list[].updateTime 更新时间（驼峰格式）
     * @return int data.total 总记录数
     * @return int data.per_page 每页数量
     * @return int data.current_page 当前页码
     * @return int data.last_page 最后页码
     */
    public function index(Request $request)
    {
        try {
            // 构建查询，预加载关联模型
            $query = CustomerContract::with([
                'customer',
                'businessOpportunity',
                'businessPerson',
                'creator',
                'updater'
            ]);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id); // 按客户ID筛选
            }

            if ($request->filled('business_opportunity_id')) {
                $query->where('business_opportunity_id', $request->business_opportunity_id); // 按商机ID筛选
            }

            if ($request->filled('customer_name')) {
                // 按客户名称模糊搜索
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%'); // 按合同编号模糊搜索
            }

            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%'); // 按合同名称模糊搜索
            }

            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type); // 按合同类型筛选
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status); // 按合同状态筛选
            }

            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id); // 按业务负责人筛选
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method); // 按付款方式筛选
            }

            // 合同金额筛选
            if ($request->filled('amount_min') && $request->filled('amount_max')) {
                $query->whereBetween('contract_amount', [$request->amount_min, $request->amount_max]);
            }

            // 签约时间筛选
            if ($request->filled('sign_date_start') && $request->filled('sign_date_end')) {
                $query->whereBetween('sign_date', [$request->sign_date_start, $request->sign_date_end]);
            }

            // 合同期间筛选
            if ($request->filled('start_date_start') && $request->filled('start_date_end')) {
                $query->whereBetween('start_date', [$request->start_date_start, $request->start_date_end]);
            }

            if ($request->filled('end_date_start') && $request->filled('end_date_end')) {
                $query->whereBetween('end_date', [$request->end_date_start, $request->end_date_end]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $contracts = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据，提供多种字段格式以兼容前端
            $contracts->getCollection()->transform(function ($contract) {
                return [
                    'id' => $contract->id,
                    'customer_id' => $contract->customer_id,
                    'customer_name' => $contract->customer->customer_name ?? '',
                    'customerName' => $contract->customer->customer_name ?? '',
                    'business_opportunity_id' => $contract->business_opportunity_id,
                    'businessOpportunityId' => $contract->business_opportunity_id,
                    'business_name' => $contract->businessOpportunity->name ?? '',
                    'businessName' => $contract->businessOpportunity->name ?? '',
                    'contract_no' => $contract->contract_no,
                    'contractNo' => $contract->contract_no,
                    'contract_name' => $contract->contract_name,
                    'contractName' => $contract->contract_name,
                    'contract_amount' => $contract->contract_amount,
                    'contractAmount' => $contract->contract_amount,
                    'sign_date' => $contract->sign_date ? $contract->sign_date->format('Y-m-d') : '',
                    'signDate' => $contract->sign_date ? $contract->sign_date->format('Y-m-d') : '',
                    'start_date' => $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                    'startDate' => $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                    'end_date' => $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
                    'endDate' => $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
                    'contract_type' => $contract->contract_type,
                    'contractType' => $contract->contract_type,
                    'status' => $contract->status,
                    'business_person' => $contract->businessPerson->name ?? '',
                    'businessPerson' => $contract->businessPerson->name ?? '',
                    'business_person_id' => $contract->business_person_id,
                    'businessPersonId' => $contract->business_person_id,
                    'contract_content' => $contract->contract_content,
                    'contractContent' => $contract->contract_content,
                    'payment_method' => $contract->payment_method,
                    'paymentMethod' => $contract->payment_method,
                    'paid_amount' => $contract->paid_amount,
                    'paidAmount' => $contract->paid_amount,
                    'unpaid_amount' => $contract->unpaid_amount,
                    'unpaidAmount' => $contract->unpaid_amount,
                    'business_staff' => $contract->businessPerson->name ?? '',
                    'businessStaff' => $contract->businessPerson->name ?? '',
                    'remark' => $contract->remark,
                    'create_user' => $contract->creator->name ?? '',
                    'createUser' => $contract->creator->name ?? '',
                    'created_at' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $contract->updater->name ?? '',
                    'updateUser' => $contract->updater->name ?? '',
                    'updated_at' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $contracts->items(),
                    'total' => $contracts->total(),
                    'per_page' => $contracts->perPage(),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建合同
     * 
     * 创建新的客户合同记录，自动生成合同编号，
     * 计算未付金额，设置创建人信息
     * 
     * @param Request $request 请求对象
     * 
     * 请求参数：
     * @param int $customer_id 客户ID（必填，必须存在于customers表）
     * @param int $business_opportunity_id 商机ID（可选，必须存在于business_opportunities表）
     * @param string $contract_name 合同名称（必填，最大200字符）
     * @param float $contract_amount 合同金额（必填，最小值0）
     * @param string $contract_type 合同类型（必填，最大50字符）
     * @param int $business_person_id 业务负责人ID（必填，必须存在于users表）
     * @param string $sign_date 签约日期（可选，日期格式）
     * @param string $start_date 合同开始日期（可选，日期格式）
     * @param string $end_date 合同结束日期（可选，日期格式）
     * @param string $payment_method 付款方式（可选，最大50字符）
     * @param string $contract_content 合同内容（可选）
     * @param float $paid_amount 已付金额（可选，默认0）
     * @param string $remark 备注（可选）
     * 
     * 返回参数：
     * @return array success 操作状态（true/false）
     * @return string message 操作消息
     * @return object data 创建的合同对象
     * @return int data.id 合同ID
     * @return string data.contract_no 自动生成的合同编号
     * @return float data.unpaid_amount 自动计算的未付金额
     * @return int data.created_by 创建人ID
     * @return int data.updated_by 更新人ID
     */
    public function store(Request $request)
    {
        try {
            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'contract_name' => 'required|string|max:200',
                'contract_amount' => 'required|numeric|min:0',
                'contract_type' => 'required|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'sign_date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'payment_method' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 生成合同编号
            $data['contract_no'] = CustomerContract::generateContractNo();
            
            // 计算未付金额
            $data['unpaid_amount'] = $data['contract_amount'] - ($data['paid_amount'] ?? 0);
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $contract = CustomerContract::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同详情
     * 
     * 根据合同ID获取合同的详细信息，包括关联的客户、
     * 商机、业务负责人等信息
     * 
     * @param int $id 合同ID
     * 
     * 请求参数：
     * @param int $id 合同ID（路径参数，必填）
     * 
     * 返回参数：
     * @return array success 操作状态（true/false）
     * @return string message 操作消息
     * @return object data 合同详情对象
     * @return int data.id 合同ID
     * @return int data.customer_id 客户ID
     * @return object data.customer 客户信息对象
     * @return int data.business_opportunity_id 商机ID
     * @return object data.businessOpportunity 商机信息对象
     * @return string data.contract_no 合同编号
     * @return string data.contract_name 合同名称
     * @return float data.contract_amount 合同金额
     * @return string data.contract_type 合同类型
     * @return string data.status 合同状态
     * @return int data.business_person_id 业务负责人ID
     * @return object data.businessPerson 业务负责人信息对象
     * @return string data.sign_date 签约日期
     * @return string data.start_date 合同开始日期
     * @return string data.end_date 合同结束日期
     * @return string data.contract_content 合同内容
     * @return string data.payment_method 付款方式
     * @return float data.paid_amount 已付金额
     * @return float data.unpaid_amount 未付金额
     * @return string data.remark 备注
     * @return int data.created_by 创建人ID
     * @return object data.creator 创建人信息对象
     * @return int data.updated_by 更新人ID
     * @return object data.updater 更新人信息对象
     * @return string data.created_at 创建时间
     * @return string data.updated_at 更新时间
     */
    public function show($id)
    {
        try {
            // 查找合同并预加载关联模型
            $contract = CustomerContract::with([
                'customer',
                'businessOpportunity',
                'businessPerson',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新合同
     * 
     * 根据合同ID更新合同信息，自动重新计算未付金额，
     * 更新修改人信息
     * 
     * @param Request $request 请求对象
     * @param int $id 合同ID
     * 
     * 请求参数：
     * @param int $id 合同ID（路径参数，必填）
     * @param int $customer_id 客户ID（必填，必须存在于customers表）
     * @param int $business_opportunity_id 商机ID（可选，必须存在于business_opportunities表）
     * @param string $contract_name 合同名称（必填，最大200字符）
     * @param float $contract_amount 合同金额（必填，最小值0）
     * @param string $contract_type 合同类型（必填，最大50字符）
     * @param int $business_person_id 业务负责人ID（必填，必须存在于users表）
     * @param string $sign_date 签约日期（可选，日期格式）
     * @param string $start_date 合同开始日期（可选，日期格式）
     * @param string $end_date 合同结束日期（可选，日期格式）
     * @param string $payment_method 付款方式（可选，最大50字符）
     * @param string $contract_content 合同内容（可选）
     * @param float $paid_amount 已付金额（可选）
     * @param string $status 合同状态（可选）
     * @param string $remark 备注（可选）
     * 
     * 返回参数：
     * @return array success 操作状态（true/false）
     * @return string message 操作消息
     * @return object data 更新后的合同对象
     * @return float data.unpaid_amount 重新计算的未付金额
     * @return int data.updated_by 更新人ID
     * @return string data.updated_at 更新时间
     */
    public function update(Request $request, $id)
    {
        try {
            // 查找要更新的合同
            $contract = CustomerContract::findOrFail($id);

            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'contract_name' => 'required|string|max:200',
                'contract_amount' => 'required|numeric|min:0',
                'contract_type' => 'required|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'sign_date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'payment_method' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 计算未付金额
            $data['unpaid_amount'] = $data['contract_amount'] - ($data['paid_amount'] ?? 0);
            
            // 设置更新人
            $data['updated_by'] = auth()->id();

            $contract->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同
     * 
     * 根据合同ID删除指定的合同记录
     * 
     * @param int $id 合同ID
     * 
     * 请求参数：
     * @param int $id 合同ID（路径参数，必填）
     * 
     * 返回参数：
     * @return array success 操作状态（true/false）
     * @return string message 操作消息
     */
    public function destroy($id)
    {
        try {
            // 查找并删除合同
            $contract = CustomerContract::findOrFail($id);
            $contract->delete();

            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }
}
