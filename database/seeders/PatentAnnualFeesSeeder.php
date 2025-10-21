<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatentAnnualFee;
use App\Models\PatentAnnualFeeDetail;

class PatentAnnualFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建主表数据
        $mainData = [
            [
                'case_type' => '发明专利',
                'apply_type' => '普通申请',
                'country' => '中国',
                'start_date' => '申请日',
                'currency' => 'CNY',
                'has_fee_guide' => 1,
                'sort_order' => 1,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'case_type' => '发明专利',
                'apply_type' => '普通申请',
                'country' => '美国',
                'start_date' => '授权日',
                'currency' => 'USD',
                'has_fee_guide' => 1,
                'sort_order' => 2,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($mainData as $item) {
            $fee = PatentAnnualFee::create($item);
            
            // 为每个主记录创建详情数据
            if ($fee->country === '中国') {
                $this->createChinaFeeDetails($fee->id);
            } elseif ($fee->country === '美国') {
                $this->createUSAFeeDetails($fee->id);
            }
        }
    }

    /**
     * 创建中国专利年费详情
     */
    private function createChinaFeeDetails($feeId)
    {
        $details = [
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_1',
                'rank' => 1,
                'official_year' => 1,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 1,
                'end_year' => 3,
                'base_fee' => 900.00,
                'small_fee' => 270.00,
                'micro_fee' => 135.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_4',
                'rank' => 2,
                'official_year' => 4,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 4,
                'end_year' => 6,
                'base_fee' => 1200.00,
                'small_fee' => 360.00,
                'micro_fee' => 180.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_7',
                'rank' => 3,
                'official_year' => 7,
                'official_month' => 0,
                'official_day' => 0,
                'start_year' => 7,
                'end_year' => 9,
                'base_fee' => 2000.00,
                'small_fee' => 600.00,
                'micro_fee' => 300.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($details as $detail) {
            PatentAnnualFeeDetail::create($detail);
        }
    }

    /**
     * 创建美国专利年费详情
     */
    private function createUSAFeeDetails($feeId)
    {
        $details = [
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_3_5',
                'rank' => 1,
                'official_year' => 3,
                'official_month' => 6,
                'official_day' => 0,
                'start_year' => 3,
                'end_year' => 3,
                'base_fee' => 1600.00,
                'small_fee' => 800.00,
                'micro_fee' => 400.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_7_5',
                'rank' => 2,
                'official_year' => 7,
                'official_month' => 6,
                'official_day' => 0,
                'start_year' => 7,
                'end_year' => 7,
                'base_fee' => 3600.00,
                'small_fee' => 1800.00,
                'micro_fee' => 900.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patent_annual_fee_id' => $feeId,
                'stage_code' => 'YEAR_11_5',
                'rank' => 3,
                'official_year' => 11,
                'official_month' => 6,
                'official_day' => 0,
                'start_year' => 11,
                'end_year' => 11,
                'base_fee' => 7700.00,
                'small_fee' => 3850.00,
                'micro_fee' => 1925.00,
                'authorization_fee' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($details as $detail) {
            PatentAnnualFeeDetail::create($detail);
        }
    }
}
