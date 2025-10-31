<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique()->comment('出款单号');
            $table->string('expense_name')->comment('出款单名称（谁收钱）');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->unsignedBigInteger('company_id')->nullable()->comment('出款公司ID（我方公司）');
            $table->string('company_name')->nullable()->comment('出款公司名称');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('总金额');
            $table->date('expense_date')->nullable()->comment('出款日期');
            $table->string('status')->default('draft')->comment('状态：draft(草稿)、submitted(已提交)、approved(已审批)、rejected(已拒绝)');
            $table->unsignedBigInteger('creator_id')->nullable()->comment('创建人ID');
            $table->string('creator_name')->nullable()->comment('创建人名称');
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('modifier_id')->nullable()->comment('修改人ID');
            $table->string('modifier_name')->nullable()->comment('修改人名称');
            $table->timestamp('updated_at')->nullable();
            $table->text('remark')->nullable()->comment('备注');
            $table->softDeletes();
            
            $table->index('customer_id');
            $table->index('company_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}

