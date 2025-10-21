<?php

namespace App\Http\Controllers\Api;

use App\Models\TechServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TechServiceItemsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return TechServiceItem::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'tech_service_region_id' => 'required|integer|exists:tech_service_regions,id',
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'expected_start_date' => 'nullable|date',
            'internal_deadline' => 'nullable|date',
            'official_deadline' => 'nullable|date',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:tech_service_items,code,' . $id;
        } else {
            $rules['code'] .= '|unique:tech_service_items,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'tech_service_region_id.required' => '科技服务地区不能为空',
            'tech_service_region_id.exists' => '科技服务地区不存在',
            'name.required' => '处理事项名称不能为空',
            'code.required' => '处理事项编码不能为空',
            'code.unique' => '处理事项编码已存在',
        ]);
    }

    /**
     * 根据科技服务类型ID获取处理事项列表
     */
    public function getByTypeId(Request $request, $typeId)
    {
        try {
            $query = TechServiceItem::where('tech_service_type_id', $typeId);

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            $items = $query->with(['techServiceType', 'techServiceRegion'])->ordered()->get();

            return json_success('获取处理事项列表成功', $items);

        } catch (\Exception $e) {
            return json_error('获取处理事项列表失败：' . $e->getMessage());
        }
    }

    /**
     * 根据科技服务地区ID获取处理事项列表
     */
    public function getByRegionId(Request $request, $regionId)
    {
        try {
            $query = TechServiceItem::where('tech_service_region_id', $regionId);

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            $items = $query->with(['techServiceType', 'techServiceRegion'])->ordered()->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tech_service_region_id' => $item->tech_service_region_id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'description' => $item->description,
                    'expected_start_date' => $item->expected_start_date ? $item->expected_start_date->format('Y-m-d') : null,
                    'internal_deadline' => $item->internal_deadline ? $item->internal_deadline->format('Y-m-d') : null,
                    'official_deadline' => $item->official_deadline ? $item->official_deadline->format('Y-m-d') : null,
                    'status' => $item->status,
                    'status_text' => $item->status_text,
                    'sort_order' => $item->sort_order,
                    'updater' => $item->updater,
                    'updated_at' => $item->updated_at,
                    'tech_service_region' => $item->techServiceRegion ? [
                        'id' => $item->techServiceRegion->id,
                        'apply_type' => $item->techServiceRegion->apply_type,
                        'service_name' => $item->techServiceRegion->service_name,
                        'main_area' => $item->techServiceRegion->main_area,
                        'project_year' => $item->techServiceRegion->project_year
                    ] : null
                ];
            });

            return json_success('获取处理事项列表成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 重写index方法，支持按科技服务类型筛选
     */
    public function index(Request $request)
    {
        try {
            $query = TechServiceItem::query();

            // 按科技服务地区筛选
            if ($request->has('tech_service_region_id') && !empty($request->tech_service_region_id)) {
                $query->where('tech_service_region_id', $request->tech_service_region_id);
            }

            // 兼容旧的科技服务类型筛选（如果需要）
            if ($request->has('tech_service_type_id') && !empty($request->tech_service_type_id)) {
                $query->where('tech_service_type_id', $request->tech_service_type_id);
            }

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

            // 分页参数
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            // 排序
            $query->with(['techServiceRegion', 'techServiceType'])->ordered();

            // 分页查询
            $result = $query->paginate($limit, ['*'], 'page', $page);

            $data = $result->items();
            $data = collect($data)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tech_service_region_id' => $item->tech_service_region_id,
                    'tech_service_type_id' => $item->tech_service_type_id, // 保留兼容性
                    'name' => $item->name,
                    'code' => $item->code,
                    'description' => $item->description,
                    'expected_start_date' => $item->expected_start_date ? $item->expected_start_date->format('Y-m-d') : null,
                    'internal_deadline' => $item->internal_deadline ? $item->internal_deadline->format('Y-m-d') : null,
                    'official_deadline' => $item->official_deadline ? $item->official_deadline->format('Y-m-d') : null,
                    'status' => $item->status,
                    'status_text' => $item->status_text,
                    'sort_order' => $item->sort_order,
                    'updater' => $item->updater,
                    'updated_at' => $item->updated_at,
                    'tech_service_region' => $item->techServiceRegion ? [
                        'id' => $item->techServiceRegion->id,
                        'apply_type' => $item->techServiceRegion->apply_type,
                        'service_name' => $item->techServiceRegion->service_name,
                        'main_area' => $item->techServiceRegion->main_area,
                        'project_year' => $item->techServiceRegion->project_year
                    ] : null,
                    'tech_service_type' => $item->techServiceType ? [
                        'id' => $item->techServiceType->id,
                        'name' => $item->techServiceType->name,
                        'apply_type' => $item->techServiceType->apply_type
                    ] : null
                ];
            });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'last_page' => $result->lastPage()
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }
}
