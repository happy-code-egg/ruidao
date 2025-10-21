<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesToCaseProcessesTable extends Migration
{
    /**
     * Run the migrations.
     * 为处理事项表添加费用字段
     * @return void
     */
    public function up()
    {
        Schema::table('case_processes', function (Blueprint $table) {
            $table->jsonb('service_fees')->nullable()->comment('服务费信息（JSON格式）')->after('attachments');
            $table->jsonb('official_fees')->nullable()->comment('官费信息（JSON格式）')->after('service_fees');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('case_processes', function (Blueprint $table) {
            $table->dropColumn(['service_fees', 'official_fees']);
        });
    }
}
