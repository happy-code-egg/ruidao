<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatentAnnualFee;
use App\Models\PatentAnnualFeeDetail;
use App\Models\CustomerLevel;
use App\Models\RelatedType;
use Illuminate\Support\Facades\DB;

class NewThreePagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        try {
            $this->seedPatentAnnualFees();
            $this->seedCustomerLevels();
            $this->seedRelatedTypes();

            DB::commit();
            echo "新增三个页面测试数据插入成功！\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "数据插入失败: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * 插入专利年费配置数据
     */
    private function seedPatentAnnualFees()
    {
        echo "插入专利年费配置数据...\n";

        // 创建专利年费主表数据
        $patentFees = [
            [
                'country' => 'CN',
                'patent_type' => '发明专利',
                'name' => '中国发明专利年费标准',
                'description' => '中国发明专利年费收费标准，按年递增',
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'country' => 'CN',
                'patent_type' => '实用新型',
                'name' => '中国实用新型专利年费标准',
                'description' => '中国实用新型专利年费收费标准',
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'country' => 'CN',
                'patent_type' => '外观设计',
                'name' => '中国外观设计专利年费标准',
                'description' => '中国外观设计专利年费收费标准',
                'is_valid' => true,
                'sort' => 3
            ],
            [
                'country' => 'US',
                'patent_type' => '发明专利',
                'name' => '美国发明专利年费标准',
                'description' => '美国发明专利维持费标准',
                'is_valid' => true,
                'sort' => 4
            ]
        ];

        foreach ($patentFees as $feeData) {
            $patentFee = PatentAnnualFee::create($feeData);

            // 为每个专利年费配置创建详情数据
            $this->createPatentFeeDetails($patentFee);
        }
    }

    /**
     * 创建专利年费详情数据
     */
    private function createPatentFeeDetails($patentFee)
    {
        $details = [];

        if ($patentFee->country == 'CN') {
            if ($patentFee->patent_type == '发明专利') {
                $details = [
                    ['year_from' => 1, 'year_to' => 3, 'fee_amount' => 900, 'currency' => 'CNY', 'remarks' => '第1-3年'],
                    ['year_from' => 4, 'year_to' => 6, 'fee_amount' => 1200, 'currency' => 'CNY', 'remarks' => '第4-6年'],
                    ['year_from' => 7, 'year_to' => 9, 'fee_amount' => 2000, 'currency' => 'CNY', 'remarks' => '第7-9年'],
                    ['year_from' => 10, 'year_to' => 12, 'fee_amount' => 4000, 'currency' => 'CNY', 'remarks' => '第10-12年'],
                    ['year_from' => 13, 'year_to' => 15, 'fee_amount' => 6000, 'currency' => 'CNY', 'remarks' => '第13-15年'],
                    ['year_from' => 16, 'year_to' => 17, 'fee_amount' => 8000, 'currency' => 'CNY', 'remarks' => '第16-17年'],
                    ['year_from' => 18, 'year_to' => 20, 'fee_amount' => 20000, 'currency' => 'CNY', 'remarks' => '第18-20年']
                ];
            } elseif ($patentFee->patent_type == '实用新型') {
                $details = [
                    ['year_from' => 1, 'year_to' => 3, 'fee_amount' => 600, 'currency' => 'CNY', 'remarks' => '第1-3年'],
                    ['year_from' => 4, 'year_to' => 5, 'fee_amount' => 900, 'currency' => 'CNY', 'remarks' => '第4-5年'],
                    ['year_from' => 6, 'year_to' => 8, 'fee_amount' => 1200, 'currency' => 'CNY', 'remarks' => '第6-8年'],
                    ['year_from' => 9, 'year_to' => 10, 'fee_amount' => 2000, 'currency' => 'CNY', 'remarks' => '第9-10年']
                ];
            } elseif ($patentFee->patent_type == '外观设计') {
                $details = [
                    ['year_from' => 1, 'year_to' => 1, 'fee_amount' => 600, 'currency' => 'CNY', 'remarks' => '第1年'],
                    ['year_from' => 2, 'year_to' => 2, 'fee_amount' => 600, 'currency' => 'CNY', 'remarks' => '第2年'],
                    ['year_from' => 3, 'year_to' => 3, 'fee_amount' => 600, 'currency' => 'CNY', 'remarks' => '第3年'],
                    ['year_from' => 4, 'year_to' => 5, 'fee_amount' => 900, 'currency' => 'CNY', 'remarks' => '第4-5年'],
                    ['year_from' => 6, 'year_to' => 8, 'fee_amount' => 1200, 'currency' => 'CNY', 'remarks' => '第6-8年'],
                    ['year_from' => 9, 'year_to' => 10, 'fee_amount' => 2000, 'currency' => 'CNY', 'remarks' => '第9-10年']
                ];
            }
        } else { // US
            $details = [
                ['year_from' => 1, 'year_to' => 3, 'fee_amount' => 400, 'currency' => 'USD', 'remarks' => '1st maintenance fee'],
                ['year_from' => 4, 'year_to' => 7, 'fee_amount' => 900, 'currency' => 'USD', 'remarks' => '2nd maintenance fee'],
                ['year_from' => 8, 'year_to' => 11, 'fee_amount' => 1850, 'currency' => 'USD', 'remarks' => '3rd maintenance fee'],
                ['year_from' => 12, 'year_to' => 20, 'fee_amount' => 7700, 'currency' => 'USD', 'remarks' => 'Final maintenance fee']
            ];
        }

        foreach ($details as $detail) {
            $detailData = array_merge($detail, [
                'patent_annual_fee_id' => $patentFee->id,
                'is_valid' => true
            ]);
            
            PatentAnnualFeeDetail::create($detailData);
        }
    }

    /**
     * 插入客户等级配置数据
     */
    private function seedCustomerLevels()
    {
        echo "插入客户等级配置数据...\n";

        $customerLevels = [
            [
                'code' => 'PLATINUM',
                'name' => '铂金客户',
                'description' => '最高等级客户，享受VIP服务和最优惠价格',
                'level_value' => 5,
                'discount_rate' => 0.7000,
                'color' => '#E6A23C',
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'code' => 'GOLD',
                'name' => '金牌客户',
                'description' => '高级客户，享受优先服务和优惠价格',
                'level_value' => 4,
                'discount_rate' => 0.8000,
                'color' => '#F56C6C',
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'code' => 'SILVER',
                'name' => '银牌客户',
                'description' => '中级客户，享受标准服务和小额优惠',
                'level_value' => 3,
                'discount_rate' => 0.9000,
                'color' => '#909399',
                'is_valid' => true,
                'sort' => 3
            ],
            [
                'code' => 'BRONZE',
                'name' => '铜牌客户',
                'description' => '普通客户，享受基础服务',
                'level_value' => 2,
                'discount_rate' => 0.9500,
                'color' => '#67C23A',
                'is_valid' => true,
                'sort' => 4
            ],
            [
                'code' => 'REGULAR',
                'name' => '普通客户',
                'description' => '新客户或低频次客户，标准价格',
                'level_value' => 1,
                'discount_rate' => 1.0000,
                'color' => '#409EFF',
                'is_valid' => true,
                'sort' => 5
            ],
            [
                'code' => 'VIP',
                'name' => 'VIP客户',
                'description' => '特殊VIP客户，定制化服务',
                'level_value' => 6,
                'discount_rate' => 0.6000,
                'color' => '#C33EF4',
                'is_valid' => true,
                'sort' => 0
            ]
        ];

        foreach ($customerLevels as $levelData) {
            CustomerLevel::create($levelData);
        }
    }

    /**
     * 插入相关类型配置数据
     */
    private function seedRelatedTypes()
    {
        echo "插入相关类型配置数据...\n";

        $relatedTypes = [
            // 项目类型分类
            [
                'category' => 'case_type',
                'code' => 'PATENT',
                'name' => '专利',
                'description' => '专利相关项目类型',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'case_type',
                'code' => 'PATENT_INVENTION',
                'name' => '发明专利',
                'description' => '发明专利申请',
                'parent_code' => 'PATENT',
                'level' => 2,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'case_type',
                'code' => 'PATENT_UTILITY',
                'name' => '实用新型专利',
                'description' => '实用新型专利申请',
                'parent_code' => 'PATENT',
                'level' => 2,
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'category' => 'case_type',
                'code' => 'PATENT_DESIGN',
                'name' => '外观设计专利',
                'description' => '外观设计专利申请',
                'parent_code' => 'PATENT',
                'level' => 2,
                'is_valid' => true,
                'sort' => 3
            ],
            
            // 商标类型分类
            [
                'category' => 'case_type',
                'code' => 'TRADEMARK',
                'name' => '商标',
                'description' => '商标相关项目类型',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'category' => 'case_type',
                'code' => 'TRADEMARK_REGISTER',
                'name' => '商标注册',
                'description' => '商标注册申请',
                'parent_code' => 'TRADEMARK',
                'level' => 2,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'case_type',
                'code' => 'TRADEMARK_RENEWAL',
                'name' => '商标续展',
                'description' => '商标续展申请',
                'parent_code' => 'TRADEMARK',
                'level' => 2,
                'is_valid' => true,
                'sort' => 2
            ],
            
            // 版权类型分类
            [
                'category' => 'case_type',
                'code' => 'COPYRIGHT',
                'name' => '版权',
                'description' => '版权相关项目类型',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 3
            ],
            [
                'category' => 'case_type',
                'code' => 'COPYRIGHT_SOFTWARE',
                'name' => '软件著作权',
                'description' => '计算机软件著作权登记',
                'parent_code' => 'COPYRIGHT',
                'level' => 2,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'case_type',
                'code' => 'COPYRIGHT_WORK',
                'name' => '作品著作权',
                'description' => '文学、艺术作品著作权登记',
                'parent_code' => 'COPYRIGHT',
                'level' => 2,
                'is_valid' => true,
                'sort' => 2
            ],

            // 业务类型分类
            [
                'category' => 'business_type',
                'code' => 'APPLICATION',
                'name' => '申请',
                'description' => '各类申请业务',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'business_type',
                'code' => 'RESPONSE',
                'name' => '答复',
                'description' => '审查意见答复业务',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'category' => 'business_type',
                'code' => 'CORRECTION',
                'name' => '补正',
                'description' => '补正业务',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 3
            ],
            [
                'category' => 'business_type',
                'code' => 'CHANGE',
                'name' => '变更',
                'description' => '著录项目变更业务',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 4
            ],
            [
                'category' => 'business_type',
                'code' => 'RENEWAL',
                'name' => '续展',
                'description' => '权利续展业务',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 5
            ],

            // 处理事项状态分类
            [
                'category' => 'process_status',
                'code' => 'PENDING',
                'name' => '待处理',
                'description' => '待处理状态',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 1
            ],
            [
                'category' => 'process_status',
                'code' => 'PROCESSING',
                'name' => '处理中',
                'description' => '正在处理状态',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 2
            ],
            [
                'category' => 'process_status',
                'code' => 'COMPLETED',
                'name' => '已完成',
                'description' => '已完成状态',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 3
            ],
            [
                'category' => 'process_status',
                'code' => 'CANCELLED',
                'name' => '已取消',
                'description' => '已取消状态',
                'parent_code' => null,
                'level' => 1,
                'is_valid' => true,
                'sort' => 4
            ]
        ];

        foreach ($relatedTypes as $typeData) {
            RelatedType::create($typeData);
        }
    }
}
