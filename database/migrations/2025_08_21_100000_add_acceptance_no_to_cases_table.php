<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptanceNoToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加acceptance_no字段（版权受理号）
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加版权受理号字段
            if (!Schema::hasColumn('cases', 'acceptance_no')) {
                $table->string('acceptance_no', 100)->nullable()->after('registration_date')->comment('受理号（版权）');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'acceptance_no')) {
                $table->dropColumn('acceptance_no');
            }
        });
    }
}
