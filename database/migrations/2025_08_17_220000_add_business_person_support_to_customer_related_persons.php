<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessPersonSupportToCustomerRelatedPersons extends Migration
{
    /**
     * Run the migrations.
     * 为客户相关人员表添加业务员支持
     * @return void
     */
    public function up()
    {
        // 更新人员类型常量，添加业务员类型
        // 这个迁移主要是为了确保数据一致性，实际的字段已经存在
        
        // 可以在这里添加一些数据初始化逻辑
        // 例如：将现有客户的业务员信息迁移到 customer_related_persons 表
        
        // 暂时不做任何表结构修改，因为需要的字段已经存在
        // 如果需要添加索引或其他优化，可以在这里添加
        
        Schema::table('customer_related_persons', function (Blueprint $table) {
            // 添加复合索引以提高查询性能
            $table->index(['customer_id', 'person_type'], 'idx_customer_person_type');
            $table->index(['customer_id', 'related_business_person_id'], 'idx_customer_related_business');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_related_persons', function (Blueprint $table) {
            $table->dropIndex('idx_customer_person_type');
            $table->dropIndex('idx_customer_related_business');
        });
    }
}
