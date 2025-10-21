<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_sorts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->string('list_type', 50)->comment('列表类型：allCustomer, myCustomer, deptCustomer, publicCustomer');
            $table->integer('sort_order')->comment('排序序号');
            $table->timestamps();
            
            // 索引
            $table->index(['user_id', 'list_type'], 'idx_user_list');
            $table->index(['customer_id'], 'idx_customer');
            $table->unique(['user_id', 'customer_id', 'list_type'], 'uk_user_customer_list');
            
            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_sorts');
    }
}
