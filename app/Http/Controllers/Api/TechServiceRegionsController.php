<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechServiceRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TechServiceRegionsController extends Controller
{
    /**
     * 功能: 获取科技服务地区列表，支持多条件筛选与分页
     * 请求参数:
     * - apply_type(string, 可选): 申请类型精确筛选
     * - service_name(string, 可选): 科技服务名称模糊匹配
     * - main_area(string, 可选): 主管地模糊匹配
     * - project_year(string, 可选): 项目年份精确筛选
     * - status(int, 可选): 状态筛选（0=禁用，1=启用）
     * - keyword(string, 可选): 关键词匹配名称/主管地/年份/描述
     * - page(int, 可选): 页码，默认1
     * - limit(int, 可选): 每页数量，默认20
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): {list(array<object>), total(int), page(int), limit(int)}
     * 接口: GET /data-config/tech-service-regions
     */
    public function index(Request $request)
    {
        // 步骤说明：解析筛选条件 -> 构建查询条件 -> 统计总数 -> 有序分页查询 -> 返回统一结构
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
     * 功能: 获取启用状态的科技服务地区树形数据（三级：申请类型→服务名称→地区记录）
     * 请求参数:
     * - keyword(string, 可选): 关键词匹配服务名称/主管地/项目年份
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(array<object>): 树形节点数组，节点含 {id,label,level,type,children,...}
     * 接口: GET /data-config/tech-service-regions/tree
     */
    public function getTreeData(Request $request)
    {
        // 步骤说明：查询启用记录 -> 关键词筛选 -> 构建分组 -> 生成层级树 -> 返回结果
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
     * 功能: 创建科技服务地区记录
     * 请求参数:
     * - apply_type(string, 必填): 申请类型，<=100
     * - service_name(string, 必填): 科技服务名称，<=200
     * - main_area(string, 必填): 主管地，<=100
     * - project_year(string, 必填): 项目年份，<=10
     * - service_level(string, 可选): 服务层级，<=50
     * - deadline(date, 可选): 截止日期
     * - batch_number(int, 可选): 批次号，>=1
     * - is_valid(bool, 可选): 是否有效，默认 true
     * - sort_order(int, 可选): 排序，>=1，默认1
     * - description(string, 可选): 描述
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 创建成功的记录
     * 接口: POST /data-config/tech-service-regions
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
     * 功能: 获取科技服务地区详情
     * 请求参数:
     * - id(int, 必填): 路径参数，记录ID
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 记录详情
     * 接口: GET /data-config/tech-service-regions/{id}
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
     * 功能: 更新科技服务地区记录
     * 请求参数:
     * - id(int, 必填): 路径参数，记录ID
     * - 其余字段同创建接口，均为可选
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 更新后的记录
     * 接口: PUT /data-config/tech-service-regions/{id}
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
     * 功能: 删除科技服务地区记录（存在关联处理事项时禁止删除）
     * 请求参数:
     * - id(int, 必填): 路径参数，记录ID
     * 返回参数:
     * - JSON: {code, message}
     * 接口: DELETE /data-config/tech-service-regions/{id}
     */
    public function destroy($id)
    {
        // 步骤说明：检索记录 -> 统计关联事项 -> 条件限制删除 -> 执行删除 -> 返回结果
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
