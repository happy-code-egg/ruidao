<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUpMethod;

class FollowUpMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => '电话',
                'code' => 'PHONE',
                'description' => '电话沟通联系',
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => '邮件',
                'code' => 'EMAIL',
                'description' => '邮件联系沟通',
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => '面谈',
                'code' => 'FACE_TO_FACE',
                'description' => '面对面沟通',
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => '微信',
                'code' => 'WECHAT',
                'description' => '微信联系沟通',
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'QQ',
                'code' => 'QQ',
                'description' => 'QQ联系沟通',
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => '短信',
                'code' => 'SMS',
                'description' => '短信联系',
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => '上门拜访',
                'code' => 'VISIT',
                'description' => '上门拜访客户',
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => '视频会议',
                'code' => 'VIDEO_CALL',
                'description' => '视频会议沟通',
                'status' => 1,
                'sort_order' => 8,
            ],
        ];

        foreach ($data as $item) {
            FollowUpMethod::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        if ($this->command) {


            $this->command->info('跟进方式数据初始化完成');


        }
    }
}

