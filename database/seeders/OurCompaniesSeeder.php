<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OurCompaniesSeeder extends Seeder
{
    public function run()
    {
        $companies = [
            [
                'name' => '睿道知识产权',
                'code' => 'rd_ip',
                'short_name' => '睿道知识产权',
                'full_name' => '北京睿道知识产权代理有限公司',
                'credit_code' => '91110108MA01T6FP27',
                'address' => '北京市海淀区中关村南大街5号理工科技大厦1605室',
                'contact_person' => '张经理',
                'contact_phone' => '010-82356781',
                'tax_number' => '91110108MA01T6FP27',
                'bank' => '中国建设银行北京中关村南大街支行',
                'account' => '1100 1085 7000 5300 2121',
                'invoice_phone' => '010-82356781',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '睿道科技',
                'code' => 'rd_tech',
                'short_name' => '睿道科技',
                'full_name' => '睿道科技（北京）有限公司',
                'credit_code' => '91110108MA01T6FP28',
                'address' => '北京市朝阳区朝阳北路创业大厦B座1008室',
                'contact_person' => '李经理',
                'contact_phone' => '010-65891234',
                'tax_number' => '91110108MA01T6FP28',
                'bank' => '中国工商银行北京朝阳支行',
                'account' => '6212 2601 0201 9876 543',
                'invoice_phone' => '010-65891234',
                'status' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '智慧产权代理',
                'code' => 'zh_ip',
                'short_name' => '智慧产权代理',
                'full_name' => '智慧知识产权代理（上海）有限公司',
                'credit_code' => '91310115MA1FL9QB9X',
                'address' => '上海市浦东新区张江高科技园区科苑路528号',
                'contact_person' => '王总监',
                'contact_phone' => '021-58751234',
                'tax_number' => '91310115MA1FL9QB9X',
                'bank' => '中国银行上海浦东支行',
                'account' => '4563 2198 7654 3210 876',
                'invoice_phone' => '021-58751234',
                'status' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('our_companies')->insert($companies);
    }
}
