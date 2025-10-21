<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToCaseProcessesTable extends Migration
{
    /**
     * Run the migrations.
     * 为处理事项表添加缺失字段
     * @return void
     */
    public function up()
    {
        Schema::table('case_processes', function (Blueprint $table) {
            // 添加发文日期字段
            if (!Schema::hasColumn('case_processes', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('expected_complete_date')->comment('发文日期');
            }
            
            // 添加项目阶段字段
            if (!Schema::hasColumn('case_processes', 'case_stage')) {
                $table->string('case_stage', 50)->nullable()->after('issue_date')->comment('项目阶段');
            }
            
            // 添加核稿人字段
            if (!Schema::hasColumn('case_processes', 'reviewer')) {
                $table->bigInteger('reviewer')->nullable()->after('case_stage')->comment('核稿人ID');
            }
            
            // 添加合同代码字段
            if (!Schema::hasColumn('case_processes', 'contract_code')) {
                $table->string('contract_code', 100)->nullable()->after('reviewer')->comment('合同代码');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('case_processes', function (Blueprint $table) {
            $columns = [
                'issue_date',
                'case_stage', 
                'reviewer',
                'contract_code'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('case_processes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
