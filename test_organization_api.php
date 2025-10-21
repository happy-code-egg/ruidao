<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Department;

// 设置Laravel环境
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== 测试组织架构数据 ===\n";

try {
    // 获取所有用户和部门
    $users = User::select('id', 'real_name', 'department_id')->where('status', 1)->get();
    echo "用户数量: " . $users->count() . "\n";
    
    foreach ($users as $user) {
        echo "用户: {$user->real_name} (ID: {$user->id}, 部门ID: {$user->department_id})\n";
    }
    
    echo "\n";
    
    // 从数据库获取部门数据
    $departments = [];
    try {
        $departments = Department::select('id', 'department_name as name', 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->toArray();
        
        echo "部门数量: " . count($departments) . "\n";
        
        foreach ($departments as $dept) {
            echo "部门: {$dept['name']} (ID: {$dept['id']}, 父级ID: {$dept['parent_id']})\n";
        }
    } catch (\Exception $e) {
        echo "部门查询失败: " . $e->getMessage() . "\n";
        // 如果部门表不存在或查询失败，使用默认数据
        $departments = [
            ['id' => 1, 'name' => '管理部门', 'parent_id' => 0],
            ['id' => 2, 'name' => '业务部门', 'parent_id' => 0],
            ['id' => 3, 'name' => '技术部门', 'parent_id' => 0],
            ['id' => 4, 'name' => '人事部门', 'parent_id' => 1],
        ];
        
        echo "使用默认部门数据，数量: " . count($departments) . "\n";
        foreach ($departments as $dept) {
            echo "部门: {$dept['name']} (ID: {$dept['id']}, 父级ID: {$dept['parent_id']})\n";
        }
    }
    
    echo "\n=== 构建组织架构树 ===\n";
    
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
    
    echo "组织架构树节点数量: " . count($tree) . "\n";
    
    foreach ($tree as $node) {
        echo "节点: {$node['label']} (ID: {$node['id']}, 类型: {$node['type']}, 用户数: {$node['count']})\n";
        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                echo "  - 用户: {$child['label']} (ID: {$child['id']})\n";
            }
        }
    }
    
    echo "\n=== JSON格式输出 ===\n";
    echo json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪: " . $e->getTraceAsString() . "\n";
}
