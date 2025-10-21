<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerFilesSeeder extends Seeder
{
    /**
     * 运行客户文件数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customer_files')->truncate();

        $now = Carbon::now();

        // 创建示例文件数据
        $files = [
            [
                'id' => 1,
                'customer_id' => 1,
                'file_name' => '营业执照.pdf',
                'file_original_name' => '上海睿道知识产权有限公司营业执照.pdf',
                'file_path' => '/uploads/customer_files/2024/01/business_license_' . time() . '.pdf',
                'file_type' => 'pdf',
                'file_category' => '证件资料',
                'file_size' => 1048576, // 1MB
                'mime_type' => 'application/pdf',
                'file_description' => '公司营业执照扫描件',
                'is_private' => false,
                'uploaded_by' => 1,
                'remark' => '最新版营业执照，有效期至2034年',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'file_name' => '高新技术企业认定证书.pdf',
                'file_original_name' => '高新技术企业认定证书2020年.pdf',
                'file_path' => '/uploads/customer_files/2024/01/hightech_cert_' . time() . '.pdf',
                'file_type' => 'pdf',
                'file_category' => '资质证书',
                'file_size' => 2097152, // 2MB
                'mime_type' => 'application/pdf',
                'file_description' => '高新技术企业认定证书',
                'is_private' => false,
                'uploaded_by' => 1,
                'remark' => '2020年获得，有效期3年',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'customer_id' => 1,
                'file_name' => '服务合同模板.docx',
                'file_original_name' => '知识产权代理服务合同模板v2.0.docx',
                'file_path' => '/uploads/customer_files/2024/01/contract_template_' . time() . '.docx',
                'file_type' => 'docx',
                'file_category' => '合同文件',
                'file_size' => 524288, // 512KB
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_description' => '标准服务合同模板',
                'is_private' => false,
                'uploaded_by' => 1,
                'remark' => '最新版合同模板，已法务审核',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'customer_id' => 1,
                'file_name' => '技术交底书示例.pdf',
                'file_original_name' => 'AI专利申请技术交底书示例.pdf',
                'file_path' => '/uploads/customer_files/2024/01/tech_disclosure_' . time() . '.pdf',
                'file_type' => 'pdf',
                'file_category' => '技术资料',
                'file_size' => 3145728, // 3MB
                'mime_type' => 'application/pdf',
                'file_description' => 'AI相关专利技术交底书参考示例',
                'is_private' => true,
                'uploaded_by' => 1,
                'remark' => '内部技术资料，保密级别高',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'customer_id' => 1,
                'file_name' => '公司介绍.pptx',
                'file_original_name' => '上海睿道知识产权有限公司介绍2024.pptx',
                'file_path' => '/uploads/customer_files/2024/01/company_intro_' . time() . '.pptx',
                'file_type' => 'pptx',
                'file_category' => '宣传资料',
                'file_size' => 5242880, // 5MB
                'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'file_description' => '公司宣传介绍PPT',
                'is_private' => false,
                'uploaded_by' => 1,
                'remark' => '用于客户展示和商务洽谈',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('customer_files')->insert($files);
        
        $this->command->info('客户文件数据种子已成功植入！');
    }
}
