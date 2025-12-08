<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseTicketsTableDuplicate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->string('project_no', 100);
            $table->string('application_no', 100);
            $table->string('project_name', 200);
            $table->string('ticket_type', 20); // official_fee, agent_fee, other_fee
            $table->decimal('ticket_amount', 10, 2);
            $table->string('ticket_code', 50)->unique();
            $table->text('remark')->nullable();
            $table->unsignedInteger('created_by')->default(1);
            $table->timestamps();
            $table->softDeletes();
            
            // 外键约束
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('case_tickets');
    }
}