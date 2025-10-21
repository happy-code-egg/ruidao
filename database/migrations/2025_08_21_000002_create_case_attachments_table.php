<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseAttachmentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('case_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('case_id')->index();
            $table->string('file_type', 100)->nullable();
            $table->string('file_sub_type', 100)->nullable();
            $table->string('file_desc', 500)->nullable();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_attachments');
    }
};


