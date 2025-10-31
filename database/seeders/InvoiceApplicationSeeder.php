<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceApplication;
use App\Models\InvoiceApplicationHistory;
use App\Models\Customer;
use App\Models\Contract;
use Carbon\Carbon;

class InvoiceApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 获取一些客户和合同
        $customers = Customer::take(5)->get();
        $contracts = Contract::take(5)->get();

        if ($customers->isEmpty() || $contracts->isEmpty()) {
            $this->command->info('没有足够的客户或合同数据，跳过发票申请数据生成');
            return;
        }

        $statuses = ['draft', 'reviewing', 'approved', 'rejected', 'completed'];
        $invoiceTypes = ['special', 'ordinary', 'electronic'];
        $priorities = ['normal', 'urgent', 'very_urgent'];

        // 创建20条测试数据
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $contract = $contracts->random();
            $status = $statuses[array_rand($statuses)];
            $invoiceType = $invoiceTypes[array_rand($invoiceTypes)];
            $priority = $priorities[array_rand($priorities)];

            $invoice = InvoiceApplication::create([
                'application_no' => InvoiceApplication::generateApplicationNo(),
                'application_date' => Carbon::now()->subDays(rand(0, 30)),
                'applicant' => '测试申请人' . $i,
                'department' => ['财务部', '销售部', '市场部'][rand(0, 2)],
                'customer_id' => $customer->id,
                'customer_name' => $customer->name ?? $customer->customer_name,
                'customer_no' => $customer->customer_no,
                'contract_id' => $contract->id,
                'contract_name' => $contract->contract_name,
                'contract_no' => $contract->contract_no,
                'buyer_name' => $customer->name ?? $customer->customer_name,
                'buyer_tax_id' => $customer->invoice_credit_code ?? $customer->credit_code,
                'buyer_address' => ($customer->invoice_address ?? $customer->address ?? '') . ' ' . ($customer->invoice_phone ?? $customer->contact_phone ?? ''),
                'buyer_bank_account' => ($customer->bank_name ?? '') . ' ' . ($customer->bank_account ?? ''),
                'invoice_type' => $invoiceType,
                'invoice_amount' => rand(10000, 500000) / 100,
                'items' => [
                    [
                        'name' => '服务费',
                        'specification' => '知识产权服务',
                        'unit' => '项',
                        'quantity' => 1,
                        'price' => rand(10000, 500000) / 100,
                        'amount' => rand(10000, 500000) / 100,
                        'taxRate' => 6,
                    ]
                ],
                'flow_status' => $status,
                'current_handler' => $status === 'reviewing' ? '审批人' . rand(1, 3) : null,
                'priority' => $priority,
                'invoice_number' => $status === 'completed' ? 'FP' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                'invoice_date' => $status === 'completed' ? Carbon::now()->subDays(rand(0, 10)) : null,
                'invoice_files' => $status === 'completed' ? [
                    [
                        'name' => '发票文件.pdf',
                        'url' => '/storage/invoices/test_invoice.pdf',
                        'size' => rand(100000, 1000000),
                    ]
                ] : null,
                'upload_remark' => $status === 'completed' ? '发票已上传' : null,
                'approval_comment' => in_array($status, ['approved', 'rejected', 'completed']) ? '审批意见' . $i : null,
                'approved_at' => in_array($status, ['approved', 'completed']) ? Carbon::now()->subDays(rand(0, 5)) : null,
                'approved_by' => in_array($status, ['approved', 'completed']) ? 1 : null,
                'remark' => '测试备注' . $i,
                'created_by' => 1,
            ]);

            // 创建历史记录
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '创建申请',
                'handler' => '测试申请人' . $i,
                'action' => 'create',
                'comment' => '创建发票申请',
                'type' => 'primary',
            ]);

            if ($status !== 'draft') {
                InvoiceApplicationHistory::create([
                    'invoice_application_id' => $invoice->id,
                    'title' => '提交审批',
                    'handler' => '测试申请人' . $i,
                    'action' => 'submit',
                    'comment' => '提交审批',
                    'type' => 'info',
                ]);
            }

            if (in_array($status, ['approved', 'completed'])) {
                InvoiceApplicationHistory::create([
                    'invoice_application_id' => $invoice->id,
                    'title' => '审批通过',
                    'handler' => '审批人1',
                    'action' => 'approve',
                    'comment' => '审批通过',
                    'type' => 'success',
                ]);
            }

            if ($status === 'rejected') {
                InvoiceApplicationHistory::create([
                    'invoice_application_id' => $invoice->id,
                    'title' => '审批退回',
                    'handler' => '审批人1',
                    'action' => 'reject',
                    'comment' => '需要补充材料',
                    'type' => 'danger',
                ]);
            }

            if ($status === 'completed') {
                InvoiceApplicationHistory::create([
                    'invoice_application_id' => $invoice->id,
                    'title' => '发票上传',
                    'handler' => '财务人员',
                    'action' => 'upload',
                    'comment' => '发票已上传，发票号码：' . $invoice->invoice_number,
                    'type' => 'success',
                ]);
            }
        }

        $this->command->info('成功创建20条发票申请测试数据');
    }
}

