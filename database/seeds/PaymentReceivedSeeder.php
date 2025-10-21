<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentReceivedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('payment_received_requests')->truncate();
        DB::table('payment_receiveds')->truncate();

        // 获取一些客户、合同、用户ID用于关联
        $customers = DB::table('customers')->limit(10)->pluck('id')->toArray();
        $contracts = DB::table('contracts')->limit(10)->pluck('id')->toArray();
        $users = DB::table('users')->limit(5)->pluck('id')->toArray();
        $paymentRequests = DB::table('payment_requests')->where('request_status', 3)->limit(20)->pluck('id')->toArray();

        if (empty($customers)) {
            $this->command->warn('没有找到客户数据,请先创建客户数据');
            return;
        }

        if (empty($users)) {
            $this->command->warn('没有找到用户数据,请先创建用户数据');
            return;
        }

        $statuses = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4]; // 草稿、待认领、已认领、已核销
        $currencies = ['CNY', 'USD', 'EUR', 'JPY'];
        $paymentMethods = ['银行转账', '电汇', '支票', '现金'];
        $bankAccounts = [
            '工商银行 6222 **** **** 1234',
            '建设银行 6217 **** **** 5678',
            '招商银行 6225 **** **** 9012',
            '农业银行 6228 **** **** 3456'
        ];

        // 创建40条到款单数据
        for ($i = 1; $i <= 40; $i++) {
            $status = $statuses[array_rand($statuses)];
            $customerId = in_array($status, [3, 4]) ? $customers[array_rand($customers)] : null;
            $contractId = in_array($status, [3, 4]) && !empty($contracts) ? $contracts[array_rand($contracts)] : null;
            $createdBy = $users[array_rand($users)];
            $claimedBy = in_array($status, [3, 4]) ? $users[array_rand($users)] : null;
            
            $amount = rand(5000, 100000);
            $claimedAmount = in_array($status, [3, 4]) ? rand(0, $amount) : 0;
            $unclaimedAmount = $amount - $claimedAmount;
            
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            $receivedDate = $createdAt->copy()->subDays(rand(0, 5));
            $claimedAt = in_array($status, [3, 4]) ? $createdAt->copy()->addHours(rand(1, 72)) : null;

            $paymentReceivedId = DB::table('payment_receiveds')->insertGetId([
                'payment_no' => 'DK' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customerId,
                'contract_id' => $contractId,
                'status' => $status,
                'amount' => $amount,
                'claimed_amount' => $claimedAmount,
                'unclaimed_amount' => $unclaimedAmount,
                'currency' => $currencies[array_rand($currencies)],
                'payer' => '付款公司' . $i,
                'payer_account' => '****' . rand(1000, 9999),
                'bank_account' => $bankAccounts[array_rand($bankAccounts)],
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'transaction_ref' => 'TXN' . date('YmdHis') . rand(1000, 9999),
                'received_date' => $receivedDate->format('Y-m-d'),
                'claimed_by' => $claimedBy,
                'claimed_at' => $claimedAt,
                'remark' => $i % 3 == 0 ? '到款备注信息 ' . $i : null,
                'created_by' => $createdBy,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // 为已认领和已核销的到款单关联请款单
            if (in_array($status, [3, 4]) && !empty($paymentRequests)) {
                $requestCount = rand(1, min(3, count($paymentRequests)));
                $selectedRequests = array_rand(array_flip($paymentRequests), $requestCount);
                if (!is_array($selectedRequests)) {
                    $selectedRequests = [$selectedRequests];
                }

                foreach ($selectedRequests as $requestId) {
                    $allocatedAmount = rand(1000, min(10000, $unclaimedAmount));

                    DB::table('payment_received_requests')->insert([
                        'payment_received_id' => $paymentReceivedId,
                        'payment_request_id' => $requestId,
                        'allocated_amount' => $allocatedAmount,
                        'created_at' => $claimedAt,
                        'updated_at' => $claimedAt,
                    ]);
                }
            }
        }

        $this->command->info('成功创建40条到款单数据!');
        $this->command->info('草稿状态: ' . DB::table('payment_receiveds')->where('status', 1)->count() . ' 条');
        $this->command->info('待认领: ' . DB::table('payment_receiveds')->where('status', 2)->count() . ' 条');
        $this->command->info('已认领: ' . DB::table('payment_receiveds')->where('status', 3)->count() . ' 条');
        $this->command->info('已核销: ' . DB::table('payment_receiveds')->where('status', 4)->count() . ' 条');
    }
}

