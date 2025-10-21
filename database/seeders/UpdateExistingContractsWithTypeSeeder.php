<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Contract;

class UpdateExistingContractsWithTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 为现有合同数据添加默认的合同类型
     * @return void
     */
    public function run()
    {
        $this->command->info('开始更新现有合同的合同类型...');

        try {
            // 更新所有现有合同的合同类型为"标准合同"
            $updatedCount = DB::table('contracts')
                ->whereNull('contract_type')
                ->orWhere('contract_type', '')
                ->update([
                    'contract_type' => Contract::TYPE_STANDARD,
                    'updated_at' => now()
                ]);

            $this->command->info("成功更新了 {$updatedCount} 个合同的合同类型为标准合同");

            // 处理service_type字段，确保现有的字符串数据能正确处理
            $contracts = DB::table('contracts')
                ->whereNotNull('service_type')
                ->where('service_type', '!=', '')
                ->get();

            $jsonUpdatedCount = 0;
            foreach ($contracts as $contract) {
                // 检查service_type是否已经是有效的JSON
                if (!$this->isValidJson($contract->service_type)) {
                    // 如果不是JSON，将其转换为JSON字符串格式
                    $jsonValue = json_encode($contract->service_type);
                    DB::table('contracts')
                        ->where('id', $contract->id)
                        ->update([
                            'service_type' => $jsonValue,
                            'updated_at' => now()
                        ]);
                    $jsonUpdatedCount++;
                }
            }

            $this->command->info("成功处理了 {$jsonUpdatedCount} 个合同的服务类型JSON格式");

        } catch (\Exception $e) {
            $this->command->error('更新合同类型时发生错误：' . $e->getMessage());
        }

        $this->command->info('合同类型更新完成！');
    }

    /**
     * 检查字符串是否为有效的JSON
     */
    private function isValidJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
