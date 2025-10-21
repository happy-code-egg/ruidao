<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonitorFieldsToCaseFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case_fees', function (Blueprint $table) {
            $table->date('payment_deadline')->nullable()->comment('缴费期限');
            $table->date('receivable_date')->nullable()->comment('应收日期');
            $table->date('actual_receive_date')->nullable()->comment('实收日期');
            $table->tinyInteger('payment_status')->default(0)->comment('支付状态：0未缴费，1已缴费，2逾期');
            $table->boolean('is_reduction')->default(false)->comment('是否减缓');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case_fees', function (Blueprint $table) {
            $table->dropColumn([
                'payment_deadline',
                'receivable_date', 
                'actual_receive_date',
                'payment_status',
                'is_reduction'
            ]);
        });
    }
}
