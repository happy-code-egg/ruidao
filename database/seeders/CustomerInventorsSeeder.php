<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerInventorsSeeder extends Seeder
{
    /**
     * 运行客户发明人数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customer_inventors')->truncate();

        $now = Carbon::now();

        // 创建示例发明人数据
        $inventors = [
            [
                'id' => 1,
                'customer_id' => 1,
                'inventor_name_cn' => '张三',
                'inventor_name_en' => 'Zhang San',
                'inventor_code' => 'INV20250101001',
                'inventor_type' => '主发明人',
                'gender' => '男',
                'id_type' => '身份证',
                'id_number' => '310115198501010001',
                'country' => '中国',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'street' => '张江镇博云路2号',
                'postal_code' => '201203',
                'address' => '上海市浦东新区张江镇博云路2号',
                'address_en' => 'No.2 Boyun Road, Zhangjiang Town, Pudong New District, Shanghai',
                'phone' => '13800138000',
                'landline' => '021-88889999',
                'wechat' => 'zhangsan_wx',
                'email' => 'zhangsan@ruidao.com',
                'work_unit' => '上海睿道知识产权有限公司',
                'department' => '技术部',
                'position' => '技术总监',
                'business_staff' => '王销售',
                'remark' => '公司主要技术负责人，拥有多项专利',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'inventor_name_cn' => '李四',
                'inventor_name_en' => 'Li Si',
                'inventor_code' => 'INV20250101002',
                'inventor_type' => '共同发明人',
                'gender' => '男',
                'id_type' => '身份证',
                'id_number' => '310115198801020002',
                'country' => '中国',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'street' => '张江镇科苑路388号',
                'postal_code' => '201203',
                'address' => '上海市浦东新区张江镇科苑路388号',
                'address_en' => 'No.388 Keyuan Road, Zhangjiang Town, Pudong New District, Shanghai',
                'phone' => '13900139000',
                'landline' => '021-88889998',
                'wechat' => 'lisi_wx',
                'email' => 'lisi@ruidao.com',
                'work_unit' => '上海睿道知识产权有限公司',
                'department' => '研发部',
                'position' => '高级工程师',
                'business_staff' => '王销售',
                'remark' => '资深技术专家，专注于AI算法研发',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'customer_id' => 1,
                'inventor_name_cn' => '王五',
                'inventor_name_en' => 'Wang Wu',
                'inventor_code' => 'INV20250101003',
                'inventor_type' => '共同发明人',
                'gender' => '女',
                'id_type' => '身份证',
                'id_number' => '310115199205150003',
                'country' => '中国',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'street' => '张江镇中科路699号',
                'postal_code' => '201203',
                'address' => '上海市浦东新区张江镇中科路699号',
                'address_en' => 'No.699 Zhongke Road, Zhangjiang Town, Pudong New District, Shanghai',
                'phone' => '13700137000',
                'landline' => '021-88889997',
                'wechat' => 'wangwu_wx',
                'email' => 'wangwu@ruidao.com',
                'work_unit' => '上海睿道知识产权有限公司',
                'department' => '产品部',
                'position' => '产品经理',
                'business_staff' => '王销售',
                'remark' => '负责产品设计和用户体验优化',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('customer_inventors')->insert($inventors);
        
        $this->command->info('客户发明人数据种子已成功植入！');
    }
}