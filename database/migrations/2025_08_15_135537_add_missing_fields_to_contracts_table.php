<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToContractsTable extends Migration
{
    /**
     * Run the migrations.
     * 添加合同表缺失的字段以匹配前端页面需求
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 检查并添加缺失的字段
            if (!Schema::hasColumn('contracts', 'technical_department')) {
                $table->string('technical_department', 100)->nullable()->after('technical_director_id')->comment('技术主导部门');
            }
            if (!Schema::hasColumn('contracts', 'paper_status')) {
                $table->string('paper_status', 10)->default('否')->after('technical_department')->comment('纸件状态');
            }

            // 甲方信息
            if (!Schema::hasColumn('contracts', 'party_a_contact_id')) {
                $table->bigInteger('party_a_contact_id')->nullable()->after('paper_status')->comment('甲方联系人ID（关联customer_contacts表）');
            }
            if (!Schema::hasColumn('contracts', 'party_a_phone')) {
                $table->string('party_a_phone', 20)->nullable()->after('party_a_contact_id')->comment('甲方电话');
            }
            if (!Schema::hasColumn('contracts', 'party_a_email')) {
                $table->string('party_a_email', 100)->nullable()->after('party_a_phone')->comment('甲方邮箱');
            }
            if (!Schema::hasColumn('contracts', 'party_a_address')) {
                $table->text('party_a_address')->nullable()->after('party_a_email')->comment('甲方签约地址');
            }

            // 乙方信息
            if (!Schema::hasColumn('contracts', 'party_b_signer')) {
                $table->string('party_b_signer', 50)->nullable()->after('party_a_address')->comment('乙方签约人');
            }
            if (!Schema::hasColumn('contracts', 'party_b_phone')) {
                $table->string('party_b_phone', 20)->nullable()->after('party_b_signer')->comment('乙方手机');
            }
            if (!Schema::hasColumn('contracts', 'party_b_company')) {
                $table->string('party_b_company', 100)->nullable()->after('party_b_phone')->comment('乙方签约公司');
            }
            if (!Schema::hasColumn('contracts', 'party_b_address')) {
                $table->text('party_b_address')->nullable()->after('party_b_company')->comment('乙方签约地址');
            }

            // 金额信息
            if (!Schema::hasColumn('contracts', 'service_fee')) {
                $table->decimal('service_fee', 15, 2)->default(0.00)->after('party_b_address')->comment('服务费');
            }
            if (!Schema::hasColumn('contracts', 'official_fee')) {
                $table->decimal('official_fee', 15, 2)->default(0.00)->after('service_fee')->comment('官费');
            }
            if (!Schema::hasColumn('contracts', 'channel_fee')) {
                $table->decimal('channel_fee', 15, 2)->default(0.00)->after('official_fee')->comment('渠道费');
            }
            if (!Schema::hasColumn('contracts', 'total_service_fee')) {
                $table->decimal('total_service_fee', 15, 2)->default(0.00)->after('channel_fee')->comment('总服务费');
            }
            if (!Schema::hasColumn('contracts', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0.00)->after('total_service_fee')->comment('总金额');
            }

            // 项目数量
            if (!Schema::hasColumn('contracts', 'case_count')) {
                $table->integer('case_count')->default(0)->after('total_amount')->comment('项目数量');
            }

            // 商机相关
            if (!Schema::hasColumn('contracts', 'opportunity_no')) {
                $table->string('opportunity_no', 50)->nullable()->after('case_count')->comment('对应商机号');
            }
            if (!Schema::hasColumn('contracts', 'opportunity_name')) {
                $table->string('opportunity_name', 200)->nullable()->after('opportunity_no')->comment('对应商机名称');
            }

            // 日期信息
            if (!Schema::hasColumn('contracts', 'validity_start_date')) {
                $table->date('validity_start_date')->nullable()->after('signing_date')->comment('合同有效期开始日期');
            }
            if (!Schema::hasColumn('contracts', 'validity_end_date')) {
                $table->date('validity_end_date')->nullable()->after('validity_start_date')->comment('合同有效期结束日期');
            }

            // 其他信息
            if (!Schema::hasColumn('contracts', 'additional_terms')) {
                $table->text('additional_terms')->nullable()->after('opportunity_name')->comment('附加条款');
            }
            if (!Schema::hasColumn('contracts', 'remark')) {
                $table->text('remark')->nullable()->after('additional_terms')->comment('合同备注');
            }

            // 流程相关
            if (!Schema::hasColumn('contracts', 'last_process_time')) {
                $table->timestamp('last_process_time')->nullable()->after('remark')->comment('最后处理时间');
            }
            if (!Schema::hasColumn('contracts', 'process_remark')) {
                $table->text('process_remark')->nullable()->after('last_process_time')->comment('流程备注');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 删除添加的字段
            $fieldsToRemove = [
                'technical_department',
                'paper_status',
                'party_a_contact_id',
                'party_a_phone',
                'party_a_email',
                'party_a_address',
                'party_b_signer',
                'party_b_phone',
                'party_b_company',
                'party_b_address',
                'service_fee',
                'official_fee',
                'channel_fee',
                'total_service_fee',
                'total_amount',
                'case_count',
                'opportunity_no',
                'opportunity_name',
                'validity_start_date',
                'validity_end_date',
                'additional_terms',
                'remark',
                'last_process_time',
                'process_remark'
            ];

            foreach ($fieldsToRemove as $field) {
                if (Schema::hasColumn('contracts', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
}
