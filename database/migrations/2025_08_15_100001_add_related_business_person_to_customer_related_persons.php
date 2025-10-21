<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelatedBusinessPersonToCustomerRelatedPersons extends Migration
{
    /**
     * Run the migrations.
     * 为客户相关人员表添加关联业务人员字段
     * @return void
     */
    public function up()
    {
        Schema::table('customer_related_persons', function (Blueprint $table) {
            $table->bigInteger('related_business_person_id')->nullable()->after('customer_id')->comment('关联业务人员ID（关联users表）');
            $table->index(['related_business_person_id']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_related_persons', function (Blueprint $table) {
            $table->dropIndex(['related_business_person_id']);
            $table->dropColumn('related_business_person_id');
        });
    }
}
