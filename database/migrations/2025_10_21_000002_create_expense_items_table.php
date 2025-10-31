<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_id')->comment('支出单ID');
            $table->string('request_no')->nullable()->comment('申请号');
            $table->date('request_date')->nullable()->comment('申请日期');
            $table->string('our_no')->nullable()->comment('我方文号');
            $table->string('case_name')->nullable()->comment('案件名称');
            $table->string('client_name')->nullable()->comment('客户名称');
            $table->string('applicant')->nullable()->comment('申请人');
            $table->string('process_item')->nullable()->comment('处理事项');
            $table->string('expense_name')->nullable()->comment('费用名称');
            $table->decimal('payable_amount', 15, 2)->default(0)->comment('应付金额');
            $table->date('payment_date')->nullable()->comment('支付日期');
            $table->date('actual_pay_date')->nullable()->comment('实付日期');
            $table->text('expense_remark')->nullable()->comment('费用备注');
            $table->string('cooperative_agency')->nullable()->comment('合作机构');
            $table->string('expense_type')->default('nonOfficial')->comment('费用类型：official(官费)、nonOfficial(非官费)');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->index('expense_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_items');
    }
}

