<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectFieldsToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加项目相关字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加项目相关字段
            if (!Schema::hasColumn('cases', 'project_year')) {
                $table->string('project_year', 10)->nullable()->after('author')->comment('项目年份');
            }
            if (!Schema::hasColumn('cases', 'apply_batch')) {
                $table->string('apply_batch', 100)->nullable()->after('project_year')->comment('申报批次');
            }
            if (!Schema::hasColumn('cases', 'tech_service_level')) {
                $table->string('tech_service_level', 50)->nullable()->after('apply_batch')->comment('科技服务级别');
            }
            if (!Schema::hasColumn('cases', 'supervisory_location')) {
                $table->string('supervisory_location', 100)->nullable()->after('tech_service_level')->comment('主管地');
            }
            if (!Schema::hasColumn('cases', 'supervisory_department')) {
                $table->string('supervisory_department', 100)->nullable()->after('supervisory_location')->comment('主管部门');
            }
            if (!Schema::hasColumn('cases', 'government_reward')) {
                $table->decimal('government_reward', 15, 2)->nullable()->after('supervisory_department')->comment('政府预估奖励');
            }
            if (!Schema::hasColumn('cases', 'cost_ratio')) {
                $table->string('cost_ratio', 50)->nullable()->after('government_reward')->comment('费用比例');
            }
            if (!Schema::hasColumn('cases', 'technical_contact')) {
                $table->string('technical_contact', 100)->nullable()->after('cost_ratio')->comment('技术对接');
            }
            if (!Schema::hasColumn('cases', 'is_urgent')) {
                $table->string('is_urgent', 10)->default('否')->after('technical_contact')->comment('是否加急');
            }
            if (!Schema::hasColumn('cases', 'case_handler')) {
                $table->string('case_handler', 100)->nullable()->after('is_urgent')->comment('项目处理人');
            }
            if (!Schema::hasColumn('cases', 'estimated_start_date')) {
                $table->date('estimated_start_date')->nullable()->after('case_handler')->comment('预计启动日');
            }
            if (!Schema::hasColumn('cases', 'internal_deadline')) {
                $table->date('internal_deadline')->nullable()->after('estimated_start_date')->comment('内部期限');
            }
            if (!Schema::hasColumn('cases', 'receipt_date')) {
                $table->date('receipt_date')->nullable()->after('internal_deadline')->comment('收单日期');
            }
            if (!Schema::hasColumn('cases', 'estimated_completion_date')) {
                $table->date('estimated_completion_date')->nullable()->after('receipt_date')->comment('预计完成日');
            }
            if (!Schema::hasColumn('cases', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('estimated_completion_date')->comment('前后付费');
            }
            if (!Schema::hasColumn('cases', 'estimated_final_payment')) {
                $table->decimal('estimated_final_payment', 15, 2)->nullable()->after('payment_method')->comment('预计尾款');
            }
            if (!Schema::hasColumn('cases', 'contract_requirements')) {
                $table->text('contract_requirements')->nullable()->after('estimated_final_payment')->comment('合同要求');
            }
            if (!Schema::hasColumn('cases', 'project_requirements')) {
                $table->text('project_requirements')->nullable()->after('contract_requirements')->comment('项目要求');
            }
            if (!Schema::hasColumn('cases', 'special_progress')) {
                $table->text('special_progress')->nullable()->after('project_requirements')->comment('特别进展事项');
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
                'project_year',
                'apply_batch',
                'tech_service_level',
                'supervisory_location',
                'supervisory_department',
                'government_reward',
                'cost_ratio',
                'technical_contact',
                'is_urgent',
                'case_handler',
                'estimated_start_date',
                'internal_deadline',
                'receipt_date',
                'estimated_completion_date',
                'payment_method',
                'estimated_final_payment',
                'contract_requirements',
                'project_requirements',
                'special_progress'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
