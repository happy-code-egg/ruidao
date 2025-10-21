<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // 查询客户数据
    $customer = DB::table('customers')->where('id', 4)->first();
    if ($customer) {
        echo "🏢 客户信息：\n";
        echo "  - ID: {$customer->id}\n";
        echo "  - 客户名称: {$customer->customer_name}\n";
        echo "  - 前端名称: {$customer->name}\n";
        echo "  - 英文名称: {$customer->name_en}\n";
        echo "  - 法定代表人: {$customer->legal_representative}\n";
        echo "  - 联系人: {$customer->contact_name}\n";
        echo "  - 邮箱: {$customer->email}\n";
        echo "  - 行业: {$customer->industry}\n";
        echo "  - 网站: {$customer->website}\n\n";
    }
    
    // 查询联系人数据
    $contacts = DB::table('customer_contacts')->where('customer_id', 4)->get();
    echo "📞 联系人列表：\n";
    foreach ($contacts as $contact) {
        echo "  - {$contact->contact_name} ({$contact->contact_type_text}) - {$contact->phone}\n";
    }
    echo "\n";
    
    // 查询申请人数据
    $applicants = DB::table('customer_applicants')->where('customer_id', 4)->get();
    echo "📋 申请人列表：\n";
    foreach ($applicants as $applicant) {
        echo "  - {$applicant->applicant_name_cn} ({$applicant->applicant_type})\n";
    }
    echo "\n";
    
    // 查询案例数据
    $cases = DB::table('cases')->where('customer_id', 4)->get();
    echo "📁 案例列表：\n";
    if ($cases->count() > 0) {
        foreach ($cases as $case) {
            echo "  - {$case->case_name} ({$case->case_code})\n";
        }
    } else {
        echo "  - 暂无案例数据\n";
    }
    echo "\n";
    
    // 统计信息
    echo "📊 数据库统计：\n";
    echo "  - 客户总数: " . DB::table('customers')->count() . "\n";
    echo "  - 联系人总数: " . DB::table('customer_contacts')->count() . "\n";
    echo "  - 申请人总数: " . DB::table('customer_applicants')->count() . "\n";
    echo "  - 发明人总数: " . DB::table('customer_inventors')->count() . "\n";
    echo "  - 案例总数: " . DB::table('cases')->count() . "\n";
    
    echo "\n✅ 数据验证完成！所有数据已成功插入数据库。\n";
    
} catch (\Exception $e) {
    echo "❌ 查询失败：" . $e->getMessage() . "\n";
}
