<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechServiceRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TechServiceRegionsController extends Controller
{
    /**
     * 获取科技服务地区列表
     */
    public function index(Request $request)
    {
        try {
            $query = TechServiceRegion::query();

            // 申请类型筛选
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $query->where('apply_type', $request->apply_type);
            }

            // 科技服务名称筛选
            if ($request->has('service_name') && !empty($request->service_name)) {
                $query->where('service_name', 'like', '%' . $request->service_name . '%');
            }

            // 主管地筛选
            if ($request->has('main_area') && !empty($request->main_area)) {
                $query->where('main_area', 'like', '%' . $request->main_area . '%');
            }

            // 项目年份筛选
            if ($request->has('project_year') && !empty($request->project_year)) {
                $query->where('project_year', $request->project_year);
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('service_name', 'like', "%{$keyword}%")
                      ->orWhere('main_area', 'like', "%{$keyword}%")
                      ->orWhere('project_year', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            // 分页参数
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $total = $query->count();
            $list = $query->ordered()
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取科技服务地区列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (\Exception $e) {
            return json_error('获取科技服务地区列表失败：' . $e->getMessage());
        }
    }

    /**
     * 获取树形结构数据
     */
    public function getTreeData(Request $request)
    {
        try {
            $query = TechServiceRegion::enabled();

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('service_name', 'like', "%{$keyword}%")
                      ->orWhere('main_area', 'like', "%{$keyword}%")
                      ->orWhere('project_year', 'like', "%{$keyword}%");
                });
            }

            $list = $query->ordered()->get();

            // 构建三级树形结构：申请类型 -> 科技服务名称 -> 科服地区
            $grouped = [];
            foreach ($list as $item) {
                $applyType = $item->apply_type ?: '其他';
                $serviceName = $item->service_name ?: '未命名';
                
                if (!isset($grouped[$applyType])) {
                    $grouped[$applyType] = [];
                }
                if (!isset($grouped[$applyType][$serviceName])) {
                    $grouped[$applyType][$serviceName] = [];
                }
                $grouped[$applyType][$serviceName][] = $item;
            }

            $tree = [];
            $level1Id = 1;
            foreach ($grouped as $applyType => $services) {
                $level1Node = [
                    'id' => "apply-{$level1Id}",
                    'label' => $applyType,
                    'level' => 1,
                    'type' => 'apply_type',
                    'children' => []
                ];

                $level2Id = 1;
                foreach ($services as $serviceName => $regions) {
                    $level2Node = [
                        'id' => "service-{$level1Id}-{$level2Id}",
                        'label' => $serviceName,
                        'level' => 2,
                        'type' => 'service_name',
                        'apply_type' => $applyType,
                        'children' => []
                    ];

                    foreach ($regions as $region) {
                        $level3Node = [
                            'id' => $region->id,
                            'label' => "{$region->main_area} {$region->project_year}",
                            'level' => 3,
                            'type' => 'region',
                            'apply_type' => $applyType,
                            'service_name' => $serviceName,
                            'record' => $region
                        ];
                        $level2Node['children'][] = $level3Node;
                    }

                    $level1Node['children'][] = $level2Node;
                    $level2Id++;
                }

                $tree[] = $level1Node;
                $level1Id++;
            }

            return json_success('获取树形数据成功', $tree);

        } catch (\Exception $e) {
            return json_error('获取树形数据失败：' . $e->getMessage());
        }
    }

    /**
     * 创建科技服务地区
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apply_type' => 'required|string|max:100',
            'service_name' => 'required|string|max:200',
            'main_area' => 'required|string|max:100',
            'project_year' => 'required|string|max:10',
            'service_level' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'batch_number' => 'nullable|integer|min:1',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return json_error('参数验证失败', $validator->errors());
        }

        try {
            $data = $request->all();
            $data['updater'] = '系统管理员'; // 这里可以从认证用户获取
            $data['status'] = 1;
            $data['is_valid'] = $data['is_valid'] ?? true;
            $data['sort_order'] = $data['sort_order'] ?? 1;
            $data['batch_number'] = $data['batch_number'] ?? 1;

            $region = TechServiceRegion::create($data);

            return json_success('创建科技服务地区成功', $region);

        } catch (\Exception $e) {
            return json_error('创建科技服务地区失败：' . $e->getMessage());
        }
    }

    /**
     * 获取科技服务地区详情
     */
    public function show($id)
    {
        try {
            $region = TechServiceRegion::findOrFail($id);
            return json_success('获取科技服务地区详情成功', $region);

        } catch (\Exception $e) {
            return json_error('获取科技服务地区详情失败：' . $e->getMessage());
        }
    }

    /**
     * 更新科技服务地区
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'apply_type' => 'required|string|max:100',
            'service_name' => 'required|string|max:200',
            'main_area' => 'required|string|max:100',
            'project_year' => 'required|string|max:10',
            'service_level' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'batch_number' => 'nullable|integer|min:1',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return json_error('参数验证失败', $validator->errors());
        }

        try {
            $region = TechServiceRegion::findOrFail($id);
            
            $data = $request->all();
            $data['updater'] = '系统管理员'; // 这里可以从认证用户获取
            
            $region->update($data);

            return json_success('更新科技服务地区成功', $region);

        } catch (\Exception $e) {
            return json_error('更新科技服务地区失败：' . $e->getMessage());
        }
    }

    /**
     * 删除科技服务地区
     */
    public function destroy($id)
    {
        try {
            $region = TechServiceRegion::findOrFail($id);
            
            // 检查是否有关联的科技服务事项
            $itemCount = $region->techServiceItems()->count();
            if ($itemCount > 0) {
                return json_error('该科技服务地区下还有 ' . $itemCount . ' 个处理事项，无法删除');
            }
            
            $region->delete();

            return json_success('删除科技服务地区成功');

        } catch (\Exception $e) {
            return json_error('删除科技服务地区失败：' . $e->getMessage());
        }
    }
}
