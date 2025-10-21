<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerDetailDataSeeder extends Seeder
{
    /**
     * 运行客户详情相关的所有数据种子
     *
     * @return void
     */
    public function run()
    {
        // 开始数据库事务
        DB::beginTransaction();
        
        try {
            $this->command->info('开始植入客户详情相关数据...');
            
            // 按依赖关系顺序运行种子
            $this->call([
                CustomerDetailSeeder::class,          // 客户基础信息
                CustomerContactsSeeder::class,        // 客户联系人
                CustomerApplicantsSeeder::class,      // 客户申请人
                CustomerInventorsSeeder::class,       // 客户发明人
                CustomerRelatedPersonsSeeder::class,  // 客户相关人员
                CustomerFilesSeeder::class,          // 客户文件
                CustomerContractsSeeder::class,      // 客户合同
                CustomerCasesSeeder::class,          // 客户案例/项目
            ]);
            
            // 提交事务
            DB::commit();
            
            $this->command->info('✅ 所有客户详情数据种子植入成功！');
            $this->command->info('');
            $this->command->info('📊 数据统计：');
            $this->command->info('  - 客户: ' . DB::table('customers')->count() . ' 条');
            $this->command->info('  - 联系人: ' . DB::table('customer_contacts')->count() . ' 条');
            $this->command->info('  - 申请人: ' . DB::table('customer_applicants')->count() . ' 条');
            $this->command->info('  - 发明人: ' . DB::table('customer_inventors')->count() . ' 条');
            $this->command->info('  - 相关人员: ' . DB::table('customer_related_persons')->count() . ' 条');
            $this->command->info('  - 文件: ' . DB::table('customer_files')->count() . ' 条');
            $this->command->info('  - 合同: ' . DB::table('customer_contracts')->count() . ' 条');
            $this->command->info('  - 案例: ' . DB::table('cases')->count() . ' 条');
            
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollback();
            
            $this->command->error('❌ 数据植入失败：' . $e->getMessage());
            throw $e;
        }
    }
}
