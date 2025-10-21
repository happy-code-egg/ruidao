<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 代理师表种子数据
     */
    public function run()
    {
        $agents = [
            [
                'sort' => 1,
                'name_cn' => '张明华',
                'last_name_cn' => '张',
                'first_name_cn' => '明华',
                'name_en' => 'Zhang Minghua',
                'last_name_en' => 'Zhang',
                'first_name_en' => 'Minghua',
                'license_number' => 'ZL11001',
                'qualification_number' => '11001',
                'license_date' => '2015-06-01',
                'agency' => '北京睿道', // 对应北京睿道
                'phone' => '13800138001',
                'email' => 'zhang.minghua@readow.com',
                'gender' => '男',
                'license_expiry' => '2025-06-01',
                'specialty' => '发明专利、实用新型专利',
                'is_default_agent' => true,
                'is_valid' => true,
                'credit_rating' => 'A',
                'status' => 1,
                'remarks' => '资深专利代理师，擅长机械和电子技术领域',
                'creator' => '系统管理员',
                'creation_time' => Carbon::now(),
                'modifier' => '系统管理员',
                'update_time' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 2,
                'name_cn' => '李晓红',
                'last_name_cn' => '李',
                'first_name_cn' => '晓红',
                'name_en' => 'Li Xiaohong',
                'last_name_en' => 'Li',
                'first_name_en' => 'Xiaohong',
                'license_number' => 'ZL31001',
                'qualification_number' => '31001',
                'license_date' => '2016-03-15',
                'agency' => '上海华诚', // 对应上海华诚
                'phone' => '13800138002',
                'email' => 'li.xiaohong@huacheng.com',
                'gender' => '女',
                'license_expiry' => '2026-03-15',
                'specialty' => '商标注册、商标维权',
                'is_default_agent' => true,
                'is_valid' => true,
                'credit_rating' => 'A+',
                'status' => 1,
                'remarks' => '商标业务专家，具有丰富的商标争议处理经验',
                'creator' => '系统管理员',
                'creation_time' => Carbon::now(),
                'modifier' => '系统管理员',
                'update_time' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 3,
                'name_cn' => '王建国',
                'last_name_cn' => '王',
                'first_name_cn' => '建国',
                'name_en' => 'Wang Jianguo',
                'last_name_en' => 'Wang',
                'first_name_en' => 'Jianguo',
                'license_number' => 'ZL44001',
                'qualification_number' => '44001',
                'license_date' => '2017-09-20',
                'agency' => '广州德恒', // 对应广州德恒
                'phone' => '13800138003',
                'email' => 'wang.jianguo@deheng.com',
                'gender' => '男',
                'license_expiry' => '2027-09-20',
                'specialty' => '软件著作权、作品著作权',
                'is_default_agent' => false,
                'is_valid' => true,
                'credit_rating' => 'A',
                'status' => 1,
                'remarks' => '版权业务专业，熟悉软件行业知识产权保护',
                'creator' => '系统管理员',
                'creation_time' => Carbon::now(),
                'modifier' => '系统管理员',
                'update_time' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 4,
                'name_cn' => '陈美玲',
                'last_name_cn' => '陈',
                'first_name_cn' => '美玲',
                'name_en' => 'Chen Meiling',
                'last_name_en' => 'Chen',
                'first_name_en' => 'Meiling',
                'license_number' => 'ZL44002',
                'qualification_number' => '44002',
                'license_date' => '2019-12-01',
                'agency' => '深圳创新', // 对应深圳创新
                'phone' => '13800138004',
                'email' => 'chen.meiling@innovation.com',
                'gender' => '女',
                'license_expiry' => '2029-12-01',
                'specialty' => '电子信息技术专利、计算机软件专利',
                'is_default_agent' => true,
                'is_valid' => true,
                'credit_rating' => 'A',
                'status' => 1,
                'remarks' => '高新技术专利专家，深圳科技园区资深代理师',
                'creator' => '系统管理员',
                'creation_time' => Carbon::now(),
                'modifier' => '系统管理员',
                'update_time' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 5,
                'name_cn' => '约翰·史密斯',
                'last_name_cn' => '史密斯',
                'first_name_cn' => '约翰',
                'name_en' => 'John Smith',
                'last_name_en' => 'Smith',
                'first_name_en' => 'John',
                'license_number' => 'US001',
                'qualification_number' => '001',
                'license_date' => '2010-05-15',
                'agency' => '美国专利代理公司', // 对应美国专利代理公司
                'phone' => '+1-408-555-0001',
                'email' => 'john.smith@uspatent.com',
                'gender' => '男',
                'license_expiry' => '2030-05-15',
                'specialty' => 'US Patent Application, PCT Application',
                'is_default_agent' => true,
                'is_valid' => true,
                'credit_rating' => 'A+',
                'status' => 1,
                'remarks' => '美国注册专利代理师，具有丰富的美国专利申请经验',
                'creator' => '系统管理员',
                'creation_time' => Carbon::now(),
                'modifier' => '系统管理员',
                'update_time' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('agents')->count() == 0) {
            DB::table('agents')->insert($agents);
        }
    }
}
