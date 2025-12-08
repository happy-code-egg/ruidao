<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseTicketFilesTableDuplicate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_ticket_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->integer('file_size');
            $table->string('file_type', 100);
            $table->timestamps();
            
            // 外键约束
            $table->foreign('ticket_id')->references('id')->on('case_tickets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('case_ticket_files');
    }
}
