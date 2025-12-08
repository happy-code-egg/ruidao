<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseTicketFilesTable extends Migration
{
    public function up()
    {
        Schema::create('case_ticket_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_id');
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->unsignedBigInteger('file_size');
            $table->string('file_type', 100)->nullable();
            $table->timestamps();
            $table->foreign('ticket_id')->references('id')->on('case_tickets')->onDelete('cascade');
            $table->index('ticket_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_ticket_files');
    }
}

