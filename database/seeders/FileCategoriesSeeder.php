<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FileCategories;
use Illuminate\Support\Facades\DB;

class FileCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清空表数据
        DB::table('file_categories')->truncate();

        $fileCategories = [
            // 专利文件分类
            [
                'main_category' => '专利申请文件',
                'sub_category' => '发明专利申请书',
                'is_valid' => true,
                'sort' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '实用新型专利申请书',
                'is_valid' => true,
                'sort' => 2,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '外观设计专利申请书',
                'is_valid' => true,
                'sort' => 3,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '权利要求书',
                'is_valid' => true,
                'sort' => 4,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '说明书',
                'is_valid' => true,
                'sort' => 5,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '说明书附图',
                'is_valid' => true,
                'sort' => 6,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '说明书摘要',
                'is_valid' => true,
                'sort' => 7,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利申请文件',
                'sub_category' => '摘要附图',
                'is_valid' => true,
                'sort' => 8,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 专利程序文件
            [
                'main_category' => '专利程序文件',
                'sub_category' => '实质审查请求书',
                'is_valid' => true,
                'sort' => 9,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利程序文件',
                'sub_category' => '费用减缴请求书',
                'is_valid' => true,
                'sort' => 10,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利程序文件',
                'sub_category' => '提前公布声明',
                'is_valid' => true,
                'sort' => 11,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利程序文件',
                'sub_category' => '优先权证明文件',
                'is_valid' => true,
                'sort' => 12,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利程序文件',
                'sub_category' => '委托书',
                'is_valid' => true,
                'sort' => 13,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 专利答复文件
            [
                'main_category' => '专利答复文件',
                'sub_category' => '意见陈述书',
                'is_valid' => true,
                'sort' => 14,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利答复文件',
                'sub_category' => '修改页',
                'is_valid' => true,
                'sort' => 15,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '专利答复文件',
                'sub_category' => '替换页',
                'is_valid' => true,
                'sort' => 16,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 商标申请文件
            [
                'main_category' => '商标申请文件',
                'sub_category' => '商标注册申请书',
                'is_valid' => true,
                'sort' => 17,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标申请文件',
                'sub_category' => '商标图样',
                'is_valid' => true,
                'sort' => 18,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标申请文件',
                'sub_category' => '商品/服务项目清单',
                'is_valid' => true,
                'sort' => 19,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标申请文件',
                'sub_category' => '优先权证明文件',
                'is_valid' => true,
                'sort' => 20,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标申请文件',
                'sub_category' => '委托书',
                'is_valid' => true,
                'sort' => 21,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 商标程序文件
            [
                'main_category' => '商标程序文件',
                'sub_category' => '商标续展申请书',
                'is_valid' => true,
                'sort' => 22,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标程序文件',
                'sub_category' => '商标转让申请书',
                'is_valid' => true,
                'sort' => 23,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标程序文件',
                'sub_category' => '商标变更申请书',
                'is_valid' => true,
                'sort' => 24,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标程序文件',
                'sub_category' => '商标撤回申请书',
                'is_valid' => true,
                'sort' => 25,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 商标答复文件
            [
                'main_category' => '商标答复文件',
                'sub_category' => '驳回复审申请书',
                'is_valid' => true,
                'sort' => 26,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标答复文件',
                'sub_category' => '异议答辩书',
                'is_valid' => true,
                'sort' => 27,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '商标答复文件',
                'sub_category' => '无效宣告答辩书',
                'is_valid' => true,
                'sort' => 28,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 版权申请文件
            [
                'main_category' => '版权申请文件',
                'sub_category' => '作品著作权登记申请表',
                'is_valid' => true,
                'sort' => 29,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '版权申请文件',
                'sub_category' => '计算机软件著作权登记申请表',
                'is_valid' => true,
                'sort' => 30,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '版权申请文件',
                'sub_category' => '作品样本',
                'is_valid' => true,
                'sort' => 31,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '版权申请文件',
                'sub_category' => '源程序代码',
                'is_valid' => true,
                'sort' => 32,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '版权申请文件',
                'sub_category' => '用户手册',
                'is_valid' => true,
                'sort' => 33,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 科技服务文件
            [
                'main_category' => '科技服务文件',
                'sub_category' => '高新技术企业认定申请书',
                'is_valid' => true,
                'sort' => 34,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '科技服务文件',
                'sub_category' => '专精特新申请书',
                'is_valid' => true,
                'sort' => 35,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '科技服务文件',
                'sub_category' => '研发费用专项审计报告',
                'is_valid' => true,
                'sort' => 36,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '科技服务文件',
                'sub_category' => '高新技术产品收入专项审计报告',
                'is_valid' => true,
                'sort' => 37,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '科技服务文件',
                'sub_category' => '科技成果转化证明材料',
                'is_valid' => true,
                'sort' => 38,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 合同文件
            [
                'main_category' => '合同文件',
                'sub_category' => '服务合同',
                'is_valid' => true,
                'sort' => 39,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '合同文件',
                'sub_category' => '补充协议',
                'is_valid' => true,
                'sort' => 40,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '合同文件',
                'sub_category' => '委托书',
                'is_valid' => true,
                'sort' => 41,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 财务文件
            [
                'main_category' => '财务文件',
                'sub_category' => '发票',
                'is_valid' => true,
                'sort' => 42,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '财务文件',
                'sub_category' => '收据',
                'is_valid' => true,
                'sort' => 43,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '财务文件',
                'sub_category' => '银行转账凭证',
                'is_valid' => true,
                'sort' => 44,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '财务文件',
                'sub_category' => '缴费凭证',
                'is_valid' => true,
                'sort' => 45,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 客户资料
            [
                'main_category' => '客户资料',
                'sub_category' => '营业执照',
                'is_valid' => true,
                'sort' => 46,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '客户资料',
                'sub_category' => '身份证明',
                'is_valid' => true,
                'sort' => 47,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '客户资料',
                'sub_category' => '授权委托书',
                'is_valid' => true,
                'sort' => 48,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 官方文件
            [
                'main_category' => '官方文件',
                'sub_category' => '受理通知书',
                'is_valid' => true,
                'sort' => 49,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '官方文件',
                'sub_category' => '审查意见通知书',
                'is_valid' => true,
                'sort' => 50,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '官方文件',
                'sub_category' => '授权通知书',
                'is_valid' => true,
                'sort' => 51,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '官方文件',
                'sub_category' => '驳回通知书',
                'is_valid' => true,
                'sort' => 52,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '官方文件',
                'sub_category' => '证书',
                'is_valid' => true,
                'sort' => 53,
                'created_by' => 1,
                'updated_by' => 1,
            ],

            // 内部文件
            [
                'main_category' => '内部文件',
                'sub_category' => '技术交底书',
                'is_valid' => true,
                'sort' => 54,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '内部文件',
                'sub_category' => '检索报告',
                'is_valid' => true,
                'sort' => 55,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '内部文件',
                'sub_category' => '分析报告',
                'is_valid' => true,
                'sort' => 56,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'main_category' => '内部文件',
                'sub_category' => '工作记录',
                'is_valid' => true,
                'sort' => 57,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($fileCategories as $category) {
            // 检查是否已存在相同的文件分类
            $exists = FileCategories::where('main_category', $category['main_category'])
                ->where('sub_category', $category['sub_category'])
                ->exists();

            if (!$exists) {
                FileCategories::create($category);
            }
        }

        $this->command->info('FileCategories seeder completed successfully!');
    }
}
