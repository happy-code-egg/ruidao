<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RelatedType;

class RelatedTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $relatedTypes = [
            // 发明专利相关类型
            [
                'case_type' => '发明专利',
                'type_name' => '技术方案类',
                'type_code' => 'INVENTION_TECH',
                'description' => '涉及技术方案的发明专利',
                'is_valid' => true,
                'sort_order' => 1,
                'updater' => '系统初始化',
            ],
            [
                'case_type' => '发明专利',
                'type_name' => '方法类',
                'type_code' => 'INVENTION_METHOD',
                'description' => '涉及方法的发明专利',
                'is_valid' => true,
                'sort_order' => 2,
                'updater' => '系统初始化',
            ],
            // 实用新型相关类型
            [
                'case_type' => '实用新型',
                'type_name' => '产品结构类',
                'type_code' => 'UTILITY_STRUCTURE',
                'description' => '产品结构相关实用新型',
                'is_valid' => true,
                'sort_order' => 3,
                'updater' => '系统初始化',
            ],
            [
                'case_type' => '实用新型',
                'type_name' => '机械装置类',
                'type_code' => 'UTILITY_MACHINE',
                'description' => '机械装置相关实用新型',
                'is_valid' => true,
                'sort_order' => 4,
                'updater' => '系统初始化',
            ],
            // 外观设计相关类型
            [
                'case_type' => '外观设计',
                'type_name' => '产品外观类',
                'type_code' => 'DESIGN_PRODUCT',
                'description' => '产品外观设计',
                'is_valid' => true,
                'sort_order' => 5,
                'updater' => '系统初始化',
            ],
            [
                'case_type' => '外观设计',
                'type_name' => '界面设计类',
                'type_code' => 'DESIGN_UI',
                'description' => '用户界面设计',
                'is_valid' => true,
                'sort_order' => 6,
                'updater' => '系统初始化',
            ],
            // 商标相关类型
            [
                'case_type' => '商标',
                'type_name' => '文字商标',
                'type_code' => 'TRADEMARK_TEXT',
                'description' => '文字形式的商标',
                'is_valid' => true,
                'sort_order' => 7,
                'updater' => '系统初始化',
            ],
            [
                'case_type' => '商标',
                'type_name' => '图形商标',
                'type_code' => 'TRADEMARK_GRAPHIC',
                'description' => '图形形式的商标',
                'is_valid' => true,
                'sort_order' => 8,
                'updater' => '系统初始化',
            ],
            // 版权相关类型
            [
                'case_type' => '版权',
                'type_name' => '软件著作权',
                'type_code' => 'COPYRIGHT_SOFTWARE',
                'description' => '计算机软件著作权',
                'is_valid' => true,
                'sort_order' => 9,
                'updater' => '系统初始化',
            ],
            [
                'case_type' => '版权',
                'type_name' => '作品著作权',
                'type_code' => 'COPYRIGHT_WORK',
                'description' => '文学、艺术作品著作权',
                'is_valid' => true,
                'sort_order' => 10,
                'updater' => '系统初始化',
            ],
        ];

        foreach ($relatedTypes as $type) {
            RelatedType::updateOrCreate(
                ['type_code' => $type['type_code']],
                $type
            );
        }

        if ($this->command) {


            $this->command->info('相关类型数据初始化完成');


        }
    }
}
