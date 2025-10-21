<?php

// 批量修复核稿管理页面的API集成

$pages = [
    'to-be-start' => [
        'api_method' => 'getToBeStartList',
        'file' => 'ema/src/views/case/review-management/to-be-start/index.vue'
    ],
    'in-review' => [
        'api_method' => 'getInReviewList', 
        'file' => 'ema/src/views/case/review-management/in-review/index.vue'
    ],
    'completed' => [
        'api_method' => 'getCompletedList',
        'file' => 'ema/src/views/case/review-management/completed/index.vue'
    ]
];

foreach ($pages as $pageName => $config) {
    $filePath = '../' . $config['file'];
    
    if (!file_exists($filePath)) {
        echo "文件不存在: {$filePath}\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // 添加import语句
    if (strpos($content, 'import reviewApi') === false) {
        $content = str_replace(
            '<script>',
            "<script>\nimport reviewApi from '@/api/review'\n",
            $content
        );
    }
    
    // 替换getList方法为真实API调用
    $oldPattern = '/getList\s*\(\)\s*\{[^}]*this\.listLoading\s*=\s*true[^}]*setTimeout\s*\([^}]*\}\s*,\s*\d+\s*\)\s*\}/s';
    
    $newMethod = "async getList () {
      this.listLoading = true
      try {
        const params = {
          page: this.listQuery.page,
          limit: this.listQuery.limit
        }

        const response = await reviewApi.{$config['api_method']}(params)
        
        if (response.data.success) {
          this.tableData = response.data.data.list
          this.total = response.data.data.total
          this.listQuery.page = response.data.data.currentPage || 1
        } else {
          this.\$message.error(response.data.message || '查询失败')
        }
      } catch (error) {
        console.error('查询{$pageName}数据失败:', error)
        this.\$message.error('查询失败，请稍后重试')
        // 模拟数据作为备用
        this.tableData = []
        this.total = 0
      } finally {
        this.listLoading = false
      }
    }";
    
    $content = preg_replace($oldPattern, $newMethod, $content);
    
    // 修复查看详情的跳转
    $content = str_replace(
        'path: `/case/reception/case-detail/${row.id}/view`',
        'path: `/case/review-management/detail/${row.id}`',
        $content
    );
    
    file_put_contents($filePath, $content);
    echo "已修复: {$pageName} 页面\n";
}

echo "所有核稿管理页面修复完成！\n";
