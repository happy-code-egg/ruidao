<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPatentFieldsToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加专利相关字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加专利相关字段
            if (!Schema::hasColumn('cases', 'initial_stage')) {
                $table->string('initial_stage', 50)->default('新申请')->after('case_direction')->comment('起始阶段');
            }
            if (!Schema::hasColumn('cases', 'annual_fee_stage')) {
                $table->string('annual_fee_stage', 50)->nullable()->after('initial_stage')->comment('办理年费阶段');
            }
            if (!Schema::hasColumn('cases', 'prosecution_review')) {
                $table->string('prosecution_review', 10)->default('否')->after('annual_fee_stage')->comment('延迟审查');
            }
            if (!Schema::hasColumn('cases', 'preliminary_case')) {
                $table->boolean('preliminary_case')->default(false)->after('prosecution_review')->comment('预审项目');
            }
            if (!Schema::hasColumn('cases', 'early_publication')) {
                $table->boolean('early_publication')->default(false)->after('preliminary_case')->comment('提前公布');
            }
            if (!Schema::hasColumn('cases', 'confidential_application')) {
                $table->boolean('confidential_application')->default(false)->after('early_publication')->comment('保密审查');
            }
            if (!Schema::hasColumn('cases', 'substantive_examination')) {
                $table->boolean('substantive_examination')->default(false)->after('confidential_application')->comment('同时提实审');
            }
            if (!Schema::hasColumn('cases', 'fast_track_case')) {
                $table->boolean('fast_track_case')->default(false)->after('substantive_examination')->comment('快维项目');
            }
            if (!Schema::hasColumn('cases', 'priority_examination')) {
                $table->boolean('priority_examination')->default(false)->after('fast_track_case')->comment('优先审查');
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
                'initial_stage',
                'annual_fee_stage',
                'prosecution_review',
                'preliminary_case',
                'early_publication',
                'confidential_application',
                'substantive_examination',
                'fast_track_case',
                'priority_examination'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
