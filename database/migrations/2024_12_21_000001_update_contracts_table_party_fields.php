<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateContractsTablePartyFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 修改乙方签约人字段为ID类型
            $table->dropColumn('party_b_signer');
            $table->bigInteger('party_b_signer_id')->nullable()->after('party_a_address')->comment('乙方签约人ID（关联users表）');
            
            // 修改乙方签约公司字段为ID类型
            $table->dropColumn('party_b_company');
            $table->bigInteger('party_b_company_id')->nullable()->after('party_b_phone')->comment('乙方签约公司ID（关联our_companies表）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 恢复原来的字段
            $table->dropColumn('party_b_signer_id');
            $table->string('party_b_signer', 50)->nullable()->after('party_a_address')->comment('乙方签约人');
            
            $table->dropColumn('party_b_company_id');
            $table->string('party_b_company', 100)->nullable()->after('party_b_phone')->comment('乙方签约公司');
        });
    }
}
