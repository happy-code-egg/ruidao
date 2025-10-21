<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建测试用户数据';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始创建测试用户数据...');

        // 先创建测试部门
        $departments = [
            ['id' => 1, 'department_code' => 'MGMT', 'department_name' => '管理部门', 'parent_id' => 0],
            ['id' => 2, 'department_code' => 'BUSINESS', 'department_name' => '业务部门', 'parent_id' => 0],
            ['id' => 3, 'department_code' => 'FINANCE', 'department_name' => '财务部门', 'parent_id' => 0],
            ['id' => 4, 'department_code' => 'TECH', 'department_name' => '技术部门', 'parent_id' => 0],
            ['id' => 5, 'department_code' => 'SERVICE', 'department_name' => '客服部门', 'parent_id' => 0],
        ];

        // 检查并插入部门数据（如果不存在）
        foreach ($departments as $dept) {
            $existingDept = DB::table('departments')->where('department_code', $dept['department_code'])->first();
            if (!$existingDept) {
                DB::table('departments')->insert(array_merge($dept, [
                    'level_path' => '/' . $dept['id'] . '/',
                    'sort_order' => $dept['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                $this->info("创建部门: {$dept['department_name']}");
            } else {
                $this->comment("部门已存在: {$dept['department_name']}");
            }
        }

        // 创建测试用户
        $users = [
            // 管理部门
            ['username' => 'manager_zhang', 'real_name' => '张主管', 'position' => '部门主管', 'department_id' => 1],
            ['username' => 'manager_li', 'real_name' => '李主管', 'position' => '副主管', 'department_id' => 1],
            ['username' => 'manager_wang', 'real_name' => '王主管', 'position' => '项目主管', 'department_id' => 1],
            
            // 业务部门
            ['username' => 'business_chen', 'real_name' => '陈业务', 'position' => '业务经理', 'department_id' => 2],
            ['username' => 'business_liu', 'real_name' => '刘业务', 'position' => '业务专员', 'department_id' => 2],
            ['username' => 'business_zhao', 'real_name' => '赵业务', 'position' => '高级业务员', 'department_id' => 2],
            
            // 财务部门
            ['username' => 'finance_sun', 'real_name' => '孙财务', 'position' => '财务经理', 'department_id' => 3],
            ['username' => 'finance_zhou', 'real_name' => '周财务', 'position' => '会计', 'department_id' => 3],
            
            // 技术部门
            ['username' => 'tech_wu', 'real_name' => '吴技术', 'position' => '技术专员', 'department_id' => 4],
            ['username' => 'tech_zheng', 'real_name' => '郑技术', 'position' => '高级技术员', 'department_id' => 4],
            
            // 客服部门
            ['username' => 'service_huang', 'real_name' => '黄客服', 'position' => '客服专员', 'department_id' => 5],
            ['username' => 'service_xu', 'real_name' => '许客服', 'position' => '客服主管', 'department_id' => 5],
        ];

        $createdCount = 0;
        foreach ($users as $index => $userData) {
            $existingUser = DB::table('users')->where('username', $userData['username'])->first();
            
            if (!$existingUser) {
                DB::table('users')->insert(array_merge($userData, [
                    'password' => Hash::make('123456'), // 默认密码
                    'email' => $userData['username'] . '@example.com',
                    'employee_no' => 'EMP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                $createdCount++;
                $this->info("创建用户: {$userData['real_name']} ({$userData['username']})");
            } else {
                $this->comment("用户已存在: {$userData['real_name']} ({$userData['username']})");
            }
        }

        $this->info("测试用户创建完成！新创建了 {$createdCount} 个用户。");
        $this->info("默认密码都是: 123456");
        
        return 0;
    }
}
