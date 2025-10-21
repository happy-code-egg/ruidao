<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加缺失的字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加申请方式字段
            if (!Schema::hasColumn('cases', 'application_method')) {
                $table->string('application_method', 50)->default('电子申请')->after('application_type')->comment('申请方式');
            }
            
            // 添加项目流向字段
            if (!Schema::hasColumn('cases', 'case_direction')) {
                $table->string('case_direction', 20)->default('内-内')->after('application_method')->comment('项目流向');
            }
            
            // 添加提案号字段
            if (!Schema::hasColumn('cases', 'proposal_no')) {
                $table->string('proposal_no', 100)->nullable()->after('case_direction')->comment('提案号');
            }
            
            // 添加公司字段
            if (!Schema::hasColumn('cases', 'company')) {
                $table->string('company', 200)->nullable()->after('proposal_no')->comment('公司');
            }
            
            // 添加合同编号字段
            if (!Schema::hasColumn('cases', 'contract_number')) {
                $table->string('contract_number', 100)->nullable()->after('company')->comment('合同编号');
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
            $columns = [
                'application_method',
                'case_direction',
                'proposal_no',
                'company',
                'contract_number'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
