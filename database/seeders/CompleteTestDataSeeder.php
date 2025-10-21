<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\User;
use App\Models\OurCompanies;
use Carbon\Carbon;

class CompleteTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 创建完整的测试数据，包括客户、联系人、合同等
     */
    public function run()
    {
        $this->command->info('开始创建完整的测试数据...');

        // 1. 确保有用户数据
        $this->createUsers();

        // 2. 确保有我方公司数据
        $this->createOurCompanies();

        // 3. 创建客户数据
        $this->createCustomers();

        // 4. 创建客户联系人数据
        $this->createCustomerContacts();

        // 5. 创建合同数据
        $this->createContracts();

        $this->command->info('完整测试数据创建完成！');
    }

    /**
     * 创建用户数据
     */
    private function createUsers()
    {
        $users = [
            [
                'username' => 'zhangsan',
                'email' => 'zhangsan@example.com',
                'password' => bcrypt('123456'),
                'real_name' => '张三',
                'phone' => '13800138001',
                'department_id' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'lisi',
                'email' => 'lisi@example.com',
                'password' => bcrypt('123456'),
                'real_name' => '李四',
                'phone' => '13800138002',
                'department_id' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'wangwu',
                'email' => 'wangwu@example.com',
                'password' => bcrypt('123456'),
                'real_name' => '王五',
                'phone' => '13800138003',
                'department_id' => 2,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'zhaoliu',
                'email' => 'zhaoliu@example.com',
                'password' => bcrypt('123456'),
                'real_name' => '赵六',
                'phone' => '13800138004',
                'department_id' => 2,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'sunqi',
                'email' => 'sunqi@example.com',
                'password' => bcrypt('123456'),
                'real_name' => '孙七',
                'phone' => '13800138005',
                'department_id' => 3,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }

        $this->command->info('用户数据创建完成');
    }

    /**
     * 创建我方公司数据
     */
    private function createOurCompanies()
    {
        $companies = [
            [
                'name' => '睿道知识产权代理有限公司',
                'code' => 'rd_ip',
                'short_name' => '睿道知识产权',
                'full_name' => '睿道知识产权代理有限公司',
                'credit_code' => '91110000123456789B',
                'address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'contact_person' => '陈总',
                'contact_phone' => '010-12345678',
                'tax_number' => '91110000123456789B',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => '睿道知识产权代理有限公司深圳分公司',
                'code' => 'rd_ip_sz',
                'short_name' => '睿道深圳',
                'full_name' => '睿道知识产权代理有限公司深圳分公司',
                'credit_code' => '91440300123456789C',
                'address' => '深圳市南山区科技园南区深圳湾科技生态园10栋A座',
                'contact_person' => '李总',
                'contact_phone' => '0755-87654321',
                'tax_number' => '91440300123456789C',
                'status' => 1,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($companies as $company) {
            DB::table('our_companies')->updateOrInsert(
                ['name' => $company['name']],
                $company
            );
        }

        $this->command->info('我方公司数据创建完成');
    }

    /**
     * 创建客户数据
     */
    private function createCustomers()
    {
        $users = DB::table('users')->get();
        $customers = [
            [
                'customer_code' => 'C202501001',
                'customer_name' => '北京科技创新有限公司',
                'name' => '北京科技创新有限公司',
                'name_en' => 'Beijing Technology Innovation Co., Ltd.',
                'credit_code' => '91110000123456789A',
                'customer_type' => 1,
                'customer_level' => 1,
                'level' => 'A',
                'legal_representative' => '张科技',
                'company_manager' => '张总',
                'employee_count' => '150',
                'industry' => '软件和信息技术服务业',
                'business_person_id' => $users[0]->id,
                'business_person' => $users[0]->real_name,
                'business_assistant_id' => $users[1]->id,
                'business_assistant' => $users[1]->real_name,
                'business_partner_id' => $users[2]->id,
                'business_partner' => $users[2]->real_name,
                'price_index_str' => '85',
                'innovation_index_str' => '92',
                'contract_count_str' => '5',
                'latest_contract_date_str' => '2024-12-01',
                'creator' => $users[0]->real_name,
                'create_date' => '2024-01-15',
                'create_time' => '2024-01-15 10:30:00',
                'updater' => $users[0]->real_name,
                'update_time' => '2024-12-15 14:20:00',
                'remark' => '重要客户，技术实力强，与我公司合作关系良好',
                'contact_name' => '李联系',
                'contact_phone' => '010-12345678',
                'contact_email' => 'contact@bjkjcx.com',
                'email' => 'contact@bjkjcx.com',
                'qq' => '123456789',
                'wechat' => 'bjkjcx_tech',
                'country' => '中国',
                'address' => '北京市海淀区中关村大街1号',
                'website' => 'www.bjkjcx.com',
                'customer_status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_code' => 'C202501002',
                'customer_name' => '上海智能科技有限公司',
                'name' => '上海智能科技有限公司',
                'name_en' => 'Shanghai Smart Technology Co., Ltd.',
                'credit_code' => '91310000123456789B',
                'customer_type' => 1,
                'customer_level' => 2,
                'level' => 'B',
                'legal_representative' => '王智能',
                'company_manager' => '王总',
                'employee_count' => '80',
                'industry' => '人工智能',
                'business_person_id' => $users[1]->id,
                'business_person' => $users[1]->real_name,
                'business_assistant_id' => $users[2]->id,
                'business_assistant' => $users[2]->real_name,
                'business_partner_id' => $users[3]->id,
                'business_partner' => $users[3]->real_name,
                'price_index_str' => '78',
                'innovation_index_str' => '88',
                'contract_count_str' => '3',
                'latest_contract_date_str' => '2024-11-15',
                'creator' => $users[1]->real_name,
                'create_date' => '2024-02-20',
                'create_time' => '2024-02-20 09:15:00',
                'updater' => $users[1]->real_name,
                'update_time' => '2024-12-10 16:30:00',
                'remark' => '新兴AI公司，发展潜力大',
                'contact_name' => '陈联系',
                'contact_phone' => '021-87654321',
                'contact_email' => 'contact@shsmart.com',
                'email' => 'contact@shsmart.com',
                'qq' => '987654321',
                'wechat' => 'shsmart_ai',
                'country' => '中国',
                'address' => '上海市浦东新区张江高科技园区',
                'website' => 'www.shsmart.com',
                'customer_status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_code' => 'C202501003',
                'customer_name' => '深圳电子制造有限公司',
                'name' => '深圳电子制造有限公司',
                'name_en' => 'Shenzhen Electronics Manufacturing Co., Ltd.',
                'credit_code' => '91440300123456789C',
                'customer_type' => 1,
                'customer_level' => 1,
                'level' => 'A',
                'legal_representative' => '赵制造',
                'company_manager' => '赵总',
                'employee_count' => '300',
                'industry' => '电子制造业',
                'business_person_id' => $users[2]->id,
                'business_person' => $users[2]->real_name,
                'business_assistant_id' => $users[3]->id,
                'business_assistant' => $users[3]->real_name,
                'business_partner_id' => $users[4]->id,
                'business_partner' => $users[4]->real_name,
                'price_index_str' => '92',
                'innovation_index_str' => '85',
                'contract_count_str' => '8',
                'latest_contract_date_str' => '2024-12-20',
                'creator' => $users[2]->real_name,
                'create_date' => '2024-03-10',
                'create_time' => '2024-03-10 14:45:00',
                'updater' => $users[2]->real_name,
                'update_time' => '2024-12-25 11:20:00',
                'remark' => '大型制造企业，专利需求量大',
                'contact_name' => '孙联系',
                'contact_phone' => '0755-12345678',
                'contact_email' => 'contact@szem.com',
                'email' => 'contact@szem.com',
                'qq' => '555666777',
                'wechat' => 'szem_tech',
                'country' => '中国',
                'address' => '深圳市南山区科技园南区',
                'website' => 'www.szem.com',
                'customer_status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_code' => 'C202501004',
                'customer_name' => '杭州互联网科技有限公司',
                'name' => '杭州互联网科技有限公司',
                'name_en' => 'Hangzhou Internet Technology Co., Ltd.',
                'credit_code' => '91330000123456789D',
                'customer_type' => 1,
                'customer_level' => 2,
                'level' => 'B',
                'legal_representative' => '李互联网',
                'company_manager' => '李总',
                'employee_count' => '120',
                'industry' => '互联网服务',
                'business_person_id' => $users[3]->id,
                'business_person' => $users[3]->real_name,
                'business_assistant_id' => $users[4]->id,
                'business_assistant' => $users[4]->real_name,
                'business_partner_id' => $users[0]->id,
                'business_partner' => $users[0]->real_name,
                'price_index_str' => '75',
                'innovation_index_str' => '90',
                'contract_count_str' => '4',
                'latest_contract_date_str' => '2024-11-30',
                'creator' => $users[3]->real_name,
                'create_date' => '2024-04-05',
                'create_time' => '2024-04-05 16:20:00',
                'updater' => $users[3]->real_name,
                'update_time' => '2024-12-18 10:15:00',
                'remark' => '互联网公司，商标需求较多',
                'contact_name' => '周联系',
                'contact_phone' => '0571-87654321',
                'contact_email' => 'contact@hzit.com',
                'email' => 'contact@hzit.com',
                'qq' => '111222333',
                'wechat' => 'hzit_web',
                'country' => '中国',
                'address' => '杭州市西湖区文三路',
                'website' => 'www.hzit.com',
                'customer_status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_code' => 'C202501005',
                'customer_name' => '成都生物医药有限公司',
                'name' => '成都生物医药有限公司',
                'name_en' => 'Chengdu Biopharmaceutical Co., Ltd.',
                'credit_code' => '91510000123456789E',
                'customer_type' => 1,
                'customer_level' => 1,
                'level' => 'A',
                'legal_representative' => '刘医药',
                'company_manager' => '刘总',
                'employee_count' => '200',
                'industry' => '生物医药',
                'business_person_id' => $users[4]->id,
                'business_person' => $users[4]->real_name,
                'business_assistant_id' => $users[0]->id,
                'business_assistant' => $users[0]->real_name,
                'business_partner_id' => $users[1]->id,
                'business_partner' => $users[1]->real_name,
                'price_index_str' => '88',
                'innovation_index_str' => '95',
                'contract_count_str' => '6',
                'latest_contract_date_str' => '2024-12-28',
                'creator' => $users[4]->real_name,
                'create_date' => '2024-05-12',
                'create_time' => '2024-05-12 11:30:00',
                'updater' => $users[4]->real_name,
                'update_time' => '2024-12-30 15:45:00',
                'remark' => '生物医药企业，专利技术含量高',
                'contact_name' => '吴联系',
                'contact_phone' => '028-12345678',
                'contact_email' => 'contact@cdbio.com',
                'email' => 'contact@cdbio.com',
                'qq' => '444555666',
                'wechat' => 'cdbio_pharma',
                'country' => '中国',
                'address' => '成都市高新区天府大道',
                'website' => 'www.cdbio.com',
                'customer_status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->updateOrInsert(
                ['customer_code' => $customer['customer_code']],
                $customer
            );
        }

        $this->command->info('客户数据创建完成');
    }

    /**
     * 创建客户联系人数据
     */
    private function createCustomerContacts()
    {
        $customers = DB::table('customers')->get();
        $contacts = [];

        foreach ($customers as $customer) {
            // 每个客户创建2-3个联系人
            $customerContacts = [
                [
                    'customer_id' => $customer->id,
                    'contact_name' => $customer->contact_name ?: '主要联系人',
                    'phone' => $customer->contact_phone ?: '13800138000',
                    'email' => $customer->contact_email ?: 'contact@example.com',
                    'position' => '总经理',
                    'is_primary' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'customer_id' => $customer->id,
                    'contact_name' => '技术联系人',
                    'phone' => str_replace('12345678', '87654321', $customer->contact_phone),
                    'email' => 'tech@example.com',
                    'position' => '技术总监',
                    'is_primary' => 0,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'customer_id' => $customer->id,
                    'contact_name' => '法务联系人',
                    'phone' => str_replace('12345678', '11223344', $customer->contact_phone),
                    'email' => 'legal@example.com',
                    'position' => '法务经理',
                    'is_primary' => 0,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            $contacts = array_merge($contacts, $customerContacts);
        }

        foreach ($contacts as $contact) {
            DB::table('customer_contacts')->updateOrInsert(
                [
                    'customer_id' => $contact['customer_id'],
                    'contact_name' => $contact['contact_name']
                ],
                $contact
            );
        }

        $this->command->info('客户联系人数据创建完成');
    }

    /**
     * 创建合同数据
     */
    private function createContracts()
    {
        $customers = DB::table('customers')->get();
        $users = DB::table('users')->get();
        $companies = DB::table('our_companies')->get();

        if ($customers->isEmpty() || $users->isEmpty() || $companies->isEmpty()) {
            $this->command->warn('缺少必要的关联数据，无法创建合同');
            return;
        }

        $contracts = [
            [
                'contract_no' => 'HT202501001',
                'contract_code' => 'HT-ZL-001',
                'contract_name' => '智能家居控制系统专利申请服务合同',
                'customer_id' => $customers[0]->id,
                'service_type' => 'patent',
                'status' => '草稿',
                'summary' => '为客户提供智能家居控制系统发明专利申请服务，包括技术交底书撰写、专利申请文件准备、申请递交等全流程服务。',
                'business_person_id' => $users[0]->id,
                'technical_director_id' => $users[1]->id,
                'technical_department' => '专利技术部',
                'paper_status' => '是',
                'party_a_contact_id' => null,
                'party_a_phone' => '010-12345678',
                'party_a_email' => 'contact@bjkjcx.com',
                'party_a_address' => '北京市海淀区中关村大街1号',
                'party_b_signer_id' => $users[0]->id,
                'party_b_phone' => '13800138001',
                'party_b_company_id' => $companies[0]->id,
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 8000.00,
                'official_fee' => 950.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 8000.00,
                'total_amount' => 8950.00,
                'case_count' => 1,
                'opportunity_no' => 'SJ2025001',
                'opportunity_name' => '智能家居专利申请项目',
                'signing_date' => Carbon::now()->subDays(10),
                'validity_start_date' => Carbon::now()->subDays(10),
                'validity_end_date' => Carbon::now()->addMonths(12),
                'additional_terms' => '1. 如遇审查意见，免费提供一次答复服务；2. 专利授权后提供电子版证书；3. 提供专利年费缴纳提醒服务。',
                'remark' => '重要客户，优先处理',
                'last_process_time' => Carbon::now()->subDays(3),
                'process_remark' => '技术交底书已完成，正在撰写申请文件。',
                'created_by' => $users[0]->id,
                'updated_by' => $users[0]->id,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'contract_no' => 'HT202501002',
                'contract_code' => 'HT-SB-002',
                'contract_name' => '睿道商标注册申请服务合同',
                'customer_id' => $customers[1]->id,
                'service_type' => 'trademark',
                'status' => '已确认',
                'summary' => '为客户提供"睿道"文字及图形商标注册申请服务，涵盖第42类科学技术服务。',
                'business_person_id' => $users[1]->id,
                'technical_director_id' => $users[2]->id,
                'technical_department' => '商标业务部',
                'paper_status' => '否',
                'party_a_contact_id' => null,
                'party_a_phone' => '021-87654321',
                'party_a_email' => 'contact@shsmart.com',
                'party_a_address' => '上海市浦东新区张江高科技园区',
                'party_b_signer_id' => $users[1]->id,
                'party_b_phone' => '13900139002',
                'party_b_company_id' => $companies[0]->id,
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 2000.00,
                'official_fee' => 300.00,
                'channel_fee' => 200.00,
                'total_service_fee' => 2200.00,
                'total_amount' => 2500.00,
                'case_count' => 2,
                'opportunity_no' => 'SJ2025002',
                'opportunity_name' => '睿道商标注册项目',
                'signing_date' => Carbon::now()->subDays(20),
                'validity_start_date' => Carbon::now()->subDays(20),
                'validity_end_date' => Carbon::now()->addMonths(24),
                'additional_terms' => '1. 提供商标查询服务；2. 商标注册成功后提供电子版证书；3. 提供商标续展提醒服务。',
                'remark' => '商标注册项目',
                'last_process_time' => Carbon::now()->subDays(5),
                'process_remark' => '商标查询已完成，准备提交申请。',
                'created_by' => $users[1]->id,
                'updated_by' => $users[1]->id,
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'contract_no' => 'HT202501003',
                'contract_code' => 'HT-ZL-003',
                'contract_name' => '电子制造工艺专利申请服务合同',
                'customer_id' => $customers[2]->id,
                'service_type' => 'patent',
                'status' => '审批中',
                'summary' => '为客户提供电子制造工艺发明专利申请服务，包括工艺改进、设备优化等相关技术专利申请。',
                'business_person_id' => $users[2]->id,
                'technical_director_id' => $users[3]->id,
                'technical_department' => '专利技术部',
                'paper_status' => '是',
                'party_a_contact_id' => null,
                'party_a_phone' => '0755-12345678',
                'party_a_email' => 'contact@szem.com',
                'party_a_address' => '深圳市南山区科技园南区',
                'party_b_signer_id' => $users[2]->id,
                'party_b_phone' => '13700137003',
                'party_b_company_id' => $companies[1]->id,
                'party_b_address' => '深圳市南山区科技园南区深圳湾科技生态园10栋A座',
                'service_fee' => 12000.00,
                'official_fee' => 1900.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 12000.00,
                'total_amount' => 13900.00,
                'case_count' => 3,
                'opportunity_no' => 'SJ2025003',
                'opportunity_name' => '电子制造工艺专利项目',
                'signing_date' => Carbon::now()->subDays(30),
                'validity_start_date' => Carbon::now()->subDays(30),
                'validity_end_date' => Carbon::now()->addMonths(18),
                'additional_terms' => '1. 提供专利布局建议；2. 专利授权后提供专利分析报告；3. 提供专利年费管理服务。',
                'remark' => '大型制造企业项目',
                'last_process_time' => Carbon::now()->subDays(1),
                'process_remark' => '专利申请文件已提交，等待审查。',
                'created_by' => $users[2]->id,
                'updated_by' => $users[2]->id,
                'created_at' => Carbon::now()->subDays(35),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'contract_no' => 'HT202501004',
                'contract_code' => 'HT-SB-004',
                'contract_name' => '互联网平台商标注册服务合同',
                'customer_id' => $customers[3]->id,
                'service_type' => 'trademark',
                'status' => '已完成',
                'summary' => '为客户提供互联网平台相关商标注册申请服务，涵盖多个商品和服务类别。',
                'business_person_id' => $users[3]->id,
                'technical_director_id' => $users[4]->id,
                'technical_department' => '商标业务部',
                'paper_status' => '否',
                'party_a_contact_id' => null,
                'party_a_phone' => '0571-87654321',
                'party_a_email' => 'contact@hzit.com',
                'party_a_address' => '杭州市西湖区文三路',
                'party_b_signer_id' => $users[3]->id,
                'party_b_phone' => '13600136004',
                'party_b_company_id' => $companies[0]->id,
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 5000.00,
                'official_fee' => 600.00,
                'channel_fee' => 400.00,
                'total_service_fee' => 5400.00,
                'total_amount' => 6000.00,
                'case_count' => 5,
                'opportunity_no' => 'SJ2025004',
                'opportunity_name' => '互联网平台商标项目',
                'signing_date' => Carbon::now()->subDays(60),
                'validity_start_date' => Carbon::now()->subDays(60),
                'validity_end_date' => Carbon::now()->addMonths(36),
                'additional_terms' => '1. 提供商标监控服务；2. 商标注册成功后提供品牌保护建议；3. 提供商标维权服务。',
                'remark' => '互联网公司商标项目',
                'last_process_time' => Carbon::now()->subDays(30),
                'process_remark' => '商标注册已完成，证书已发放。',
                'created_by' => $users[3]->id,
                'updated_by' => $users[3]->id,
                'created_at' => Carbon::now()->subDays(65),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'contract_no' => 'HT202501005',
                'contract_code' => 'HT-ZL-005',
                'contract_name' => '生物医药专利布局服务合同',
                'customer_id' => $customers[4]->id,
                'service_type' => 'patent',
                'status' => '草稿',
                'summary' => '为客户提供生物医药领域专利布局服务，包括药物分子、制备方法、用途等相关专利申请。',
                'business_person_id' => $users[4]->id,
                'technical_director_id' => $users[0]->id,
                'technical_department' => '专利技术部',
                'paper_status' => '是',
                'party_a_contact_id' => null,
                'party_a_phone' => '028-12345678',
                'party_a_email' => 'contact@cdbio.com',
                'party_a_address' => '成都市高新区天府大道',
                'party_b_signer_id' => $users[4]->id,
                'party_b_phone' => '13500135005',
                'party_b_company_id' => $companies[0]->id,
                'party_b_address' => '北京市海淀区中关村南大街5号理工科技大厦1601室',
                'service_fee' => 25000.00,
                'official_fee' => 3800.00,
                'channel_fee' => 0.00,
                'total_service_fee' => 25000.00,
                'total_amount' => 28800.00,
                'case_count' => 8,
                'opportunity_no' => 'SJ2025005',
                'opportunity_name' => '生物医药专利布局项目',
                'signing_date' => Carbon::now()->subDays(5),
                'validity_start_date' => Carbon::now()->subDays(5),
                'validity_end_date' => Carbon::now()->addMonths(24),
                'additional_terms' => '1. 提供专利检索分析；2. 专利授权后提供专利价值评估；3. 提供专利运营建议。',
                'remark' => '生物医药重点项目',
                'last_process_time' => Carbon::now()->subDays(2),
                'process_remark' => '项目启动中，正在收集技术资料。',
                'created_by' => $users[4]->id,
                'updated_by' => $users[4]->id,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($contracts as $contract) {
            DB::table('contracts')->updateOrInsert(
                ['contract_no' => $contract['contract_no']],
                $contract
            );
        }

        $this->command->info('合同数据创建完成');
    }
}
