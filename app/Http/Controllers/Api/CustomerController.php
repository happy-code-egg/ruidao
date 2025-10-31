<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSort;
use App\Models\User;
use App\Models\CustomerLevel;
use App\Models\CustomerScale;
use App\Models\ParkConfig;
use App\Models\InnovationIndices;
use App\Models\PriceIndices;
use App\Models\ContractCaseRecord;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


/**
 * 客户管理控制器
 * 
 * 负责处理客户信息的增删改查、导入导出、业务员分配等功能
 * 支持多种筛选条件和分页查询
 * 
 * @author 系统
 * @version 1.0
 */
class CustomerController extends Controller
{
    /**
     * 获取客户列表
     * 
     * 支持多种筛选条件的客户列表查询，包括基本信息、业务员、地址、企业认证等
     * 
     * @param Request $request 请求对象
     * 
     * 请求参数：
     * @param string $name 客户名称（模糊搜索）
     * @param string $credit_code 信用代码（模糊搜索）
     * @param string $customer_type 客户类型
     * @param int $customer_level 客户等级ID
     * @param int $level 客户等级ID（别名）
     * @param int $customer_scale 客户规模ID
     * @param int $innovation_index 创新指数ID
     * @param int $price_index 价格指数ID
     * @param string $industrial_park 产业园区名称（模糊搜索）
     * @param string $industry 行业（模糊搜索）
     * @param int|array $business_person_id 业务员ID（支持数组）
     * @param int|array $business_person 业务员ID（别名，支持数组）
     * @param int $business_assistant_id 业务助理ID
     * @param int $business_assistant 业务助理ID（别名）
     * @param int $business_partner_id 业务合作伙伴ID
     * @param int $business_partner 业务合作伙伴ID（别名）
     * @param int $company_manager_id 公司经理ID
     * @param int $company_manager 公司经理ID（别名）
     * @param int $process_staff_id 流程专员ID
     * @param int $process_staff 流程专员ID（别名）
     * @param int $creator_id 创建人ID
     * @param int $creator 创建人ID（别名）
     * @param string $customer_status 客户状态
     * @param string $province 省份
     * @param string $city 城市
     * @param string $district 区县
     * @param int $contract_count1 合同数量最小值
     * @param int $contract_count2 合同数量最大值
     * @param int $case_count1 案件数量最小值
     * @param int $case_count2 案件数量最大值
     * @param string $customer_no 客户编号（模糊搜索）
     * @param string $customer_code 客户代码（模糊搜索）
     * @param string $remark 备注（模糊搜索）
     * @param string $remarks 备注（别名，模糊搜索）
     * @param int|string $park_id 园区ID或名称
     * @param string $economic_category 国民经济行业分类
     * @param string $economic_door 国民经济门类（模糊搜索）
     * @param string $economic_big_class 国民经济大类（模糊搜索）
     * @param string $economic_mid_class 国民经济中类（模糊搜索）
     * @param string $economic_small_class 国民经济小类（模糊搜索）
     * @param string $high_tech_enterprise 高新技术企业（1/0）
     * @param string $province_enterprise 省级企业（1/0）
     * @param string $city_enterprise 市级企业（1/0）
     * @param string $province_tech_center 省级技术中心（1/0）
     * @param string $ip_standard IP标准（1/0）
     * @param string $it_standard IT标准（1/0）
     * @param string $type 客户类型筛选（my/dept/public/all）
     * @param bool $my_customers_only 是否只查看我的客户
     * @param string $tab_type 标签类型（myCustomer/assistCustomer/assistantCustomer）
     * @param bool $department_filter 是否按部门筛选
     * @param string $start_date 开始日期
     * @param string $end_date 结束日期
     * @param string $latest_contract_date_start 最新合同开始日期
     * @param string $latest_contract_date_end 最新合同结束日期
     * @param string $create_date_start 创建开始日期
     * @param string $create_date_end 创建结束日期
     * @param string $update_date_start 更新开始日期
     * @param string $update_date_end 更新结束日期
     * @param int $page_size 每页数量（默认10）
     * @param int $page 页码（默认1）
     * 
     * 返回参数：
     * @return \Illuminate\Http\JsonResponse {
     *     @type bool $success 操作是否成功
     *     @type string $message 返回消息
     *     @type array $data {
     *         @type array $list 客户列表数组
     *         @type int $total 总记录数
     *         @type int $per_page 每页数量
     *         @type int $current_page 当前页码
     *         @type int $last_page 最后页码
     *     }
     * }
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::with([
                'contacts', 
                'businessPerson', 
                'businessAssistant', 
                'businessPartner', 
                'companyManager',
                'processStaff',
                'creator',
                'updater'
            ]);

            // 搜索条件
            if ($request->filled('name')) {
                $query->where('customer_name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('credit_code')) {
                $query->where('credit_code', 'like', '%' . $request->credit_code . '%');
            }

            if ($request->filled('customer_type')) {
                $query->where('customer_type', $request->customer_type);
            }

            if ($request->filled('customer_level') || $request->filled('level')) {
                $level = $request->customer_level ?: $request->level;
                $query->where('customer_level', $level);
            }

            if ($request->filled('customer_scale')) {
                $query->where('customer_scale', $request->customer_scale);
            }

            if ($request->filled('innovation_index')) {
                $query->where('innovation_index', $request->innovation_index);
            }

            if ($request->filled('price_index')) {
                $query->where('price_index', $request->price_index);
            }

            if ($request->filled('industrial_park')) {
                $query->where('industrial_park', 'like', '%' . $request->industrial_park . '%');
            }

            if ($request->filled('customer_scale')) {
                $query->where('customer_scale', $request->customer_scale);
            }

            if ($request->filled('industry')) {
                $query->where('industry', 'like', '%' . $request->industry . '%');
            }

            if ($request->filled('business_person_id') || $request->filled('business_person')) {
                $businessPersonIds = $request->business_person_id ?: $request->business_person;
                if (is_array($businessPersonIds)) {
                    // 支持多业务员搜索：既搜索传统字段，也搜索 customer_related_persons 表
                    $query->where(function($q) use ($businessPersonIds) {
                        $q->whereIn('business_person_id', $businessPersonIds)
                          ->orWhereHas('relatedPersons', function($subQ) use ($businessPersonIds) {
                              $subQ->where('person_type', '业务员')
                                   ->whereIn('related_business_person_id', $businessPersonIds);
                          });
                    });
                } else {
                    // 单个业务员搜索
                    $query->where(function($q) use ($businessPersonIds) {
                        $q->where('business_person_id', $businessPersonIds)
                          ->orWhereHas('relatedPersons', function($subQ) use ($businessPersonIds) {
                              $subQ->where('person_type', '业务员')
                                   ->where('related_business_person_id', $businessPersonIds);
                          });
                    });
                }
            }

            if ($request->filled('business_assistant_id') || $request->filled('business_assistant')) {
                $assistantId = $request->business_assistant_id ?: $request->business_assistant;
                $query->where('business_assistant_id', $assistantId);
            }

            if ($request->filled('business_partner_id') || $request->filled('business_partner')) {
                $partnerId = $request->business_partner_id ?: $request->business_partner;
                $query->where('business_partner_id', $partnerId);
            }

            if ($request->filled('company_manager_id') || $request->filled('company_manager')) {
                $managerId = $request->company_manager_id ?: $request->company_manager;
                $query->where('company_manager_id', $managerId);
            }

            if ($request->filled('process_staff_id') || $request->filled('process_staff')) {
                $staffId = $request->process_staff_id ?: $request->process_staff;
                $query->where('process_staff_id', $staffId);
            }

            if ($request->filled('creator_id') || $request->filled('creator')) {
                $creatorId = $request->creator_id ?: $request->creator;
                $query->where('created_by', $creatorId);
            }

            if ($request->filled('customer_status')) {
                $query->where('customer_status', $request->customer_status);
            }

            // 地址信息查询
            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }

            if ($request->filled('district')) {
                $query->where('district', $request->district);
            }

            // 数量范围查询
            if ($request->filled('contract_count1') || $request->filled('contract_count2')) {
                if ($request->filled('contract_count1')) {
                    $query->where('contract_count', '>=', $request->contract_count1);
                }
                if ($request->filled('contract_count2')) {
                    $query->where('contract_count', '<=', $request->contract_count2);
                }
            }

            if ($request->filled('case_count1') || $request->filled('case_count2')) {
                if ($request->filled('case_count1')) {
                    $query->where('case_count', '>=', $request->case_count1);
                }
                if ($request->filled('case_count2')) {
                    $query->where('case_count', '<=', $request->case_count2);
                }
            }

            // 客户编号查询
            if ($request->filled('customer_no')) {
                $query->where('customer_no', 'like', '%' . $request->customer_no . '%');
            }

            if ($request->filled('customer_code')) {
                $query->where('customer_code', 'like', '%' . $request->customer_code . '%');
            }

            // 备注查询
            if ($request->filled('remark') || $request->filled('remarks')) {
                $remark = $request->remark ?: $request->remarks;
                $query->where('remarks', 'like', '%' . $remark . '%');
            }

            // 产业园区查询
            if ($request->filled('industrial_park') || $request->filled('park_id')) {
                $parkValue = $request->industrial_park ?: $request->park_id;
                if (is_numeric($parkValue)) {
                    $query->where('park_id', $parkValue);
                } else {
                    // 如果传入的是园区名称，需要关联park表查询
                    $query->whereHas('park', function($q) use ($parkValue) {
                        $q->where('name', 'like', '%' . $parkValue . '%');
                    });
                }
            }

            // 国民经济行业分类查询
            if ($request->filled('economic_category')) {
                $query->where('economic_category', $request->economic_category);
            }

            if ($request->filled('economic_door')) {
                $query->where('economic_door', 'like', '%' . $request->economic_door . '%');
            }

            if ($request->filled('economic_big_class')) {
                $query->where('economic_big_class', 'like', '%' . $request->economic_big_class . '%');
            }

            if ($request->filled('economic_mid_class')) {
                $query->where('economic_mid_class', 'like', '%' . $request->economic_mid_class . '%');
            }

            if ($request->filled('economic_small_class')) {
                $query->where('economic_small_class', 'like', '%' . $request->economic_small_class . '%');
            }

            // 企业认证状态查询
            if ($request->filled('high_tech_enterprise')) {
                $query->where('high_tech_enterprise', $request->high_tech_enterprise == '1' ? 1 : 0);
            }

            if ($request->filled('province_enterprise')) {
                $query->where('province_enterprise', $request->province_enterprise == '1' ? 1 : 0);
            }

            if ($request->filled('city_enterprise')) {
                $query->where('city_enterprise', $request->city_enterprise == '1' ? 1 : 0);
            }

            if ($request->filled('province_tech_center')) {
                $query->where('province_tech_center', $request->province_tech_center == '1' ? 1 : 0);
            }

            if ($request->filled('ip_standard')) {
                $query->where('ip_standard', $request->ip_standard == '1' ? 1 : 0);
            }

            if ($request->filled('it_standard')) {
                $query->where('it_standard', $request->it_standard == '1' ? 1 : 0);
            }

            // 指数查询
            if ($request->filled('innovation_index')) {
                $query->where('innovation_index', $request->innovation_index);
            }

            if ($request->filled('price_index')) {
                $query->where('price_index', $request->price_index);
            }

            // 根据类型筛选（我的客户、部门客户、公海客户等）
            $type = $request->get('type', 'all');
            $currentUserId = auth()->id();
            
            // 特殊处理：我的客户页面的不同标签
            if ($request->filled('my_customers_only') && $request->my_customers_only) {
                $tabType = $request->get('tab_type', 'myCustomer');
                switch ($tabType) {
                    case 'myCustomer':
                        // 我的客户：作为业务人员的客户
                        $query->where('business_person_id', $currentUserId);
                        break;
                    case 'assistCustomer':
                        // 协作客户：作为协作人的客户
                        $query->where('business_partner_id', $currentUserId);
                        break;
                    case 'assistantCustomer':
                        // 助理客户：作为助理的客户
                        $query->where('business_assistant_id', $currentUserId);
                        break;
                }
            }
            // 部门客户页面
            elseif ($request->filled('department_filter') && $request->department_filter) {
                // 获取当前用户部门的所有用户的客户
                // 这里假设用户表有department_id字段，需要根据实际情况调整
                $userDeptIds = User::where('id', $currentUserId)->pluck('department_id')->toArray();
                if (!empty($userDeptIds)) {
                    $deptUserIds = User::whereIn('department_id', $userDeptIds)->pluck('id')->toArray();
                    $query->where(function($q) use ($deptUserIds) {
                        $q->whereIn('business_person_id', $deptUserIds)
                          ->orWhereIn('business_assistant_id', $deptUserIds)
                          ->orWhereIn('business_partner_id', $deptUserIds);
                    });
                }
            }
            // 传统的类型筛选
            else {
                switch ($type) {
                    case 'my':
                        $query->where('business_person_id', $currentUserId);
                        break;
                    case 'dept':
                        // 获取当前用户部门的客户（这里需要根据实际用户部门逻辑调整）
                        $userDeptIds = User::where('id', $currentUserId)->pluck('department_id')->toArray();
                        if (!empty($userDeptIds)) {
                            $deptUserIds = User::whereIn('department_id', $userDeptIds)->pluck('id')->toArray();
                            $query->whereIn('business_person_id', $deptUserIds);
                        }
                        break;
                    case 'public':
                        $query->whereNull('business_person_id');
                        break;
                    case 'all':
                    default:
                        // 显示所有客户
                        break;
                }
            }

            // 日期范围筛选
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
            }

            // 最新合同日期范围查询
            if ($request->filled('latest_contract_date_start') && $request->filled('latest_contract_date_end')) {
                $query->whereBetween('latest_contract_date', [$request->latest_contract_date_start, $request->latest_contract_date_end]);
            }

            // 创建日期范围查询
            if ($request->filled('create_date_start') && $request->filled('create_date_end')) {
                $query->whereBetween('created_at', [$request->create_date_start, $request->create_date_end . ' 23:59:59']);
            }

            // 更新日期范围查询
            if ($request->filled('update_date_start') && $request->filled('update_date_end')) {
                $query->whereBetween('updated_at', [$request->update_date_start, $request->update_date_end . ' 23:59:59']);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $page = $request->get('page', 1);

            // 确保加载关联数据，包括业务员列表
            $customers = $query->with(['customerLevel', 'customerScale', 'businessPersons'])
                ->orderBy('id', 'desc')
                ->paginate($pageSize, ['*'], 'page', $page);



            // 格式化数据
            $customers->getCollection()->transform(function ($customer) {
                return [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'customer_name' => $customer->customer_name,
                    'name' => $customer->customer_name,
                    'credit_code' => $customer->credit_code,
                    'creditCode' => $customer->credit_code,
                    'customer_type' => $customer->customer_type,
                    'customer_level' => $customer->customer_level,
                    'level' => $this->getCustomerLevelName($customer),
                    'customer_scale' => $customer->customer_scale,
                    'customerScale' => $this->getCustomerScaleName($customer),
                    'industry' => $customer->industry,
                    'contact_phone' => $customer->contact_phone,
                    'contact_email' => $customer->contact_email,
                    'business_person' => $this->getBusinessPersonsDisplay($customer),
                    'businessPerson' => $this->getBusinessPersonsDisplay($customer),
                    'business_assistant' => $customer->businessAssistant->real_name ?? '',
                    'businessAssistant' => $customer->businessAssistant->real_name ?? '',
                    'business_partner' => $customer->businessPartner->real_name ?? '',
                    'businessPartner' => $customer->businessPartner->real_name ?? '',
                    'company_manager' => $customer->companyManager->real_name ?? '',
                    'companyManager' => $customer->companyManager->real_name ?? '',
                    'process_staff' => $customer->processStaff->real_name ?? '',
                    'processStaff' => $customer->processStaff->real_name ?? '',
                    'customer_status' => $customer->customer_status,
                    'latest_contract_date' => $customer->latest_contract_date,
                    'latestContractDate' => $customer->latest_contract_date,
                    'contract_count' => $customer->contract_count,
                    'contractCount' => $customer->contract_count,
                    'total_amount' => $customer->total_amount,
                    'creator' => $customer->creator->real_name ?? '',
                    'created_at' => $customer->created_at ?? '',
                    'createTime' => $customer->created_at ?? '',
                    'updater' => $customer->updater->real_name ?? '',
                    'updated_at' => $customer->updated_at ?? '',
                    'updateTime' => $customer->updated_at ?? '',
                    // 新增字段
                    'province' => $customer->province,
                    'city' => $customer->city,
                    'district' => $customer->district,
                    'case_count' => $customer->case_count,
                    'caseCount' => $customer->case_count,
                    'customer_no' => $customer->customer_no,
                    'customerNo' => $customer->customer_no,
                    'customerCode' => $customer->customer_code,
                    'remark' => $customer->remarks,
                    'industrialPark' => $customer->park->park_name ?? ($customer->industrial_park ?? ''),
                    'economicCategory' => $customer->economic_category,
                    'economicDoor' => $customer->economic_door,
                    'economicBigClass' => $customer->economic_big_class,
                    'economicMidClass' => $customer->economic_mid_class,
                    'economicSmallClass' => $customer->economic_small_class,
                    'sales2021' => $customer->sales_2021,
                    'researchFee2021' => $customer->research_fee_2021,
                    'loan2021' => $customer->loan_2021,
                    'highTechEnterprise' => $customer->high_tech_enterprise ? '1' : '0',
                    'provinceEnterprise' => $customer->province_enterprise ? '1' : '0',
                    'cityEnterprise' => $customer->city_enterprise ? '1' : '0',
                    'provinceTechCenter' => $customer->province_tech_center ? '1' : '0',
                    'ipStandard' => $customer->ip_standard ? '1' : '0',
                    'itStandard' => $customer->it_standard ? '1' : '0',
                    'innovationIndex' => $customer->innovation_index,
                    'priceIndex' => $customer->price_index,
                    'primary_contact' => $customer->primaryContact ? [
                        'name' => $customer->primaryContact->contact_name,
                        'phone' => $customer->primaryContact->phone,
                        'email' => $customer->primaryContact->email,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $customers->items(),
                    'total' => $customers->total(),
                    'per_page' => $customers->perPage(),
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
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
     * 创建客户
     * 
     * 创建新的客户记录，支持完整的客户信息录入
     * 
     * @param Request $request 请求对象
     * 
     * 请求参数：
     * @param string $name 客户名称（必填）
     * @param string $customer_name 客户名称（可选）
     * @param string $name_en 英文名称
     * @param string $customer_code 客户编号（系统自动生成）
     * @param string $credit_code 信用代码（必填，唯一）
     * @param string $legal_representative 法定代表人
     * @param string $company_manager 公司经理
     * @param int $level 客户等级
     * @param string $employee_count 员工数量
     * @param int $business_person 业务员ID
     * @param string $business_assistant 业务助理
     * @param string $business_partner 业务合作伙伴
     * @param int $price_index 价格指数ID
     * @param int $innovation_index 创新指数ID
     * @param string $contract_count 合同数量
     * @param string $latest_contract_date 最新合同日期
     * @param string $creator 创建人
     * @param string $create_date 创建日期
     * @param string $updater 更新人
     * @param string $update_time 更新时间
     * @param string $remark 备注
     * @param string $contact_name 联系人姓名
     * @param string $contactName 联系人姓名（驼峰命名）
     * @param string $contact_phone 联系电话
     * @param string $contactPhone 联系电话（驼峰命名）
     * @param string $email 邮箱地址
     * @param string $qq QQ号码
     * @param string $wechat 微信号
     * @param string $country 国家
     * @param string $province 省份
     * @param string $city 城市
     * @param string $district 区县
     * @param string $address 详细地址
     * @param string $address_en 英文地址
     * @param string $other_address 其他地址
     * @param string $industrial_park 产业园区
     * @param string $zip_code 邮政编码
     * @param string $website 网站地址
     * @param string $account_name 账户名称
     * @param string $bank_name 银行名称
     * @param string $bank_account 银行账号
     * @param string $invoice_address 发票地址
     * @param string $invoice_phone 发票电话
     * @param bool $is_general_taxpayer 是否一般纳税人
     * @param string $billing_address 账单地址
     * @param string $company_type 公司类型
     * @param string $registered_capital 注册资本
     * @param string $founding_date 成立日期
     * @param string $industry 行业
     * @param string $main_products 主要产品
     * @param string $business_scope 经营范围
     * @param string $economic_category 国民经济行业分类
     * @param string $economic_door 国民经济门类
     * @param string $economic_big_class 国民经济大类
     * @param string $economic_mid_class 国民经济中类
     * @param string $economic_small_class 国民经济小类
     * @param string $research_staff_count 研发人员数量
     * @param string $doctor_count 博士数量
     * @param string $senior_engineer_count 高级工程师数量
     * @param string $master_count 硕士数量
     * @param string $middle_engineer_count 中级工程师数量
     * 
     * 返回参数：
     * @return \Illuminate\Http\JsonResponse {
     *     @type bool $success 操作是否成功
     *     @type string $message 返回消息
     *     @type array $data {
     *         @type int $id 新创建的客户ID
     *         @type string $customer_code 客户编号
     *         @type string $customer_name 客户名称
     *     }
     * }
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 基本信息验证
                'name' => 'required|string|max:200',
                'customer_name' => 'nullable|string|max:200',
                'name_en' => 'nullable|string|max:200',
                'customer_code' => 'nullable|string|max:50', // 客户编号由后台自动生成，不需要前端提供
                'credit_code' => 'required|string|max:50|unique:customers,credit_code', // 信用代码必填且唯一
                'legal_representative' => 'nullable|string|max:100',
                'company_manager' => 'nullable|string|max:100',
                'level' => 'nullable|integer',
                'employee_count' => 'nullable|string|max:50',
                'business_person' => 'nullable|integer',
                'business_assistant' => 'nullable|string|max:100',
                'business_partner' => 'nullable|string|max:200',
                'price_index' => 'nullable|integer',
                'innovation_index' => 'nullable|integer',
                'contract_count' => 'nullable|string|max:10',
                'latest_contract_date' => 'nullable|string|max:50',
                'creator' => 'nullable|string|max:100',
                'create_date' => 'nullable|string|max:20',
                'updater' => 'nullable|string|max:100',
                'update_time' => 'nullable|string|max:30',
                'remark' => 'nullable|string',

                // 联系信息验证（支持前端驼峰命名）
                'contact_name' => 'nullable|string|max:100',
                'contactName' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'contactPhone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'qq' => 'nullable|string|max:50',
                'wechat' => 'nullable|string|max:50',

                // 地址信息验证
                'country' => 'nullable|string|max:50',
                'province' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'district' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'address_en' => 'nullable|string|max:500',
                'other_address' => 'nullable|string|max:500',
                'industrial_park' => 'nullable|string|max:100',
                'zip_code' => 'nullable|string|max:20',
                'website' => 'nullable|string|max:200',

                // 费用信息验证
                'account_name' => 'nullable|string|max:200',
                'bank_name' => 'nullable|string|max:200',
                'bank_account' => 'nullable|string|max:100',
                'invoice_address' => 'nullable|string',
                'invoice_phone' => 'nullable|string|max:50',
                'is_general_taxpayer' => 'nullable|boolean',
                'billing_address' => 'nullable|string',

                // 企业信息验证
                'company_type' => 'nullable|string|max:100',
                'registered_capital' => 'nullable|string|max:50',
                'founding_date' => 'nullable|date',
                'industry' => 'nullable|string|max:100',
                'main_products' => 'nullable|string|max:500',
                'business_scope' => 'nullable|string',

                // 工商信息验证
                'economic_category' => 'nullable|string|max:10',
                'economic_door' => 'nullable|string|max:100',
                'economic_big_class' => 'nullable|string|max:100',
                'economic_mid_class' => 'nullable|string|max:100',
                'economic_small_class' => 'nullable|string|max:100',
                'research_staff_count' => 'nullable|string|max:50',
                'doctor_count' => 'nullable|string|max:50',
                'senior_engineer_count' => 'nullable|string|max:50',
                'master_count' => 'nullable|string|max:50',
                'middle_engineer_count' => 'nullable|string|max:50',
                'bachelor_count' => 'nullable|string|max:50',
                'overseas_returnee_count' => 'nullable|string|max:50',

                // 知识产权信息验证
                'trademark_count' => 'nullable|integer|min:0',
                'patent_count' => 'nullable|integer|min:0',
                'invention_patent_count' => 'nullable|integer|min:0',
                'copyright_count' => 'nullable|integer|min:0',
                'has_additional_deduction' => 'nullable|boolean',
                'has_school_cooperation' => 'nullable|boolean',
                'cooperation_school' => 'nullable|string|max:200',

                // 原有字段验证（保持兼容性）
                'customer_type' => 'nullable|integer|in:1,2,3',
                'customer_level' => 'nullable|integer|in:1,2,3',
                'customer_scale' => 'nullable|integer|in:1,2,3,4,5,6',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'business_assistant_id' => 'nullable|integer|exists:users,id',
                'business_partner_id' => 'nullable|integer|exists:users,id',
                'company_manager_id' => 'nullable|integer|exists:users,id',
                'process_staff_id' => 'nullable|integer|exists:users,id',
                'customer_no' => 'nullable|string|max:50',
                'sales_2021' => 'nullable|numeric',
                'research_fee_2021' => 'nullable|numeric',
                'loan_2021' => 'nullable|numeric',
                'high_tech_enterprise' => 'nullable|string|max:10',
                'province_enterprise' => 'nullable|string|max:10',
                'city_enterprise' => 'nullable|string|max:10',
                'province_tech_center' => 'nullable|string|max:10',
                'ip_standard' => 'nullable|string|max:10',
                'it_standard' => 'nullable|string|max:10',
                'is_jinxin_verified' => 'nullable|string|max:10',
                'is_science_verified' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 字段映射处理（前端驼峰命名转后端下划线命名）
            $fieldMapping = [
                // 基本信息
                'name' => 'name', // 前端的name对应后端的name字段，customer_name作为别名
                'nameEn' => 'name_en',
                'creditCode' => 'credit_code', // 前端的creditCode对应后端的credit_code（信用代码）
                'legalRepresentative' => 'legal_representative',
                'companyManager' => 'company_manager',
                'level' => 'level',
                'employeeCount' => 'employee_count',
                'businessPerson' => 'business_person_id',
                'businessAssistant' => 'business_assistant',
                'businessPartner' => 'business_partner',
                'priceIndex' => 'price_index',
                'innovationIndex' => 'innovation_index',
                'contractCount' => 'contract_count_str',
                'latestContractDate' => 'latest_contract_date_str',
                'creator' => 'creator',
                'createDate' => 'create_date',
                'createTime' => 'create_time',
                'updater' => 'updater',
                'updateTime' => 'update_time',
                'remark' => 'remark',

                // 联系信息
                'contactName' => 'contact_name',
                'contactPhone' => 'contact_phone',
                'email' => 'email',
                'qq' => 'qq',
                'wechat' => 'wechat',

                // 地址信息
                'country' => 'country',
                'province' => 'province',
                'city' => 'city',
                'district' => 'district',
                'address' => 'address',
                'addressEn' => 'address_en',
                'otherAddress' => 'other_address',
                'industrialPark' => 'industrial_park',
                'zipCode' => 'zip_code',
                'website' => 'website',

                // 费用信息
                'accountName' => 'account_name',
                'bankName' => 'bank_name',
                'bankAccount' => 'bank_account',
                'invoiceAddress' => 'invoice_address',
                'invoicePhone' => 'invoice_phone',
                'isGeneralTaxpayer' => 'is_general_taxpayer',
                'billingAddress' => 'billing_address',
                'invoiceCreditCode' => 'invoice_credit_code',

                // 企业信息
                'companyType' => 'company_type',
                'registeredCapital' => 'registered_capital',
                'foundingDate' => 'founding_date',
                'industry' => 'industry',
                'mainProducts' => 'main_products',
                'businessScope' => 'business_scope',

                // 工商信息
                'economicCategory' => 'economic_category',
                'economicDoor' => 'economic_door',
                'economicBigClass' => 'economic_big_class',
                'economicMidClass' => 'economic_mid_class',
                'economicSmallClass' => 'economic_small_class',
                'companyStaffCount' => 'company_staff_count',
                'researchStaffCount' => 'research_staff_count',
                'doctorCount' => 'doctor_count',
                'seniorEngineerCount' => 'senior_engineer_count',
                'masterCount' => 'master_count',
                'middleEngineerCount' => 'middle_engineer_count',
                'bachelorCount' => 'bachelor_count',
                'overseasReturneeCount' => 'overseas_returnee_count',

                // 知识产权信息
                'trademarkCount' => 'trademark_count',
                'patentCount' => 'patent_count',
                'inventionPatentCount' => 'invention_patent_count',
                'copyrightCount' => 'copyright_count',
                'hasAdditionalDeduction' => 'has_additional_deduction',
                'hasSchoolCooperation' => 'has_school_cooperation',
                'cooperationSchool' => 'cooperation_school',

                // 公司资质信息
                'isJinxinVerified' => 'is_jinxin_verified',
                'jinxinVerifyDate' => 'jinxin_verify_date',
                'isScienceVerified' => 'is_science_verified',
                'scienceVerifyDate' => 'science_verify_date',
                'highTechEnterprise' => 'high_tech_enterprise',
                'highTechDate' => 'high_tech_date',
                'provinceEnterprise' => 'province_enterprise',
                'provinceEnterpriseDate' => 'province_enterprise_date',
                'cityEnterprise' => 'city_enterprise',
                'cityEnterpriseDate' => 'city_enterprise_date',
                'provinceTechCenter' => 'province_tech_center',
                'provinceTechCenterDate' => 'province_tech_center_date',
                'ipStandard' => 'ip_standard',
                'ipStandardDate' => 'ip_standard_date',
                'itStandard' => 'it_standard',
                'infoStandardDate' => 'info_standard_date',

                // 预留字段和其他
                'spare1' => 'spare1',
                'spare2' => 'spare2',
                'spare3' => 'spare3',
                'spare4' => 'spare4',
                'spare5' => 'spare5',
                'originalSalesperson' => 'original_salesperson',
                'publicSeaName' => 'public_sea_name',

                // 动态数据字段（JSON格式）
                'salesData' => 'sales_data',
                'rdCostData' => 'rd_cost_data',
                'loanData' => 'loan_data',
                'researchProjectData' => 'research_project_data',
                'projectAmountData' => 'project_amount_data',
                'rdEquipmentOriginalValueData' => 'rd_equipment_original_value_data',
                'hasAuditReportData' => 'has_audit_report_data',
                'assetLiabilityRatioData' => 'asset_liability_ratio_data',
                'fixedAssetInvestmentData' => 'fixed_asset_investment_data',
                'equipmentInvestmentData' => 'equipment_investment_data',
                'smartEquipmentInvestmentData' => 'smart_equipment_investment_data',
                'rdEquipmentInvestmentData' => 'rd_equipment_investment_data',
                'itInvestmentData' => 'it_investment_data',
                'hasImportedEquipmentData' => 'has_imported_equipment_data',
                'hasInvestmentRecordData' => 'has_investment_record_data',
                'recordAmountData' => 'record_amount_data',
                'recordPeriodData' => 'record_period_data',

                // UI相关字段
                'rating' => 'rating',
                'avatar' => 'avatar',
                'tags' => 'tags',
                'importantEvents' => 'important_events',
            ];

            // 执行字段映射
            foreach ($fieldMapping as $frontendField => $backendField) {
                if (isset($data[$frontendField]) && !isset($data[$backendField])) {
                    $data[$backendField] = $data[$frontendField];
                }
            }

            // 确保必填字段有值
            if (!isset($data['customer_name']) && isset($data['name'])) {
                $data['customer_name'] = $data['name'];
            }

            // 也同时设置name字段用于前端显示
            if (!isset($data['name']) && isset($data['customer_name'])) {
                $data['name'] = $data['customer_name'];
            }

            // 处理信用代码映射（前端的creditCode字段映射到credit_code）
            if (isset($data['creditCode'])) {
                $data['credit_code'] = $data['creditCode'];
            }

            // 总是生成新的客户编号（不依赖前端输入）
            $data['customer_code'] = $this->generateCustomerCode();

            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // 设置时间戳
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $customer = Customer::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取客户详情
     */
    /**
     * 获取客户详情
     * 
     * @param int $id 客户ID
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     * - data: object 客户详细信息
     *   - id: integer 客户ID
     *   - customer_name: string 客户名称
     *   - customer_code: string 客户编号
     *   - credit_code: string 统一社会信用代码
     *   - legal_representative: string 法定代表人
     *   - level: integer 客户级别
     *   - scale: integer 客户规模
     *   - business_person: integer 业务人员ID
     *   - business_assistant: string 业务助理
     *   - business_partner: string 业务合伙人
     *   - company_manager: string 公司经理
     *   - contacts: array 联系人列表
     *   - applicants: array 申请人列表
     *   - inventors: array 发明人列表
     *   - opportunities: array 商机列表
     *   - contracts: array 合同列表
     *   - followupRecords: array 跟进记录列表
     *   - relatedPersons: array 相关人员列表
     *   - files: array 文件列表
     *   - businessPerson: object 业务人员信息
     *   - businessAssistant: object 业务助理信息
     *   - businessPartner: object 业务合伙人信息
     *   - companyManager: object 公司经理信息
     *   - creator: object 创建者信息
     *   - updater: object 更新者信息
     */
    public function show($id)
    {
        try {
            // 查询客户信息，包含所有关联数据
            $customer = Customer::with([
                'contacts',         // 联系人
                'applicants',       // 申请人
                'inventors',        // 发明人
                'opportunities',    // 商机
                'contracts',        // 合同
                'followupRecords',  // 跟进记录
                'relatedPersons',   // 相关人员
                'files',           // 文件
                'businessPerson',   // 业务人员
                'businessAssistant', // 业务助理
                'businessPartner',  // 业务合伙人
                'companyManager',   // 公司经理
                'creator',         // 创建者
                'updater'          // 更新者
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新客户信息
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * @param int $id 客户ID
     * 
     * 请求参数:
     * - name: string 客户名称
     * - customer_name: string 客户名称（备用字段）
     * - name_en: string 英文名称
     * - credit_code: string 统一社会信用代码（必填，唯一）
     * - legal_representative: string 法定代表人
     * - company_manager: string 公司经理
     * - level: integer 客户级别
     * - employee_count: string 员工数量
     * - business_person: integer 业务人员ID
     * - business_assistant: string 业务助理
     * - business_partner: string 业务合伙人
     * - price_index: integer 价格指数
     * - innovation_index: integer 创新指数
     * - contact_name: string 联系人姓名
     * - contact_phone: string 联系电话
     * - email: string 邮箱
     * - qq: string QQ号
     * - wechat: string 微信号
     * - province: string 省份
     * - city: string 城市
     * - district: string 区县
     * - address: string 详细地址
     * - industrial_park: string 产业园区
     * - account_name: string 开户名称
     * - bank_name: string 开户银行
     * - bank_account: string 银行账号
     * - company_type: string 公司类型
     * - registered_capital: string 注册资本
     * - founding_date: date 成立日期
     * - industry: string 所属行业
     * - economic_category: string 国民经济行业分类
     * - trademark_count: integer 商标数量
     * - patent_count: integer 专利数量
     * - invention_patent_count: integer 发明专利数量
     * - copyright_count: integer 著作权数量
     * - is_jinxin_verified: string 是否通过金信认证
     * - high_tech_enterprise: string 是否高新技术企业
     * - remark: string 备注
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     * - data: object 更新后的客户信息
     *   - id: integer 客户ID
     *   - customer_name: string 客户名称
     *   - customer_code: string 客户编号
     *   - credit_code: string 统一社会信用代码
     *   - legal_representative: string 法定代表人
     *   - level: integer 客户级别
     *   - business_person_id: integer 业务人员ID
     *   - contact_name: string 联系人姓名
     *   - contact_phone: string 联系电话
     *   - address: string 地址
     *   - updated_at: string 更新时间
     */
    public function update(Request $request, $id)
    {
        try {
            // 查找要更新的客户
            $customer = Customer::findOrFail($id);

            // 验证请求数据
            $validator = Validator::make($request->all(), [
                // 基本信息验证
                'name' => 'nullable|string|max:200',
                'customer_name' => 'nullable|string|max:200',
                'name_en' => 'nullable|string|max:200',
                'customer_code' => 'nullable|string|max:50', // 客户编号不允许修改
                'credit_code' => 'required|string|max:50|unique:customers,credit_code,' . $id, // 信用代码必填且唯一
                'legal_representative' => 'nullable|string|max:100',
                'company_manager' => 'nullable|string|max:100',
                'level' => 'nullable|integer',
                'employee_count' => 'nullable|string|max:50',
                'business_person' => 'nullable|integer',
                'business_assistant' => 'nullable|string|max:100',
                'business_partner' => 'nullable|string|max:200',
                'price_index' => 'nullable|integer',
                'innovation_index' => 'nullable|integer',
                'contract_count' => 'nullable|string|max:10',
                'latest_contract_date' => 'nullable|string|max:50',
                'creator' => 'nullable|string|max:100',
                'create_date' => 'nullable|string|max:20',
                'updater' => 'nullable|string|max:100',
                'update_time' => 'nullable|string|max:30',
                'remark' => 'nullable|string',

                // 联系信息验证
                'contact_name' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'qq' => 'nullable|string|max:50',
                'wechat' => 'nullable|string|max:50',

                // 地址信息验证
                'country' => 'nullable|string|max:50',
                'province' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'district' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'address_en' => 'nullable|string|max:500',
                'other_address' => 'nullable|string|max:500',
                'industrial_park' => 'nullable|string|max:100',
                'zip_code' => 'nullable|string|max:20',
                'website' => 'nullable|string|max:200',

                // 费用信息验证
                'account_name' => 'nullable|string|max:200',
                'bank_name' => 'nullable|string|max:200',
                'bank_account' => 'nullable|string|max:100',
                'invoice_address' => 'nullable|string',
                'invoice_phone' => 'nullable|string|max:50',
                'is_general_taxpayer' => 'nullable|boolean',
                'billing_address' => 'nullable|string',
                'invoice_credit_code' => 'nullable|string|max:50',

                // 企业信息验证
                'company_type' => 'nullable|string|max:100',
                'registered_capital' => 'nullable|string|max:50',
                'founding_date' => 'nullable|date',
                'industry' => 'nullable|string|max:100',
                'main_products' => 'nullable|string|max:500',
                'business_scope' => 'nullable|string',

                // 工商信息验证
                'economic_category' => 'nullable|string|max:10',
                'economic_door' => 'nullable|string|max:100',
                'economic_big_class' => 'nullable|string|max:100',
                'economic_mid_class' => 'nullable|string|max:100',
                'economic_small_class' => 'nullable|string|max:100',
                'company_staff_count' => 'nullable|string|max:50',
                'research_staff_count' => 'nullable|string|max:50',
                'doctor_count' => 'nullable|string|max:50',
                'senior_engineer_count' => 'nullable|string|max:50',
                'master_count' => 'nullable|string|max:50',
                'middle_engineer_count' => 'nullable|string|max:50',
                'bachelor_count' => 'nullable|string|max:50',
                'overseas_returnee_count' => 'nullable|string|max:50',

                // 知识产权信息验证
                'trademark_count' => 'nullable|integer|min:0',
                'patent_count' => 'nullable|integer|min:0',
                'invention_patent_count' => 'nullable|integer|min:0',
                'copyright_count' => 'nullable|integer|min:0',
                'has_additional_deduction' => 'nullable|boolean',
                'has_school_cooperation' => 'nullable|boolean',
                'cooperation_school' => 'nullable|string|max:200',

                // 公司资质信息验证
                'is_jinxin_verified' => 'nullable|string|max:10',
                'jinxin_verify_date' => 'nullable|string|max:20',
                'is_science_verified' => 'nullable|string|max:10',
                'science_verify_date' => 'nullable|string|max:20',
                'high_tech_enterprise' => 'nullable|string|max:10',
                'high_tech_date' => 'nullable|string|max:20',
                'province_enterprise' => 'nullable|string|max:10',
                'province_enterprise_date' => 'nullable|string|max:20',
                'city_enterprise' => 'nullable|string|max:10',
                'city_enterprise_date' => 'nullable|string|max:20',
                'province_tech_center' => 'nullable|string|max:10',
                'province_tech_center_date' => 'nullable|string|max:20',
                'ip_standard' => 'nullable|string|max:10',
                'ip_standard_date' => 'nullable|string|max:20',
                'it_standard' => 'nullable|string|max:10',
                'info_standard_date' => 'nullable|string|max:20',

                // 原有字段验证（保持兼容性）
                'customer_type' => 'nullable|integer|in:1,2,3',
                'customer_level' => 'nullable|integer|in:1,2,3',
                'customer_scale' => 'nullable|integer|in:1,2,3,4,5,6',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'business_assistant_id' => 'nullable|integer|exists:users,id',
                'business_partner_id' => 'nullable|integer|exists:users,id',
                'company_manager_id' => 'nullable|integer|exists:users,id',
                'process_staff_id' => 'nullable|integer|exists:users,id',
                'customer_no' => 'nullable|string|max:50',
                'sales_2021' => 'nullable|numeric',
                'research_fee_2021' => 'nullable|numeric',
                'loan_2021' => 'nullable|numeric',
                'innovation_index_num' => 'nullable|integer|in:1,2,3',
                'price_index_num' => 'nullable|integer|in:1,2,3',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 使用相同的字段映射逻辑
            $fieldMapping = [
                // 基本信息
                'name' => 'name',
                'nameEn' => 'name_en',
                'creditCode' => 'credit_code', // 前端的creditCode对应后端的credit_code（信用代码）
                'legalRepresentative' => 'legal_representative',
                'companyManager' => 'company_manager',
                'level' => 'level',
                'employeeCount' => 'employee_count',
                'businessPerson' => 'business_person_id',
                'businessAssistant' => 'business_assistant',
                'businessPartner' => 'business_partner',
                'priceIndex' => 'price_index',
                'innovationIndex' => 'innovation_index',
                'contractCount' => 'contract_count_str',
                'latestContractDate' => 'latest_contract_date_str',
                'creator' => 'creator',
                'createDate' => 'create_date',
                'createTime' => 'create_time',
                'updater' => 'updater',
                'updateTime' => 'update_time',
                'remark' => 'remark',

                // 联系信息
                'contactName' => 'contact_name',
                'contactPhone' => 'contact_phone',
                'email' => 'email',
                'qq' => 'qq',
                'wechat' => 'wechat',

                // 地址信息
                'country' => 'country',
                'province' => 'province',
                'city' => 'city',
                'district' => 'district',
                'address' => 'address',
                'addressEn' => 'address_en',
                'otherAddress' => 'other_address',
                'industrialPark' => 'industrial_park',
                'zipCode' => 'zip_code',
                'website' => 'website',

                // 费用信息
                'accountName' => 'account_name',
                'bankName' => 'bank_name',
                'bankAccount' => 'bank_account',
                'invoiceAddress' => 'invoice_address',
                'invoicePhone' => 'invoice_phone',
                'isGeneralTaxpayer' => 'is_general_taxpayer',
                'billingAddress' => 'billing_address',
                'invoiceCreditCode' => 'invoice_credit_code',

                // 企业信息
                'companyType' => 'company_type',
                'registeredCapital' => 'registered_capital',
                'foundingDate' => 'founding_date',
                'industry' => 'industry',
                'mainProducts' => 'main_products',
                'businessScope' => 'business_scope',

                // 工商信息
                'economicCategory' => 'economic_category',
                'economicDoor' => 'economic_door',
                'economicBigClass' => 'economic_big_class',
                'economicMidClass' => 'economic_mid_class',
                'economicSmallClass' => 'economic_small_class',
                'companyStaffCount' => 'company_staff_count',
                'researchStaffCount' => 'research_staff_count',
                'doctorCount' => 'doctor_count',
                'seniorEngineerCount' => 'senior_engineer_count',
                'masterCount' => 'master_count',
                'middleEngineerCount' => 'middle_engineer_count',
                'bachelorCount' => 'bachelor_count',
                'overseasReturneeCount' => 'overseas_returnee_count',

                // 知识产权信息
                'trademarkCount' => 'trademark_count',
                'patentCount' => 'patent_count',
                'inventionPatentCount' => 'invention_patent_count',
                'copyrightCount' => 'copyright_count',
                'hasAdditionalDeduction' => 'has_additional_deduction',
                'hasSchoolCooperation' => 'has_school_cooperation',
                'cooperationSchool' => 'cooperation_school',

                // 公司资质信息
                'isJinxinVerified' => 'is_jinxin_verified',
                'jinxinVerifyDate' => 'jinxin_verify_date',
                'isScienceVerified' => 'is_science_verified',
                'scienceVerifyDate' => 'science_verify_date',
                'highTechEnterprise' => 'high_tech_enterprise',
                'highTechDate' => 'high_tech_date',
                'provinceEnterprise' => 'province_enterprise',
                'provinceEnterpriseDate' => 'province_enterprise_date',
                'cityEnterprise' => 'city_enterprise',
                'cityEnterpriseDate' => 'city_enterprise_date',
                'provinceTechCenter' => 'province_tech_center',
                'provinceTechCenterDate' => 'province_tech_center_date',
                'ipStandard' => 'ip_standard',
                'ipStandardDate' => 'ip_standard_date',
                'itStandard' => 'it_standard',
                'infoStandardDate' => 'info_standard_date',
            ];

            // 执行字段映射
            foreach ($fieldMapping as $frontendField => $backendField) {
                if (isset($data[$frontendField]) && !isset($data[$backendField])) {
                    $data[$backendField] = $data[$frontendField];
                }
            }

            // 确保必填字段有值
            if (!isset($data['customer_name']) && isset($data['name'])) {
                $data['customer_name'] = $data['name'];
            }

            // 也同时设置name字段用于前端显示
            if (!isset($data['name']) && isset($data['customer_name'])) {
                $data['name'] = $data['customer_name'];
            }

            // 处理信用代码映射（前端的creditCode字段映射到credit_code）
            if (isset($data['creditCode'])) {
                $data['credit_code'] = $data['creditCode'];
            }

            // 更新时不修改客户编号（customer_code）
            unset($data['customer_code']);

            $data['updated_by'] = auth()->id();

            $customer->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除客户
     * 
     * @param int $id 客户ID
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     */
    public function destroy($id)
    {
        try {
            // 查找并删除客户
            $customer = Customer::findOrFail($id);
            $customer->delete();

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

    /**
     * 批量删除客户
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * 
     * 请求参数:
     * - ids: array 要删除的客户ID数组
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     */
    public function batchDestroy(Request $request)
    {
        try {
            // 获取要删除的客户ID数组
            $ids = $request->input('ids', []);
            
            // 验证是否选择了要删除的客户
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要删除的客户'
                ], 422);
            }

            // 批量删除客户
            Customer::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => '批量删除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '批量删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 转移客户
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * 
     * 请求参数:
     * - customer_ids: array 要转移的客户ID数组（必填）
     * - to_user_id: integer 目标用户ID（必填）
     * - reason: string 转移原因（可选）
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     */
    public function transfer(Request $request)
    {
        try {
            // 验证请求参数
            $validator = Validator::make($request->all(), [
                'customer_ids' => 'required|array',
                'customer_ids.*' => 'integer|exists:customers,id',
                'to_user_id' => 'required|integer|exists:users,id',
                'reason' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 获取转移参数
            $customerIds = $request->customer_ids;
            $toUserId = $request->to_user_id;
            
            // 批量更新客户的业务人员
            Customer::whereIn('id', $customerIds)->update([
                'business_person_id' => $toUserId,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '转移成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '转移失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 移入公海
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * 
     * 请求参数:
     * - customer_ids: array 要移入公海的客户ID数组
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作消息
     */
    public function moveToPublic(Request $request)
    {
        try {
            // 获取要移入公海的客户ID数组
            $customerIds = $request->input('customer_ids', []);
            
            // 验证是否选择了客户
            if (empty($customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要移入公海的客户'
                ], 422);
            }

            // 将客户的业务人员设为空，移入公海
            Customer::whereIn('id', $customerIds)->update([
                'business_person_id' => null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '移入公海成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '移入公海失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量转移业务员
     */
    public function batchTransferBusiness(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_ids' => 'required|array',
                'customer_ids.*' => 'integer|exists:customers,id',
                'selected_persons' => 'required|array',
                'selected_persons.*' => 'integer|exists:users,id',
                'sync_case_business' => 'nullable|boolean',
                'sync_contract_business' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customerIds = $request->customer_ids;
            $selectedPersons = $request->selected_persons;
            
            // 如果选择了多个人员，取第一个作为业务人员
            $businessPersonId = $selectedPersons[0];
            
            Customer::whereIn('id', $customerIds)->update([
                'business_person_id' => $businessPersonId,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '批量转移业务员成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '批量转移失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量添加业务员
     */
    public function batchAddBusiness(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_ids' => 'required|array',
                'customer_ids.*' => 'integer|exists:customers,id',
                'selected_persons' => 'required|array',
                'selected_persons.*' => 'integer|exists:users,id',
                'sync_case_business' => 'nullable|boolean',
                'sync_contract_business' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customerIds = $request->customer_ids;
            $selectedPersons = $request->selected_persons;
            
            // 这里可以根据业务逻辑选择如何分配多个人员
            // 暂时将第一个设为业务人员，第二个设为助理，第三个设为协作人
            $updateData = ['updated_by' => auth()->id(), 'updated_at' => now()];
            
            if (isset($selectedPersons[0])) {
                $updateData['business_person_id'] = $selectedPersons[0];
            }
            if (isset($selectedPersons[1])) {
                $updateData['business_assistant_id'] = $selectedPersons[1];
            }
            if (isset($selectedPersons[2])) {
                $updateData['business_partner_id'] = $selectedPersons[2];
            }
            
            Customer::whereIn('id', $customerIds)->update($updateData);

            return response()->json([
                'success' => true,
                'message' => '批量添加业务员成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '批量添加失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取用户选项列表
     */
    public function getUserOptions()
    {
        try {
            $users = User::select('id', 'real_name', 'username', 'department_id')
                ->where('status', 1)
                ->orderBy('real_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $users->map(function($user) {
                    return [
                        'userId' => $user->id,
                        'userName' => $user->real_name,
                        'username' => $user->username,
                        'departmentId' => $user->department_id,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取组织架构树（用于业务员选择）
     */
    public function getOrganizationTree()
    {
        try {
            // 获取所有用户和部门
            $users = User::select('id', 'real_name', 'department_id')->where('status', 1)->get();

            // 从数据库获取部门数据
            $departments = [];
            try {
                $departments = \App\Models\Department::select('id', 'department_name as name', 'parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                // 如果部门表不存在或查询失败，使用默认数据
                $departments = [
                    ['id' => 1, 'name' => '管理部门', 'parent_id' => 0],
                    ['id' => 2, 'name' => '业务部门', 'parent_id' => 0],
                    ['id' => 3, 'name' => '技术部门', 'parent_id' => 0],
                    ['id' => 4, 'name' => '人事部门', 'parent_id' => 1],
                ];
            }

            // 构建组织架构树
            $tree = [];

            // 首先构建部门映射
            $departmentMap = [];
            foreach ($departments as $dept) {
                $departmentMap[$dept['id']] = [
                    'id' => 'dept_' . $dept['id'],
                    'label' => $dept['name'],
                    'type' => 'dept',
                    'children' => [],
                    'count' => 0,
                    'parent_id' => $dept['parent_id']
                ];
            }

            // 添加用户到对应部门
            foreach ($users as $user) {
                if ($user->department_id && isset($departmentMap[$user->department_id])) {
                    $departmentMap[$user->department_id]['children'][] = [
                        'id' => $user->id,
                        'label' => $user->real_name,
                        'type' => 'user',
                        'parentId' => $user->department_id,
                        'department_name' => $departmentMap[$user->department_id]['label']
                    ];
                    $departmentMap[$user->department_id]['count']++;
                }
            }

            // 构建树形结构（显示所有部门，包括子部门）
            foreach ($departmentMap as $deptId => $dept) {
                if ($dept['parent_id'] == 0 || $dept['parent_id'] == null) {
                    // 顶级部门
                    $tree[] = $dept;
                } else {
                    // 子部门也显示为独立项
                    $tree[] = $dept;
                }
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $tree
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取基础配置数据
     */
    public function getConfigData()
    {
        try {
            // 从数据库获取客户等级
            $customerLevels = \App\Models\CustomerLevel::where('is_valid', 1)
                ->orderBy('sort_order')
                ->orderBy('sort')
                ->select('id as value', 'level_name as label', 'level_code')
                ->get()
                ->toArray();



            // 从数据库获取客户规模
            $customerScales = \App\Models\CustomerScale::where('is_valid', true)
                ->orderBy('sort')
                ->select('id as value', 'scale_name as label')
                ->get()
                ->toArray();



            // 从数据库获取产业园区（优先使用ParkConfig，如果没有数据则使用Park）
            $industrialParks = \App\Models\ParkConfig::enabled()->ordered()
                ->select('id as value', 'park_name as label')
                ->get()
                ->toArray();


            // 从数据库获取创新指数
            $innovationIndices = \App\Models\InnovationIndices::where('status', 1)
                ->orderBy('sort_order')
                ->select('id as value', 'index_name as label')
                ->get()
                ->toArray();

            // 从数据库获取价值指数
            $priceIndices = \App\Models\PriceIndices::where('status', 1)
                ->orderBy('sort_order')
                ->select('id as value', 'index_name as label')
                ->get()
                ->toArray();

            // 省市区数据（保持原有的静态数据）
            $provinces = [
                '北京', '上海', '天津', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江',
                '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南',
                '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '台湾',
                '内蒙古', '广西', '西藏', '宁夏', '新疆', '香港', '澳门'
            ];

            $cities = [
                '北京市', '上海市', '广州市', '深圳市', '杭州市', '南京市', '成都市', '武汉市', '西安市'
            ];

            $districts = [
                '朝阳区', '海淀区', '东城区', '西城区', '丰台区', '石景山区', '门头沟区', '房山区', '通州区'
            ];

            // 行业数据（保持原有的静态数据）
            $industries = [
                ['value' => 'IT', 'label' => '信息技术'],
                ['value' => '金融', 'label' => '金融行业'],
                ['value' => '教育', 'label' => '教育行业'],
                ['value' => '医疗', 'label' => '医疗行业'],
                ['value' => '制造', 'label' => '制造业'],
                ['value' => '零售', 'label' => '零售业'],
                ['value' => '其他', 'label' => '其他行业']
            ];

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'provinces' => array_map(function($item) { return ['value' => $item, 'label' => $item]; }, $provinces),
                    'cities' => array_map(function($item) { return ['value' => $item, 'label' => $item]; }, $cities),
                    'districts' => array_map(function($item) { return ['value' => $item, 'label' => $item]; }, $districts),
                    'levels' => $customerLevels,
                    'industries' => $industries,
                    'customerScales' => $customerScales,
                    'industrialParks' => $industrialParks,
                    'innovationIndices' => $innovationIndices,
                    'priceIndices' => $priceIndices
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
     * 导出客户数据
     */
    /**
     * 导出客户数据
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * 
     * 请求参数:
     * - name: string 客户名称（模糊查询）
     * - creditCode: string 信用代码（模糊查询）
     * - level: integer 客户级别
     * - customerScale: integer 客户规模
     * - businessPerson: array|integer 业务人员ID
     * - province: string 省份
     * - city: string 城市
     * - district: string 区县
     * - industrialPark: string 产业园区（模糊查询）
     * - innovationIndex: integer 创新指数
     * - priceIndex: integer 价格指数
     * - create_date_start: string 创建开始日期
     * - create_date_end: string 创建结束日期
     * - latest_contract_date_start: string 最新合同开始日期
     * - latest_contract_date_end: string 最新合同结束日期
     * - format: string 导出格式（csv|xlsx，默认csv）
     * - filename: string 文件名（可选）
     * 
     * @return \Illuminate\Http\Response 文件下载响应
     */
    public function export(Request $request)
    {
        try {
            // 构建查询条件
            $query = Customer::query();

            // 应用查询条件（与index方法一致）
            if ($request->filled('name')) {
                $query->where('customer_name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('creditCode')) {
                $query->where('credit_code', 'like', '%' . $request->creditCode . '%');
            }

            if ($request->filled('level')) {
                $query->where('customer_level', $request->level);
            }

            if ($request->filled('customerScale')) {
                $query->where('customer_scale', $request->customerScale);
            }

            if ($request->filled('businessPerson')) {
                $businessPersons = is_array($request->businessPerson) ? $request->businessPerson : [$request->businessPerson];
                $query->whereIn('business_person_id', $businessPersons);
            }

            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }

            if ($request->filled('district')) {
                $query->where('district', $request->district);
            }

            if ($request->filled('industrialPark')) {
                $query->where('industrial_park', 'like', '%' . $request->industrialPark . '%');
            }

            if ($request->filled('innovationIndex')) {
                $query->where('innovation_index', $request->innovationIndex);
            }

            if ($request->filled('priceIndex')) {
                $query->where('price_index', $request->priceIndex);
            }

            // 日期范围查询
            if ($request->filled('create_date_start') && $request->filled('create_date_end')) {
                $query->whereBetween('created_at', [$request->create_date_start, $request->create_date_end . ' 23:59:59']);
            }

            if ($request->filled('latest_contract_date_start') && $request->filled('latest_contract_date_end')) {
                $query->whereBetween('latest_contract_date', [$request->latest_contract_date_start, $request->latest_contract_date_end]);
            }

            // 获取数据
            $customers = $query->orderBy('id', 'desc')->get();

            // 准备导出数据
            $exportData = [];
            // 设置表头
            $exportData[] = [
                '客户编号', '客户名称', '英文名称', '信用代码', '客户等级', '法定代表人',
                '公司负责人', '员工数量', '行业', '业务人员', '业务助理', '业务协作人',
                '联系人', '联系电话', '邮箱', 'QQ', '微信', '国家', '省', '市', '区',
                '详细地址', '英文地址', '产业园区', '邮编', '网站', '价格指数', '创新指数',
                '合同数量', '最新合同日期', '备注', '创建人', '创建时间'
            ];

            foreach ($customers as $customer) {
                $exportData[] = [
                    $customer->customer_code,
                    $customer->customer_name,
                    $customer->name_en,
                    $customer->credit_code,
                    $customer->level,
                    $customer->legal_representative,
                    $customer->company_manager,
                    $customer->employee_count,
                    $customer->industry,
                    $customer->business_person,
                    $customer->business_assistant,
                    $customer->business_partner,
                    $customer->contact_name,
                    $customer->contact_phone,
                    $customer->email,
                    $customer->qq,
                    $customer->wechat,
                    $customer->country,
                    $customer->province,
                    $customer->city,
                    $customer->district,
                    $customer->address,
                    $customer->address_en,
                    $customer->industrial_park,
                    $customer->zip_code,
                    $customer->website,
                    $customer->price_index_str,
                    $customer->innovation_index_str,
                    $customer->contract_count_str,
                    $customer->latest_contract_date_str,
                    $customer->remark,
                    $customer->creator,
                    $customer->created_at ? (is_string($customer->created_at) ? $customer->created_at : $customer->created_at->format('Y-m-d H:i:s')) : '',
                ];
            }

            // 获取导出格式
            $format = $request->input('format', 'csv');
            $filename = $request->input('filename', '客户数据导出_' . date('YmdHis'));

            if ($format === 'xlsx') {
                // Excel格式导出
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // 设置表头
                $sheet->fromArray($exportData, null, 'A1');

                // 设置列宽自适应
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // 输出到临时文件
                $tempFile = tempnam(sys_get_temp_dir(), 'export');
                $writer->save($tempFile);

                $content = file_get_contents($tempFile);
                unlink($tempFile);

                return response($content)
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            } else {
                // CSV格式导出
                $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM

                foreach ($exportData as $row) {
                    $csvContent .= implode(',', array_map(function($field) {
                        return '"' . str_replace('"', '""', $field) . '"';
                    }, $row)) . "\r\n";
                }

                return response($csvContent)
                    ->header('Content-Type', 'text/csv; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下载导入模板
     */
    public function downloadTemplate()
    {
        try {
            // 模板数据
            $templateData = [];
            
            // 添加表头
            $templateData[] = [
                '客户名称*', '英文名称', '信用代码', '客户等级', '法定代表人',
                '公司负责人', '员工数量', '行业', '联系人*', '联系电话*', '邮箱', 'QQ', '微信',
                '国家', '省*', '市*', '区', '详细地址', '英文地址', '产业园区', '邮编', '网站',
                '价格指数', '创新指数', '备注'
            ];
            
            // 添加示例数据
            $templateData[] = [
                '示例科技有限公司', 'Example Technology Co., Ltd.', '91110000123456789A', 'A', '张三',
                '张总', '100', '软件和信息技术服务业', '李联系', '010-12345678', 'example@test.com', '123456789', 'wechat123',
                '中国', '北京', '北京', '朝阳区', '测试路123号', '123 Test Road', '中关村科技园', '100000', 'https://www.example.com',
                '85', '90', '这是一个示例客户'
            ];

            // 生成CSV内容
            $filename = '客户导入模板_' . date('YmdHis') . '.csv';
            $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
            
            foreach ($templateData as $row) {
                $csvContent .= implode(',', array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $row)) . "\r\n";
            }

            return response($csvContent)
                ->header('Content-Type', 'application/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '模板下载失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 导入客户数据
     * 
     * @param \Illuminate\Http\Request $request 请求对象
     * 
     * 请求参数:
     * - file: file CSV文件（必填）
     * 
     * CSV文件格式要求:
     * - 文件大小不超过10MB
     * - 文件类型必须为CSV或TXT
     * - 必填字段：客户名称、联系人、联系电话、省、市
     * - 支持字段：客户编号、客户名称、英文名称、信用代码、客户等级、法定代表人、
     *   公司负责人、员工数量、行业、业务人员、业务助理、业务协作人、联系人、
     *   联系电话、邮箱、QQ、微信、国家、省、市、区、地址、英文地址、产业园区、
     *   邮编、网站、价格指数、创新指数、备注等
     * 
     * 返回参数:
     * - success: boolean 操作是否成功
     * - message: string 操作结果消息
     * - data: object 导入结果详情
     *   - success_count: integer 成功导入数量
     *   - error_count: integer 失败数量
     *   - error_rows: array 失败行详情
     *     - row: integer 行号
     *     - error: string 错误信息
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function import(Request $request)
    {
        try {
            // 验证上传文件
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240', // 最大10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '文件验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $path = $file->getRealPath();
            
            // 读取CSV文件
            $csvData = array_map('str_getcsv', file($path));
            
            // 移除BOM和空行
            if (!empty($csvData)) {
                $csvData[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $csvData[0][0]);
                $csvData = array_filter($csvData, function($row) {
                    return !empty(array_filter($row));
                });
            }

            if (count($csvData) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => '文件格式错误或没有数据行'
                ], 422);
            }

            // 获取表头
            $headers = array_shift($csvData);
            
            $successCount = 0;
            $errorRows = [];
            
            foreach ($csvData as $rowIndex => $row) {
                try {
                    // 检查必填字段
                    if (empty($row[0]) || empty($row[8]) || empty($row[9]) || empty($row[14]) || empty($row[15])) {
                        $errorRows[] = [
                            'row' => $rowIndex + 2,
                            'error' => '必填字段不能为空（客户名称、联系人、联系电话、省、市）'
                        ];
                        continue;
                    }

                    // 检查是否已存在
                    $existingCustomer = Customer::where('customer_name', $row[0])->first();
                    if ($existingCustomer) {
                        $errorRows[] = [
                            'row' => $rowIndex + 2,
                            'error' => '客户名称已存在'
                        ];
                        continue;
                    }

                    // 准备数据
                    $customerData = [
                        'customer_code' => $this->generateCustomerCode(),
                        'customer_name' => $row[0] ?? '',
                        'name' => $row[0] ?? '',
                        'name_en' => $row[1] ?? '',
                        'credit_code' => $row[2] ?? null,
                        'level' => $row[3] ?? null,
                        'legal_representative' => $row[4] ?? '',
                        'company_manager' => $row[5] ?? '',
                        'employee_count' => $row[6] ?? '',
                        'industry' => $row[7] ?? '',
                        'contact_name' => $row[8] ?? '',
                        'contact_phone' => $row[9] ?? '',
                        'email' => $row[10] ?? '',
                        'qq' => $row[11] ?? '',
                        'wechat' => $row[12] ?? '',
                        'country' => $row[13] ?? '中国',
                        'province' => $row[14] ?? '',
                        'city' => $row[15] ?? '',
                        'district' => $row[16] ?? '',
                        'address' => $row[17] ?? '',
                        'address_en' => $row[18] ?? '',
                        'industrial_park' => $row[19] ?? '',
                        'zip_code' => $row[20] ?? '',
                        'website' => $row[21] ?? '',
                        'price_index_str' => $row[22] ?? '',
                        'innovation_index_str' => $row[23] ?? '',
                        'remark' => $row[24] ?? '',
                        'customer_status' => 1,
                        'creator' => auth()->user()->name ?? '系统导入',
                        'create_user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    Customer::create($customerData);
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorRows[] = [
                        'row' => $rowIndex + 2,
                        'error' => '数据保存失败：' . $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "导入完成！成功：{$successCount}条" . (count($errorRows) > 0 ? "，失败：" . count($errorRows) . "条" : ""),
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => count($errorRows),
                    'error_rows' => $errorRows
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导入失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成客户编码
     * 格式：KH + 年月日 + 6位随机数字 + 2位校验码
     * 例如：KH20250818123456AB
     */
    /**
     * 生成客户编码
     * 
     * 编码格式：KH + 年月日 + 6位随机数字 + 2位校验码
     * 例如：KH20250118123456AB
     * 
     * 生成规则：
     * 1. 前缀：KH（客户的拼音首字母）
     * 2. 日期：当前年月日（YYYYMMDD格式）
     * 3. 随机数：6位随机数字（000000-999999）
     * 4. 校验码：基于前面内容生成的2位字母数字校验码
     * 
     * 唯一性保证：
     * - 使用数据库事务确保并发安全
     * - 最多尝试10次生成不重复编码
     * - 如果10次都重复，使用时间戳作为后备方案
     * 
     * @return string 生成的客户编码
     */
    private function generateCustomerCode()
    {
        return DB::transaction(function () {
            $maxAttempts = 10; // 最大尝试次数

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                // 生成基础编码
                $prefix = 'KH' . date('Ymd'); // KH + 年月日
                $randomNumber = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // 6位随机数字

                // 生成2位校验码（基于前面的数字计算）
                $baseCode = $prefix . $randomNumber;
                $checksum = $this->generateChecksum($baseCode);

                $newCode = $baseCode . $checksum;

                // 检查是否已存在（使用悲观锁防止并发问题）
                $exists = Customer::where('customer_code', $newCode)->lockForUpdate()->exists();
                if (!$exists) {
                    return $newCode;
                }
            }

            // 如果多次尝试都失败，使用时间戳确保唯一性
            $fallbackCode = 'KH' . date('YmdHis') . mt_rand(10, 99);
            return $fallbackCode;
        });
    }

    /**
     * 生成校验码
     * 
     * 基于输入字符串使用CRC32算法生成2位字母数字校验码
     * 
     * 算法说明：
     * 1. 对输入字符串计算CRC32哈希值
     * 2. 使用36进制字符集（0-9A-Z）
     * 3. 通过取模和除法运算生成2位校验码
     * 
     * @param string $input 需要生成校验码的输入字符串
     * @return string 2位字母数字校验码
     */
    private function generateChecksum($input)
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $hash = crc32($input);
        $checksum = '';

        // 生成2位校验码
        for ($i = 0; $i < 2; $i++) {
            $checksum .= $chars[abs($hash) % 36];
            $hash = intval($hash / 36);
        }

        return $checksum;
    }

    /**
     * 保存客户排序
     */
    public function saveCustomerSort(Request $request)
    {
        try {
            $userId = auth()->id();
            $listType = $request->input('list_type', 'allCustomer');
            $sortData = $request->input('sort_data', []);

            if (empty($sortData)) {
                return response()->json([
                    'success' => false,
                    'message' => '排序数据不能为空'
                ], 400);
            }

            CustomerSort::saveUserSort($userId, $listType, $sortData);

            return response()->json([
                'success' => true,
                'message' => '排序保存成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '保存排序失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取客户排序
     */
    public function getCustomerSort(Request $request)
    {
        try {
            $userId = auth()->id();
            $listType = $request->input('list_type', 'allCustomer');

            $sortData = CustomerSort::getUserSort($userId, $listType);

            return response()->json([
                'success' => true,
                'data' => $sortData,
                'message' => '获取排序成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取排序失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 重置客户排序
     */
    public function resetCustomerSort(Request $request)
    {
        try {
            $userId = auth()->id();
            $listType = $request->input('list_type', 'allCustomer');

            CustomerSort::resetUserSort($userId, $listType);

            return response()->json([
                'success' => true,
                'message' => '排序重置成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '重置排序失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 设置客户排序
     */
    public function setCustomerSort(Request $request)
    {
        try {
            // 尝试多种方式获取用户ID
            $userId = auth()->id();
            if (!$userId) {
                $userId = auth('api')->id();
            }
            if (!$userId) {
                $user = auth()->user();
                $userId = $user ? $user->id : null;
            }
            if (!$userId) {
                // 从请求头获取用户信息
                $token = $request->bearerToken();
                if ($token) {
                    // 这里可以添加token解析逻辑
                    // 暂时使用默认用户ID 1 作为降级处理
                    $userId = 1;
                }
            }

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未登录'
                ], 401);
            }

            $listType = $request->input('list_type', 'allCustomer');
            $sortData = $request->input('sort_data', []);

            if (empty($sortData)) {
                return response()->json([
                    'success' => false,
                    'message' => '排序数据不能为空'
                ], 400);
            }

            // 处理每个客户的排序设置
            foreach ($sortData as $item) {
                $customerId = $item['customer_id'];
                $sortOrder = $item['sort_order'];

                // 使用CustomerSort模型的setCustomerSort方法，它会自动处理相同序号的后移
                CustomerSort::setCustomerSort($userId, $customerId, $listType, $sortOrder);
            }

            return response()->json([
                'success' => true,
                'message' => '排序设置成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '设置排序失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 测试客户等级和规模数据
     */
    public function testLevelAndScale()
    {
        try {
            // 检查客户等级表
            $levels = \App\Models\CustomerLevel::all();
            $scales = \App\Models\CustomerScale::all();

            // 检查客户表中的数据
            $customers = Customer::with(['customerLevel', 'customerScale'])->take(5)->get();

            $result = [
                'levels_count' => $levels->count(),
                'levels' => $levels->toArray(),
                'scales_count' => $scales->count(),
                'scales' => $scales->toArray(),
                'customers_sample' => $customers->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->customer_name,
                        'customer_level' => $customer->customer_level,
                        'customer_scale' => $customer->customer_scale,
                        'level_relation' => $customer->customerLevel ? $customer->customerLevel->toArray() : null,
                        'scale_relation' => $customer->customerScale ? $customer->customerScale->toArray() : null,
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '测试失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取客户业务员信息
     */
    public function getCustomerBusinessPersons(Request $request)
    {
        try {
            // 支持单个customer_id或customer_ids数组
            $customerIds = $request->input('customer_ids', []);
            $customerId = $request->input('customer_id');

            if ($customerId) {
                $customerIds = [$customerId];
            }

            if (empty($customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '客户ID不能为空'
                ], 400);
            }

            $businessPersons = [];

            // 如果是单个客户，返回该客户的所有业务相关人员
            if (count($customerIds) === 1) {
                $customerId = $customerIds[0];

                // 从 customer_related_persons 表获取业务员
                $relatedPersons = \App\Models\CustomerRelatedPerson::with(['relatedBusinessPerson.department'])
                    ->where('customer_id', $customerId)
                    ->where('person_type', '业务员')
                    ->get();

                foreach ($relatedPersons as $person) {
                    if ($person->relatedBusinessPerson) {
                        $businessPersons[] = [
                            'id' => $person->relatedBusinessPerson->id,
                            'name' => $person->relatedBusinessPerson->real_name,
                            'department' => $person->relatedBusinessPerson->department->department_name ?? '',
                            'phone' => $person->relatedBusinessPerson->phone ?? '',
                            'email' => $person->relatedBusinessPerson->email ?? '',
                            'type' => '业务员',
                            'role' => '业务人员'
                        ];
                    }
                }

                // 如果没有从 customer_related_persons 表找到业务员，则从客户表的传统字段获取
                if (empty($businessPersons)) {
                    $customer = Customer::with(['businessPerson', 'businessAssistant', 'businessPartner'])
                        ->find($customerId);

                    if ($customer) {
                        // 添加业务人员
                        if ($customer->businessPerson) {
                            $businessPersons[] = [
                                'id' => $customer->businessPerson->id,
                                'name' => $customer->businessPerson->real_name,
                                'department' => $customer->businessPerson->department->name ?? '',
                                'phone' => $customer->businessPerson->phone,
                                'email' => $customer->businessPerson->email,
                                'type' => '业务员',
                                'role' => '业务人员'
                            ];
                        }
                    }
                }
            } else {
                // 多个客户的情况，保持原有逻辑
                $customers = Customer::whereIn('id', $customerIds)->get();
                $userIds = [];

                foreach ($customers as $customer) {
                    if ($customer->business_person_id) {
                        $userIds[] = $customer->business_person_id;
                    }
                    if ($customer->business_assistant_id) {
                        $userIds[] = $customer->business_assistant_id;
                    }
                    if ($customer->business_partner_id) {
                        $userIds[] = $customer->business_partner_id;
                    }
                }

                // 获取用户信息
                $users = User::whereIn('id', array_unique($userIds))->get()->keyBy('id');

                foreach ($customers as $customer) {
                    if ($customer->business_person_id && isset($users[$customer->business_person_id])) {
                        $businessPersons[] = [
                            'id' => $customer->business_person_id,
                            'name' => $users[$customer->business_person_id]->real_name,
                            'role' => '业务人员'
                        ];
                    }
                    if ($customer->business_assistant_id && isset($users[$customer->business_assistant_id])) {
                        $businessPersons[] = [
                            'id' => $customer->business_assistant_id,
                            'name' => $users[$customer->business_assistant_id]->real_name,
                            'role' => '业务助理'
                        ];
                    }
                    if ($customer->business_partner_id && isset($users[$customer->business_partner_id])) {
                        $businessPersons[] = [
                            'id' => $customer->business_partner_id,
                            'name' => $users[$customer->business_partner_id]->real_name,
                            'role' => '业务伙伴'
                        ];
                    }
                }

                // 去重
                $uniquePersons = [];
                $seen = [];
                foreach ($businessPersons as $person) {
                    $key = $person['id'] . '_' . $person['role'];
                    if (!isset($seen[$key])) {
                        $uniquePersons[] = $person;
                        $seen[$key] = true;
                    }
                }

                $businessPersons = $uniquePersons;
            }

            return response()->json([
                'success' => true,
                'data' => $businessPersons,
                'message' => '获取业务员信息成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取业务员信息失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取客户等级名称
     * 
     * 获取客户等级的显示名称，支持多种数据源：
     * 1. 优先从关联关系 customerLevel 获取
     * 2. 如果关联关系为空，直接查询 CustomerLevel 表
     * 3. 如果查询失败，使用默认映射表
     * 
     * @param \App\Models\Customer $customer 客户对象
     * @return string 客户等级名称
     */
    private function getCustomerLevelName($customer)
    {
        // 首先尝试从关联关系获取
        if ($customer->customerLevel) {
            return $customer->customerLevel->level_name;
        }

        // 如果关联关系没有数据，尝试直接查询
        if ($customer->customer_level) {
            $level = \App\Models\CustomerLevel::find($customer->customer_level);
            if ($level) {
                return $level->level_name;
            }

            // 降级处理：根据数字返回默认名称
            $levelMap = [
                1 => '重要客户',
                2 => '一般客户',
                3 => '潜在客户'
            ];
            return $levelMap[$customer->customer_level] ?? '等级' . $customer->customer_level;
        }

        return '';
    }

    /**
     * 获取客户规模名称
     * 
     * 获取客户规模的显示名称，支持多种数据源：
     * 1. 优先从关联关系 customerScale 获取
     * 2. 如果关联关系为空，直接查询 CustomerScale 表
     * 3. 如果查询失败，使用默认映射表
     * 
     * @param \App\Models\Customer $customer 客户对象
     * @return string 客户规模名称
     */
    private function getCustomerScaleName($customer)
    {
        // 首先尝试从关联关系获取
        if ($customer->customerScale) {
            return $customer->customerScale->scale_name;
        }

        // 如果关联关系没有数据，尝试直接查询
        if ($customer->customer_scale) {
            $scale = \App\Models\CustomerScale::find($customer->customer_scale);
            if ($scale) {
                return $scale->scale_name;
            }

            // 降级处理：根据数字返回默认名称
            $scaleMap = [
                1 => '大型企业',
                2 => '中型企业',
                3 => '小微企业',
                4 => '初创企业',
                5 => '央企',
                6 => '国企'
            ];
            return $scaleMap[$customer->customer_scale] ?? '规模' . $customer->customer_scale;
        }

        return '';
    }

    /**
     * 修复客户等级和规模数据
     */
    public function fixLevelAndScaleData()
    {
        try {
            // 确保配置表有数据
            $this->ensureConfigData();

            // 获取所有客户
            $customers = Customer::all();
            $fixedCount = 0;

            foreach ($customers as $customer) {
                $updated = false;

                // 修复客户等级
                if ($customer->customer_level && !is_numeric($customer->customer_level)) {
                    // 如果是字符串，尝试映射到ID
                    $levelMap = [
                        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4,
                        '重要' => 1, '一般' => 2, '潜在' => 3
                    ];
                    if (isset($levelMap[$customer->customer_level])) {
                        $customer->customer_level = $levelMap[$customer->customer_level];
                        $updated = true;
                    }
                }

                // 修复客户规模
                if ($customer->customer_scale && !is_numeric($customer->customer_scale)) {
                    // 如果是字符串，尝试映射到ID
                    $scaleMap = [
                        '大型' => 1, '中型' => 2, '小微' => 3,
                        '初创' => 4, '央企' => 5, '国企' => 6
                    ];
                    if (isset($scaleMap[$customer->customer_scale])) {
                        $customer->customer_scale = $scaleMap[$customer->customer_scale];
                        $updated = true;
                    }
                }

                if ($updated) {
                    $customer->save();
                    $fixedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "数据修复完成，共修复 {$fixedCount} 条记录",
                'data' => [
                    'fixed_count' => $fixedCount,
                    'total_customers' => $customers->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '数据修复失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 确保配置数据存在
     * 
     * 检查并创建必要的配置数据，包括客户等级和客户规模
     * 如果相关配置表为空，则自动创建默认数据
     * 
     * 创建的默认数据：
     * - 客户等级：重要客户、一般客户、潜在客户
     * - 客户规模：大型企业、中型企业、小微企业、初创企业、央企、国企
     * 
     * @return void
     */
    private function ensureConfigData()
    {
        // 确保客户等级数据
        if (\App\Models\CustomerLevel::count() == 0) {
            $levels = [
                ['level_name' => '重要客户', 'level_code' => 'IMPORTANT', 'sort' => 1, 'sort_order' => 1, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['level_name' => '一般客户', 'level_code' => 'GENERAL', 'sort' => 2, 'sort_order' => 2, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['level_name' => '潜在客户', 'level_code' => 'POTENTIAL', 'sort' => 3, 'sort_order' => 3, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1]
            ];

            foreach ($levels as $level) {
                \App\Models\CustomerLevel::create($level);
            }
        }

        // 确保客户规模数据
        if (\App\Models\CustomerScale::count() == 0) {
            $scales = [
                ['scale_name' => '大型企业', 'sort' => 1, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['scale_name' => '中型企业', 'sort' => 2, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['scale_name' => '小微企业', 'sort' => 3, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['scale_name' => '初创企业', 'sort' => 4, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['scale_name' => '央企', 'sort' => 5, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1],
                ['scale_name' => '国企', 'sort' => 6, 'is_valid' => 1, 'created_by' => 1, 'updated_by' => 1]
            ];

            foreach ($scales as $scale) {
                \App\Models\CustomerScale::create($scale);
            }
        }
    }

    /**
     * 检查信用代码唯一性
     */
    public function checkCustomerCodeUnique(Request $request)
    {
        try {
            $customerCode = $request->input('customerCode');
            $customerId = $request->input('customerId'); // 编辑时排除自己

            if (empty($customerCode)) {
                return response()->json([
                    'success' => false,
                    'message' => '信用代码不能为空'
                ]);
            }

            $query = Customer::where('credit_code', $customerCode);

            // 如果是编辑模式，排除当前客户
            if ($customerId) {
                $query->where('id', '!=', $customerId);
            }

            $exists = $query->exists();

            return response()->json([
                'success' => !$exists,
                'message' => $exists ? '该信用代码已存在' : '信用代码可用',
                'data' => [
                    'unique' => !$exists
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '检查失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取客户业务员显示文本
     * 
     * 获取客户关联的所有业务员姓名，并以逗号分隔的字符串形式返回
     * 
     * 查找逻辑：
     * 1. 优先从 customer_related_persons 关联表获取业务员
     * 2. 如果关联表为空，使用传统的 business_person_id 字段
     * 
     * @param \App\Models\Customer $customer 客户对象
     * @return string 业务员姓名列表（逗号分隔）
     */
    private function getBusinessPersonsDisplay($customer)
    {
        $businessPersons = [];

        // 从 customer_related_persons 表获取业务员
        if ($customer->businessPersons && $customer->businessPersons->count() > 0) {
            foreach ($customer->businessPersons as $relation) {
                if ($relation->relatedBusinessPerson) {
                    $businessPersons[] = $relation->relatedBusinessPerson->real_name;
                }
            }
        }

        // 如果没有从关联表找到，则使用传统字段
        if (empty($businessPersons) && $customer->businessPerson) {
            $businessPersons[] = $customer->businessPerson->real_name;
        }

        return implode(', ', $businessPersons);
    }

    /**
     * 从合同项目记录创建正式项目
     */
    public function createCaseFromRecord(Request $request, $recordId)
    {
        try {
            DB::beginTransaction();

            // 获取合同项目记录
            $record = \App\Models\ContractCaseRecord::find($recordId);
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '项目记录不存在'
                ], 404);
            }

            // 检查是否已经创建过case
            if ($record->case_id) {
                return response()->json([
                    'success' => true,
                    'data' => ['case_id' => $record->case_id],
                    'message' => '项目已存在'
                ]);
            }

            // 生成项目编码
            $caseCode = $this->generateCaseCode($record->case_type);

            // 创建cases记录
            $caseData = [
                // 必填字段
                'case_code' => $caseCode,
                'case_name' => $record->case_name ?: '未命名项目',
                'customer_id' => $record->customer_id,
                'case_type' => $record->case_type,
                'case_status' => \App\Models\Cases::STATUS_DRAFT,

                // 可选字段
                'contract_id' => $record->contract_id,
                'case_subtype' => $record->case_subtype,
                'application_type' => $record->application_type,
                'country_code' => $record->country_code ?: 'CN',
                'presale_support' => $record->presale_support,
                'tech_leader' => $record->tech_leader,
                'tech_contact' => $record->tech_contact,
                'trademark_category' => $record->trademark_category,
                'is_authorized' => $record->is_authorized ?: 0,
                'tech_service_name' => $record->tech_service_name,
                'project_no' => $record->project_no,
                'application_no' => $record->application_no,
                'registration_no' => $record->registration_no,
                'acceptance_no' => $record->acceptance_no,
                'product_id' => $record->product_id,
                'case_description' => $record->case_description,
                'priority_level' => 3, // 默认低优先级
                'estimated_cost' => $record->estimated_cost ?: 0,
                'service_fee' => $record->service_fee ?: 0,
                'official_fee' => $record->official_fee ?: 0,

                // 系统字段
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $case = \App\Models\Cases::create($caseData);

            // 更新记录中的case_id和状态
            $record->update([
                'case_id' => $case->id,
                'case_status' => ContractCaseRecord::STATUS_COMPLETED // 设置为已完成状态，表示已立项，不再显示在待立项列表中
            ]);

            \Log::info('立项成功', [
                'record_id' => $record->id,
                'case_id' => $case->id,
                'old_status' => ContractCaseRecord::STATUS_TO_BE_FILED,
                'new_status' => ContractCaseRecord::STATUS_COMPLETED
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'case_id' => $case->id,
                    'record_id' => $record->id,
                    'case_code' => $case->case_code
                ],
                'message' => '项目创建成功'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('创建项目失败: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '创建项目失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成项目编码
     * 
     * 根据项目类型生成唯一的项目编码
     * 
     * 编码格式：前缀 + 日期 + 4位序号
     * 例如：PAT202501180001
     * 
     * 项目类型前缀映射：
     * - 1: PAT (专利)
     * - 2: TRA (商标)
     * - 3: COP (版权)
     * - 4: SER (科服)
     * - 其他: CAS (通用)
     * 
     * @param int $caseType 项目类型
     * @return string 生成的项目编码
     */
    private function generateCaseCode($caseType)
    {
        $prefixMap = [
            1 => 'PAT', // 专利
            2 => 'TRA', // 商标
            3 => 'COP', // 版权
            4 => 'SER'  // 科服
        ];

        $prefix = $prefixMap[$caseType] ?? 'CAS';
        $date = date('Ymd');

        // 获取今日最大编号
        $maxCode = DB::table('cases')
            ->where('case_code', 'like', $prefix . $date . '%')
            ->orderBy('case_code', 'desc')
            ->value('case_code');

        if ($maxCode) {
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            $number = 1;
        }

        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 撤销立项 - 将项目记录状态改回待立项
     */
    public function cancelCaseFromRecord($recordId)
    {
        try {
            DB::beginTransaction();

            $record = ContractCaseRecord::findOrFail($recordId);

            // 检查记录状态，只有待立项状态的记录才能立项
            if ($record->case_status !== ContractCaseRecord::STATUS_TO_BE_FILED) {
                return response()->json([
                    'success' => false,
                    'message' => '该记录不是待立项状态，无法立项'
                ], 400);
            }

            // 检查是否已经立项
            if ($record->case_id) {
                return response()->json([
                    'success' => false,
                    'message' => '该记录已经立项，无法重复立项'
                ], 400);
            }

            // 检查是否已经立项
            if (!$record->case_id) {
                return response()->json([
                    'success' => false,
                    'message' => '该记录尚未立项'
                ], 400);
            }

            // 删除关联的cases记录（可选，或者只是标记为删除）
            if ($record->case_id) {
                Cases::where('id', $record->case_id)->delete();
            }

            // 将记录状态改回待立项
            $record->update([
                'case_id' => null,
                'case_status' => ContractCaseRecord::STATUS_TO_BE_FILED // 2 - 待立项
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '撤销立项成功'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('撤销立项失败: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '撤销立项失败：' . $e->getMessage()
            ], 500);
        }
    }
}
