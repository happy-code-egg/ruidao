<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_id')->comment('支出单ID');
            $table->string('operation')->comment('操作类型：create、update、submit、approve、reject');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作人ID');
            $table->string('operator_name')->nullable()->comment('操作人名称');
            $table->text('remark')->nullable()->comment('操作备注');
            $table->timestamp('created_at')->nullable();
            
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->index('expense_id');
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
        Schema::dropIfExists('expense_history');
    }
}

