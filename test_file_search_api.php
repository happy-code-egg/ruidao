<?php

// 简单的文件搜索API测试脚本

require_once 'vendor/autoload.php';

$baseUrl = 'http://localhost:8000/api'; // 根据实际情况调整

// 测试获取选项数据
function testGetOptions($baseUrl) {
    echo "测试获取选项数据...\n";
    
    $optionTypes = ['case_types', 'business_types', 'application_types', 'countries', 'case_flows', 'staff', 'document_names'];
    
    foreach ($optionTypes as $type) {
        $url = $baseUrl . "/search/files/options?type=" . $type;
        echo "请求URL: $url\n";
        
        $response = file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && $data['success']) {
                echo "✓ 获取 $type 选项成功，数据量: " . count($data['data']) . "\n";
            } else {
                echo "✗ 获取 $type 选项失败\n";
            }
        } else {
            echo "✗ 请求 $type 选项失败\n";
        }
    }
    echo "\n";
}

// 测试文件搜索
function testFileSearch($baseUrl) {
    echo "测试文件搜索...\n";
    
    $url = $baseUrl . "/search/files?page=1&limit=10";
    echo "请求URL: $url\n";
    
    $response = file_get_contents($url);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "✓ 文件搜索成功\n";
            echo "总数量: " . $data['data']['total'] . "\n";
            echo "当前页: " . $data['data']['current_page'] . "\n";
            echo "每页数量: " . $data['data']['per_page'] . "\n";
            echo "返回记录数: " . count($data['data']['list']) . "\n";
        } else {
            echo "✗ 文件搜索失败: " . ($data['message'] ?? '未知错误') . "\n";
        }
    } else {
        echo "✗ 文件搜索请求失败\n";
    }
    echo "\n";
}

// 执行测试
echo "=== 文件管理API测试 ===\n\n";

testGetOptions($baseUrl);
testFileSearch($baseUrl);

echo "测试完成！\n";
