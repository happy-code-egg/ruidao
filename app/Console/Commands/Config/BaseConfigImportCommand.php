<?php

namespace App\Console\Commands\Config;

use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;

abstract class BaseConfigImportCommand extends Command
{
    /**
     * 获取Excel文件路径
     */
    protected function getExcelPath(): string
    {
        return __DIR__ . '/excel/' . $this->getExcelFileName();
    }

    /**
     * 获取Excel文件名（子类必须实现）
     */
    abstract protected function getExcelFileName(): string;

    /**
     * 获取数据表名（子类必须实现）
     */
    abstract protected function getTableName(): string;

    /**
     * 获取模型类（子类必须实现）
     */
    abstract protected function getModelClass(): string;

    /**
     * 处理数据（子类可以重写）
     */
    protected function processData(array $data): array
    {
        return $data;
    }

    /**
     * 执行导入
     */
    public function handle()
    {
        $excelPath = $this->getExcelPath();
        
        if (!file_exists($excelPath)) {
            $this->error("Excel文件不存在: {$excelPath}");
            return 1;
        }

        $this->info("开始导入 {$this->getTableName()} 数据...");

        try {
            // 1. 清空当前表
            $this->info("清空表: {$this->getTableName()}");
            DB::table($this->getTableName())->truncate();

            // 2. 导入数据
            $this->info("读取Excel文件: {$excelPath}");
            $collection = (new FastExcel)->import($excelPath);

            if ($collection->isEmpty()) {
                $this->warn("Excel文件为空或没有数据");
                return 0;
            }

            $this->info("读取到 {$collection->count()} 条数据");

            // 3. 处理数据并插入
            $successCount = 0;
            $errorCount = 0;

            foreach ($collection as $row) {
                try {
                    // 过滤空值
                    $data = array_filter($row, function($value) {
                        return $value !== null && $value !== '';
                    });

                    if (empty($data)) {
                        continue;
                    }

                    // 处理数据
                    $processedData = $this->processData($data);

                    // 插入数据
                    $modelClass = $this->getModelClass();
                    
                    // 如果数据中包含时间戳，临时禁用自动时间戳
                    $model = new $modelClass();
                    if (isset($processedData['created_at']) || isset($processedData['updated_at'])) {
                        $model->timestamps = false;
                    }
                    $model->fill($processedData)->save();
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->warn("数据插入失败: " . $e->getMessage());
                    $this->warn("数据内容: " . json_encode($row, JSON_UNESCAPED_UNICODE));
                }
            }

            $this->info("导入完成!");
            $this->info("成功: {$successCount} 条");
            if ($errorCount > 0) {
                $this->warn("失败: {$errorCount} 条");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("导入失败: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * 添加时间戳
     * @param array $data 数据数组
     * @param string|null $timestamp 指定时间戳，null则使用当前时间
     */
    protected function addCustomTimestamps(array $data, string $timestamp = null): array
    {
        $time = $timestamp ?: now();
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        return $data;
    }

    /**
     * 添加自定义时间戳
     * @param array $data 数据数组
     * @param string $datetime 自定义时间字符串，如 '2025-01-01 00:00:00'
     */
    
    protected function addTimestamps(array $data, string $datetime = '2025-01-01 00:00:00'): array
    {
        $data['created_at'] = $datetime;
        $data['updated_at'] = $datetime;
        return $data;
    }

    /**
     * 添加创建人和更新人
     */
    protected function addUserInfo(array $data, int $userId = 1): array
    {
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        return $data;
    }
}
