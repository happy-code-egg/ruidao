<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 处理事项状态表种子数据
     */
    public function run()
    {
        $processStatuses = [
            [
                'sort' => 1,
                'status_name' => '待处理状态',
                'status_code' => 'STATUS_PENDING',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 2,
                'status_name' => '处理中状态',
                'status_code' => 'STATUS_PROCESSING',
                'trigger_rule' => 1,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 3,
                'status_name' => '审查中状态',
                'status_code' => 'STATUS_REVIEW',
                'trigger_rule' => 1,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 4,
                'status_name' => '需要修改状态',
                'status_code' => 'STATUS_REVISION_REQUIRED',
                'trigger_rule' => 1,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 5,
                'status_name' => '已批准状态',
                'status_code' => 'STATUS_APPROVED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 6,
                'status_name' => '已拒绝状态',
                'status_code' => 'STATUS_REJECTED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 7,
                'status_name' => '已完成状态',
                'status_code' => 'STATUS_COMPLETED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 8,
                'status_name' => '已暂停状态',
                'status_code' => 'STATUS_SUSPENDED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 9,
                'status_name' => '已取消状态',
                'status_code' => 'STATUS_CANCELLED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 9,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 10,
                'status_name' => '已放弃状态',
                'status_code' => 'STATUS_ABANDONED',
                'trigger_rule' => 0,
                'status' => 1,
                'is_valid' => 1,
                'updater' => '系统管理员',
                'sort_order' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('process_statuses')->count() == 0) {
            DB::table('process_statuses')->insert($processStatuses);
        }
    }
}
