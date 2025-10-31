<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCooperativePersonnelAndRiskClauseToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加协单人员和风险条款字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加协单人员字段（JSON格式存储多个人员ID）
            if (!Schema::hasColumn('cases', 'cooperative_personnel')) {
                $table->json('cooperative_personnel')->nullable()->after('remarks')->comment('协单人员（JSON格式存储多个人员ID）');
            }
            
            // 添加是否有风险条款字段
            if (!Schema::hasColumn('cases', 'has_risk_clause')) {
                $table->tinyInteger('has_risk_clause')->default(0)->after('cooperative_personnel')->comment('是否有风险条款（0-否，1-是）');
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
            if (Schema::hasColumn('cases', 'cooperative_personnel')) {
                $table->dropColumn('cooperative_personnel');
            }
            if (Schema::hasColumn('cases', 'has_risk_clause')) {
                $table->dropColumn('has_risk_clause');
            }
        });
    }
}

