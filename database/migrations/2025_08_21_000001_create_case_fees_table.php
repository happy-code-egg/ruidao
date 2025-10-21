<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseFeesTable extends Migration
{
    public function up(): void
    {
        Schema::create('case_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('case_id')->index();
            $table->string('fee_type', 20)->index(); // service | official
            $table->string('fee_name', 200);
            $table->string('fee_description', 500)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('RMB');
            $table->string('remarks', 500)->nullable();
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_fees');
    }
};


