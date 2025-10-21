<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractService;
use App\Models\ContractAttachment;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class ContractController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * 获取合同列表
     */
    public function index(Request $request)
    {
        try {
            // 添加调试信息
            \Log::info('Contract index called with parameters:', $request->all());
            
            $query = Contract::with([
                'customer',
                'businessPerson',
                'technicalDirector',
                'partyAContact',
                'partyBSigner',
                'partyBCompany',
                'creator',
                'updater'
            ]);

            // 根据请求参数决定是否过滤用户
            if ($request->input('my_contracts_only', false)) {
                // 只获取当前用户创建的合同
                $user = $request->user();
                if ($user) {
                    $query->where('created_by', $user->id);
                }
            }

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%');
            }

            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%');
            }

            if ($request->filled('contract_code')) {
                $query->where('contract_code', 'like', '%' . $request->contract_code . '%');
            }

            if ($request->filled('service_type')) {
                $serviceType = $request->service_type;
                // 支持JSON数组搜索和字符串搜索
                $query->where(function ($q) use ($serviceType) {
                    // 如果是字符串，使用JSON_CONTAINS或直接匹配
                    $q->where('service_type', $serviceType)
                      ->orWhere('service_type', 'like', '%"' . $serviceType . '"%');
                });
            }

            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 待处理合同查询
            if ($request->input('my_pending_only', false)) {
                $user = $request->user();
                if ($user) {
                    // 查询当前用户有待处理任务的合同
                    $query->whereHas('workflowInstance', function ($q) use ($user) {
                        $q->where('status', \App\Models\WorkflowInstance::STATUS_PENDING)
                          ->whereHas('processes', function ($processQuery) use ($user) {
                              $processQuery->where('assignee_id', $user->id)
                                          ->where('action', \App\Models\WorkflowProcess::ACTION_PENDING);
                          });
                    });
                }
            }

            if ($request->filled('paper_status')) {
                $query->where('paper_status', $request->paper_status);
            }

            if ($request->filled('party_b_company_id')) {
                $query->where('party_b_company_id', $request->party_b_company_id);
            }

            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id);
            }

            // 业务人员搜索（支持姓名搜索）
            if ($request->filled('salesman')) {
                $query->whereHas('businessPerson', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->salesman . '%');
                });
            }

            if ($request->filled('technical_director_id')) {
                $query->where('technical_director_id', $request->technical_director_id);
            }

            if ($request->filled('party_a_contact')) {
                $query->whereHas('partyAContact', function ($q) use ($request) {
                    $q->where('contact_name', 'like', '%' . $request->party_a_contact . '%');
                });
            }

            if ($request->filled('party_a_phone')) {
                $query->where('party_a_phone', 'like', '%' . $request->party_a_phone . '%');
            }

            // 服务费范围搜索
            if ($request->filled('min_service_fee')) {
                $query->where('service_fee', '>=', $request->min_service_fee);
            }

            if ($request->filled('max_service_fee')) {
                $query->where('service_fee', '<=', $request->max_service_fee);
            }

            // 总金额范围搜索
            if ($request->filled('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }

            if ($request->filled('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }

            // 项目数量范围搜索
            if ($request->filled('min_case_count')) {
                $query->where('case_count', '>=', $request->min_case_count);
            }
            if ($request->filled('max_case_count')) {
                $query->where('case_count', '<=', $request->max_case_count);
            }

            // 技术主导搜索
            if ($request->filled('technical_director')) {
                $query->whereHas('technicalDirector', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->technical_director . '%');
                });
            }

            // 乙方签约人搜索
            if ($request->filled('party_b_signer')) {
                $query->whereHas('partyBSigner', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->party_b_signer . '%');
                });
            }

            // 签约日期范围
            if ($request->filled('sign_date_start')) {
                $startDate = $request->sign_date_start;
                \Log::info('Processing sign_date_start:', ['value' => $startDate]);
                
                // 验证日期格式
                try {
                    $date = \Carbon\Carbon::parse($startDate);
                    $query->where('signing_date', '>=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_start format:', ['value' => $startDate, 'error' => $e->getMessage()]);
                    // 跳过无效的日期
                }
            }

            if ($request->filled('sign_date_end')) {
                $endDate = $request->sign_date_end;
                \Log::info('Processing sign_date_end:', ['value' => $endDate]);
                
                // 验证日期格式
                try {
                    $date = \Carbon\Carbon::parse($endDate);
                    $query->where('signing_date', '<=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_end format:', ['value' => $endDate, 'error' => $e->getMessage()]);
                    // 跳过无效的日期
                }
            }

            // 创建人搜索
            if ($request->filled('creator')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->creator . '%');
                });
            }

            // 修改人搜索
            if ($request->filled('updater')) {
                $query->whereHas('updater', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->updater . '%');
                });
            }

            // 排序
            $sortField = $request->get('sort_field', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // 分页
            $pageSize = $request->get('page_size', 15);
            $contracts = $query->paginate($pageSize);

            return response()->json([
                'success' => true,
                'data' => $contracts,
                'message' => '获取成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同详情
     */
    public function show($id)
    {
        try {
            $contract = Contract::with([
                'customer',
                'businessPerson',
                'technicalDirector',
                'partyAContact',
                'partyBSigner',
                'partyBCompany',
                'services',
                'attachments.uploader',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => '获取成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 创建合同
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'contract_name' => 'required|string|max:200',
                'contract_type' => 'required|string|in:standard,non-standard',
                'service_type' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $contractType = $request->input('contract_type');
                        if ($contractType === 'standard') {
                            if (!is_string($value)) {
                                $fail('标准合同的服务类型必须为单个值');
                            }
                        } elseif ($contractType === 'non-standard') {
                            if (!is_array($value) || empty($value)) {
                                $fail('非标合同的服务类型必须为非空数组');
                            }
                        }
                    }
                ],
                'business_person_id' => 'nullable|integer|exists:users,id',
                'technical_director_id' => 'nullable|integer|exists:users,id',
                'party_a_contact_id' => 'nullable|integer|exists:customer_contacts,id',
                'party_a_address' => 'nullable|string',
                'party_b_signer_id' => 'nullable|integer|exists:users,id',
                'party_b_company_id' => 'nullable|integer|exists:our_companies,id',
                'signing_date' => 'nullable|date',
                'validity_start_date' => 'nullable|date',
                'validity_end_date' => 'nullable|date',
                'service_fee' => 'nullable|numeric|min:0',
                'official_fee' => 'nullable|numeric|min:0',
                'channel_fee' => 'nullable|numeric|min:0',
                'services' => 'nullable|array',
                'services.*.service_name' => 'required_with:services|string|max:200',
                'services.*.service_description' => 'nullable|string',
                'services.*.amount' => 'nullable|numeric|min:0',
                'services.*.official_fee' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();
            
            // 生成合同编号
            $data['contract_no'] = Contract::generateContractNo();
            
            // 处理服务类型格式
            if ($data['contract_type'] === 'standard' && is_array($data['service_type'])) {
                // 如果前端错误地发送了数组，取第一个元素
                $data['service_type'] = is_array($data['service_type']) ? $data['service_type'][0] : $data['service_type'];
            } elseif ($data['contract_type'] === 'non-standard' && is_string($data['service_type'])) {
                // 如果前端错误地发送了字符串，转换为数组
                $data['service_type'] = [$data['service_type']];
            }
            
            // 计算总金额
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $officialFee = floatval($data['official_fee'] ?? 0);
            $channelFee = floatval($data['channel_fee'] ?? 0);
            
            $data['total_service_fee'] = $serviceFee + $channelFee;
            $data['total_amount'] = $data['total_service_fee'] + $officialFee;
            
            // 处理有效期范围
            if ($request->filled('validity') && is_array($request->validity)) {
                $data['validity_start_date'] = $request->validity[0] ?? null;
                $data['validity_end_date'] = $request->validity[1] ?? null;
            }
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $contract = Contract::create($data);

            // 创建服务明细
            if (!empty($data['services'])) {
                foreach ($data['services'] as $index => $serviceData) {
                    $serviceData['contract_id'] = $contract->id;
                    $serviceData['sort_order'] = $index + 1;
                    ContractService::create($serviceData);
                }
            }

            // 不再自动启动工作流，改为返回工作流配置信息供前端选择
            $contractWorkflow = null;
            try {
                $contractWorkflow = Workflow::where('case_type', '合同')
                    ->where('status', 1)
                    ->first();
            } catch (\Exception $e) {
                \Log::warning('获取合同工作流配置失败', [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            // 准备返回数据，包含工作流配置信息
            $responseData = [
                'contract' => $contract->load(['services', 'workflowInstance']),
                'workflow_config' => null
            ];

            if ($contractWorkflow) {
                $responseData['workflow_config'] = [
                    'id' => $contractWorkflow->id,
                    'name' => $contractWorkflow->name,
                    'nodes' => $contractWorkflow->nodes
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新合同
     */
    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'contract_name' => 'required|string|max:200',
                'contract_type' => 'required|string|in:standard,non-standard',
                'service_type' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $contractType = $request->input('contract_type');
                        if ($contractType === 'standard') {
                            if (!is_string($value)) {
                                $fail('标准合同的服务类型必须为单个值');
                            }
                        } elseif ($contractType === 'non-standard') {
                            if (!is_array($value) || empty($value)) {
                                $fail('非标合同的服务类型必须为非空数组');
                            }
                        }
                    }
                ],
                'business_person_id' => 'nullable|integer|exists:users,id',
                'technical_director_id' => 'nullable|integer|exists:users,id',
                'party_a_contact_id' => 'nullable|integer|exists:customer_contacts,id',
                'party_a_address' => 'nullable|string',
                'party_b_signer_id' => 'nullable|integer|exists:users,id',
                'party_b_company_id' => 'nullable|integer|exists:our_companies,id',
                'signing_date' => 'nullable|date',
                'validity_start_date' => 'nullable|date',
                'validity_end_date' => 'nullable|date',
                'service_fee' => 'nullable|numeric|min:0',
                'official_fee' => 'nullable|numeric|min:0',
                'channel_fee' => 'nullable|numeric|min:0',
                'services' => 'nullable|array',
                'services.*.service_name' => 'required_with:services|string|max:200',
                'services.*.service_description' => 'nullable|string',
                'services.*.amount' => 'nullable|numeric|min:0',
                'services.*.official_fee' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();
            
            // 处理服务类型格式
            if ($data['contract_type'] === 'standard' && is_array($data['service_type'])) {
                // 如果前端错误地发送了数组，取第一个元素
                $data['service_type'] = is_array($data['service_type']) ? $data['service_type'][0] : $data['service_type'];
            } elseif ($data['contract_type'] === 'non-standard' && is_string($data['service_type'])) {
                // 如果前端错误地发送了字符串，转换为数组
                $data['service_type'] = [$data['service_type']];
            }
            
            // 计算总金额
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $officialFee = floatval($data['official_fee'] ?? 0);
            $channelFee = floatval($data['channel_fee'] ?? 0);
            
            $data['total_service_fee'] = $serviceFee + $channelFee;
            $data['total_amount'] = $data['total_service_fee'] + $officialFee;
            
            // 处理有效期范围
            if ($request->filled('validity') && is_array($request->validity)) {
                $data['validity_start_date'] = $request->validity[0] ?? null;
                $data['validity_end_date'] = $request->validity[1] ?? null;
            }
            
            // 设置更新人
            $data['updated_by'] = auth()->id();

            $contract->update($data);

            // 更新服务明细
            if (array_key_exists('services', $data)) {
                // 删除原有服务明细
                $contract->services()->delete();
                
                // 创建新的服务明细
                if (!empty($data['services'])) {
                    foreach ($data['services'] as $index => $serviceData) {
                        $serviceData['contract_id'] = $contract->id;
                        $serviceData['sort_order'] = $index + 1;
                        ContractService::create($serviceData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $contract->load(['services'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同
     */
    public function destroy($id)
    {
        try {
            $contract = Contract::findOrFail($id);

            // 检查是否有关联的项目
            if ($contract->cases()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同下存在项目，无法删除'
                ], 400);
            }

            DB::beginTransaction();

            // 删除服务明细
            $contract->services()->delete();

            // 删除附件
            $contract->attachments()->delete();

            // 删除合同
            $contract->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传合同附件
     */
    public function uploadAttachment(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:51200', // 最大50MB
                'file_type' => 'required|string|max:50',
                'file_sub_type' => 'nullable|string|max:50',
                'file_desc' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('contracts/' . $contract->id, $fileName, 'public');

            $attachment = ContractAttachment::create([
                'contract_id' => $contract->id,
                'file_type' => $request->file_type,
                'file_sub_type' => $request->file_sub_type,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'uploader_id' => auth()->id(),
                'upload_time' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => $attachment->load('uploader')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同附件
     */
    public function deleteAttachment($id, $attachmentId)
    {
        try {
            $contract = Contract::findOrFail($id);
            $attachment = $contract->attachments()->findOrFail($attachmentId);

            // 删除文件
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // 删除记录
            $attachment->delete();

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
     * 下载合同附件
     */
    public function downloadAttachment($id, $attachmentId)
    {
        try {
            $contract = Contract::findOrFail($id);
            $attachment = $contract->attachments()->findOrFail($attachmentId);

            if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $attachment->file_path);
            return response()->download($filePath, $attachment->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '下载失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 导出合同列表为Excel
     */
    public function export(Request $request)
    {
        try {
            $query = Contract::with([
                'customer',
                'businessPerson',
                'technicalDirector',
                'partyAContact',
                'partyBSigner',
                'partyBCompany',
                'creator',
                'updater'
            ]);

            // 根据请求参数决定是否过滤用户
            if ($request->input('my_contracts_only', false)) {
                // 只获取当前用户创建的合同
                $user = $request->user();
                if ($user) {
                    $query->where('created_by', $user->id);
                }
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }
            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%');
            }
            if ($request->filled('contract_code')) {
                $query->where('contract_code', 'like', '%' . $request->contract_code . '%');
            }
            if ($request->filled('service_type')) {
                $query->where('service_type', $request->service_type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('paper_status')) {
                $query->where('paper_status', $request->paper_status);
            }
            if ($request->filled('party_b_company_id')) {
                $query->where('party_b_company_id', $request->party_b_company_id);
            }
            if ($request->filled('min_service_fee')) {
                $query->where('service_fee', '>=', $request->min_service_fee);
            }
            if ($request->filled('max_service_fee')) {
                $query->where('service_fee', '<=', $request->max_service_fee);
            }
            if ($request->filled('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }
            if ($request->filled('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }
            if ($request->filled('sign_date_start')) {
                try {
                    $date = \Carbon\Carbon::parse($request->sign_date_start);
                    $query->where('signing_date', '>=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_start format in export:', ['value' => $request->sign_date_start, 'error' => $e->getMessage()]);
                }
            }
            if ($request->filled('sign_date_end')) {
                try {
                    $date = \Carbon\Carbon::parse($request->sign_date_end);
                    $query->where('signing_date', '<=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_end format in export:', ['value' => $request->sign_date_end, 'error' => $e->getMessage()]);
                }
            }

            $contracts = $query->orderBy('created_at', 'desc')->limit(5000)->get();

            $selectedFields = $request->input('selected_fields', '[]');
            $fields = json_decode($selectedFields, true) ?: [];
            $fieldKeys = collect($fields)->pluck('prop')->all();

            $mapRow = function ($c) {
                return [
                    '合同流水号' => $c->contract_no,
                    '合同编号' => $c->contract_no,
                    '合同代码' => $c->contract_code,
                    '合同名称' => $c->contract_name,
                    '业务服务类型' => $c->service_type,
                    '合同摘要' => $c->summary,
                    '客户名称' => optional($c->customer)->customer_name,
                    '对应商机号' => $c->opportunity_no,
                    '对应商机名称' => $c->opportunity_name,
                    '合同服务费' => $c->total_service_fee ?? $c->service_fee,
                    '总金额' => $c->total_amount,
                    '项目数量' => $c->case_count,
                    '甲方联系人' => optional($c->partyAContact)->contact_name,
                    '联系电话' => $c->party_a_phone,
                    '甲方签约地址' => $c->party_a_address,
                    '业务人员' => optional($c->businessPerson)->name,
                    '技术主导' => optional($c->technicalDirector)->name,
                    '乙方签约人' => optional($c->partyBSigner)->name,
                    '乙方手机' => $c->party_b_phone,
                    '签约日期' => $c->signing_date,
                    '创建人' => optional($c->creator)->name,
                    '创建时间' => optional($c->created_at)->toDateTimeString(),
                    '合同状态' => $c->status,
                    '最后处理时间' => $c->last_process_time,
                    '对接人部门' => $c->process_remark,
                    '纸件状态' => $c->paper_status,
                ];
            };

            $exportData = $contracts->map($mapRow)->all();

            if (!empty($fieldKeys)) {
                $titleMap = [
                    'contractNo' => '合同流水号',
                    'contractNumber' => '合同编号',
                    'contractCode' => '合同代码',
                    'contractName' => '合同名称',
                    'serviceType' => '业务服务类型',
                    'summary' => '合同摘要',
                    'customerName' => '客户名称',
                    'opportunityNo' => '对应商机号',
                    'opportunityName' => '对应商机名称',
                    'serviceFee' => '合同服务费',
                    'totalAmount' => '总金额',
                    'caseCount' => '项目数量',
                    'partyAContact' => '甲方联系人',
                    'partyAPhone' => '联系电话',
                    'partyAAddress' => '甲方签约地址',
                    'salesman' => '业务人员',
                    'technicalDirector' => '技术主导',
                    'partyBSigner' => '乙方签约人',
                    'partyBPhone' => '乙方手机',
                    'signDate' => '签约日期',
                    'creator' => '创建人',
                    'createTime' => '创建时间',
                    'status' => '合同状态',
                    'lastProcessTime' => '最后处理时间',
                    'processRemark' => '对接人部门',
                    'paperStatus' => '纸件状态',
                ];

                $exportData = array_map(function ($row) use ($fieldKeys, $titleMap) {
                    $newRow = [];
                    foreach ($fieldKeys as $prop) {
                        $title = $titleMap[$prop] ?? $prop;
                        if (array_key_exists($title, $row)) {
                            $newRow[$title] = $row[$title];
                        }
                    }
                    return $newRow;
                }, $exportData);
            }

            $filename = 'contracts_' . date('Ymd_His') . '.xlsx';
            return (new FastExcel($exportData))->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同工作流状态
     */
    public function workflowStatus($id)
    {
        try {
            $contract = Contract::with(['workflowInstance.workflow', 'workflowInstance.processes.assignee', 'workflowInstance.processes.processor'])
                ->findOrFail($id);

            $workflowInstance = $contract->workflowInstance;

            if (!$workflowInstance) {
                return response()->json([
                    'success' => true,
                    'message' => '获取成功',
                    'data' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $workflowInstance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取工作流状态失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动合同工作流（带处理人选择）
     */
    public function startWorkflow($id)
    {
        try {
            $contract = Contract::findOrFail($id);

            // 检查是否已有进行中的工作流
            if ($contract->hasPendingWorkflow()) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同已有进行中的工作流'
                ], 400);
            }

            // 获取合同工作流配置
            $contractWorkflow = Workflow::where('case_type', '合同')
                ->where('status', 1)
                ->first();

            if (!$contractWorkflow) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到合同工作流配置'
                ], 400);
            }

            $instance = $this->workflowService->startWorkflow(
                'contract',
                $contract->id,
                $contract->contract_name,
                $contractWorkflow->id,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => '工作流启动成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '启动工作流失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动合同工作流（带用户选择的处理人）
     */
    public function startWorkflowWithAssignees(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'selected_assignees' => 'required|array',
            'selected_assignees.*' => 'integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数错误',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $contract = Contract::findOrFail($id);

            // 检查是否已有进行中的工作流
            if ($contract->hasPendingWorkflow()) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同已有进行中的工作流'
                ], 400);
            }

            $instance = $this->workflowService->startWorkflowWithAssignees(
                'contract',
                $contract->id,
                $contract->contract_name,
                $request->workflow_id,
                auth()->id(),
                $request->selected_assignees
            );

            return response()->json([
                'success' => true,
                'message' => '工作流启动成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '工作流启动失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 重新发起合同工作流（用于退回到最初状态后的重新发起）
     */
    public function restartWorkflow($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            
            // 检查权限：只有创建人可以重新发起
            if ($contract->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => '只有合同创建人可以重新发起审批'
                ], 403);
            }

            // 检查当前工作流状态
            $currentInstance = $contract->workflowInstance()
                ->whereIn('status', [WorkflowInstance::STATUS_PENDING, WorkflowInstance::STATUS_REJECTED])
                ->first();

            if ($currentInstance) {
                // 取消现有工作流
                $currentInstance->update(['status' => WorkflowInstance::STATUS_CANCELLED]);
                Log::info('重新发起前取消现有工作流', [
                    'contract_id' => $contract->id,
                    'old_instance_id' => $currentInstance->id,
                    'user_id' => auth()->id()
                ]);
            }

            // 获取合同工作流配置
            $contractWorkflow = Workflow::where('case_type', '合同')
                ->where('status', 1)
                ->first();

            if (!$contractWorkflow) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到合同工作流配置'
                ], 400);
            }

            // 启动新的工作流
            $instance = $this->workflowService->startWorkflow(
                'contract',
                $contract->id,
                $contract->contract_name,
                $contractWorkflow->id,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => '工作流重新发起成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '重新发起工作流失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取所有合同的审核进度（管理员视角）
     */
    public function getContractProgress(Request $request)
    {
        try {
            $query = Contract::with(['creator', 'customer', 'workflowInstance.workflow', 'workflowInstance.processes.assignee', 'workflowInstance.processes.processor'])
                ->select(['id', 'contract_name', 'contract_type', 'customer_id', 'total_amount', 'created_by', 'created_at', 'status']);

            // 搜索条件
            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%');
            }

            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('workflow_status')) {
                $query->whereHas('workflowInstance', function ($q) use ($request) {
                    $q->where('status', $request->workflow_status);
                });
            }

            if ($request->filled('created_by')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('real_name', 'like', '%' . $request->created_by . '%')
                      ->orWhere('username', 'like', '%' . $request->created_by . '%');
                });
            }

            // 分页
            $pageSize = $request->get('page_size', 20);
            $contracts = $query->orderBy('created_at', 'desc')->paginate($pageSize);

            // 格式化数据
            $formattedData = $contracts->getCollection()->map(function ($contract) {
                $workflowInstance = $contract->workflowInstance;
                $currentProcess = null;
                $currentHandler = null;
                $lastUpdateTime = null;
                $stopDays = 0;

                if ($workflowInstance) {
                    // 获取当前处理节点信息
                    $currentProcess = $workflowInstance->processes
                        ->where('node_index', $workflowInstance->current_node_index)
                        ->where('action', 'pending')
                        ->first();

                    if ($currentProcess) {
                        $currentHandler = $currentProcess->assignee ? 
                            ($currentProcess->assignee->real_name ?: $currentProcess->assignee->username) : '待分配';
                    }

                    // 计算最后更新时间和停滞天数
                    $lastProcessedTask = $workflowInstance->processes
                        ->whereNotNull('processed_at')
                        ->sortByDesc('processed_at')
                        ->first();

                    if ($lastProcessedTask) {
                        $lastUpdateTime = $lastProcessedTask->processed_at;
                        $stopDays = now()->diffInDays(Carbon::parse($lastProcessedTask->processed_at));
                    } else {
                        $lastUpdateTime = $workflowInstance->created_at;
                        $stopDays = now()->diffInDays(Carbon::parse($workflowInstance->created_at));
                    }
                }

                return [
                    'id' => $contract->id,
                    'contract_name' => $contract->contract_name,
                    'contract_type' => $contract->contract_type,
                    'contract_status' => $this->getContractStatusText($contract->status),
                    'customer_name' => $contract->customer ? $contract->customer->customer_name : '',
                    'total_amount' => $contract->total_amount ? number_format($contract->total_amount, 2) : '0.00',
                    'created_by' => $contract->creator ? ($contract->creator->real_name ?: $contract->creator->username) : '',
                    'created_at' => $contract->created_at ? 
                        Carbon::parse($contract->created_at)->format('Y-m-d H:i:s') : '',
                    
                    // 工作流相关信息
                    'workflow_name' => $workflowInstance ? ($workflowInstance->workflow->name ?? '合同审批流程') : '未发起',
                    'workflow_status' => $workflowInstance ? $this->getWorkflowStatusText($workflowInstance->status) : '未发起',
                    'current_node' => $currentProcess ? $currentProcess->node_name : ($workflowInstance ? '已完成' : '未发起'),
                    'current_handler' => $currentHandler ?: '无',
                    'last_update_time' => $lastUpdateTime ? 
                        Carbon::parse($lastUpdateTime)->format('Y-m-d H:i:s') : '',
                    'stop_days' => $stopDays,
                    
                    // 工作流实例数据（用于详细展示）
                    'workflow_instance' => $workflowInstance
                ];
            });

            $contracts->setCollection($formattedData);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contracts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取合同进度失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同状态文本
     */
    private function getContractStatusText($status)
    {
        $statusMap = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已审核',
            'signed' => '已签订',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取工作流状态文本
     */
    private function getWorkflowStatusText($status)
    {
        $statusMap = [
            'pending' => '进行中',
            'completed' => '已完成',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取合同工作流状态
     */
    public function getWorkflowStatus($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $workflowStatus = $contract->getWorkflowStatus();

            if (!$workflowStatus) {
                return response()->json([
                    'success' => true,
                    'message' => '该合同暂无工作流',
                    'data' => null
                ]);
            }

            // 获取工作流详细信息
            $instance = \App\Models\WorkflowInstance::with(['workflow', 'processes.assignee', 'processes.processor'])
                ->find($workflowStatus['instance_id']);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'workflow_status' => $workflowStatus,
                    'workflow_instance' => $instance
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
     * 获取当前用户的待处理合同数量
     */
    public function getPendingCount()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未登录'
                ], 401);
            }

            // 查询当前用户有待处理任务的合同数量
            $count = Contract::whereHas('workflowInstance', function ($q) use ($user) {
                $q->where('status', \App\Models\WorkflowInstance::STATUS_PENDING)
                  ->whereHas('processes', function ($processQuery) use ($user) {
                      $processQuery->where('assignee_id', $user->id)
                                  ->where('action', \App\Models\WorkflowProcess::ACTION_PENDING);
                  });
            })->count();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }
}
