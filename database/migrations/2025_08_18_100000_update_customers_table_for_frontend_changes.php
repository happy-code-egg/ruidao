<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomersTableForFrontendChanges extends Migration
{
    /**
     * Run the migrations.
     * 更新客户表以适应前端修改
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 修改资质相关字段类型，从boolean改为string，支持"未选择"状态
            $table->string('high_tech_enterprise', 10)->nullable()->change()->comment('高新技术企业：空-未选择，1-是，0-否');
            $table->string('province_enterprise', 10)->nullable()->change()->comment('省级企业：空-未选择，1-是，0-否');
            $table->string('city_enterprise', 10)->nullable()->change()->comment('市级企业：空-未选择，1-是，0-否');
            $table->string('province_tech_center', 10)->nullable()->change()->comment('省级工程技术中心：空-未选择，1-是，0-否');
            $table->string('ip_standard', 10)->nullable()->change()->comment('知识产权贯标：空-未选择，1-是，0-否');
            $table->string('it_standard', 10)->nullable()->change()->comment('两化融合贯标：空-未选择，1-是，0-否');
            
            // 添加缺失的资质字段
            if (!Schema::hasColumn('customers', 'is_jinxin_verified')) {
                $table->string('is_jinxin_verified', 10)->nullable()->comment('金信认证：空-未选择，1-是，0-否');
            }
            if (!Schema::hasColumn('customers', 'jinxin_verify_date')) {
                $table->date('jinxin_verify_date')->nullable()->comment('金信认证日期');
            }
            if (!Schema::hasColumn('customers', 'is_science_verified')) {
                $table->string('is_science_verified', 10)->nullable()->comment('科技认证：空-未选择，1-是，0-否');
            }
            if (!Schema::hasColumn('customers', 'science_verify_date')) {
                $table->date('science_verify_date')->nullable()->comment('科技认证日期');
            }
            
            // 添加其他缺失的基本信息字段
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name', 200)->nullable()->comment('客户名称（前端显示用）');
            }
            if (!Schema::hasColumn('customers', 'name_en')) {
                $table->string('name_en', 200)->nullable()->comment('客户英文名称');
            }
            if (!Schema::hasColumn('customers', 'legal_representative')) {
                $table->string('legal_representative', 100)->nullable()->comment('法定代表人');
            }
            if (!Schema::hasColumn('customers', 'company_manager')) {
                $table->string('company_manager', 100)->nullable()->comment('公司负责人');
            }
            if (!Schema::hasColumn('customers', 'level')) {
                $table->integer('level')->nullable()->comment('客户等级ID');
            }
            if (!Schema::hasColumn('customers', 'employee_count')) {
                $table->string('employee_count', 50)->nullable()->comment('公司人数');
            }
            if (!Schema::hasColumn('customers', 'business_person')) {
                $table->integer('business_person')->nullable()->comment('业务人员ID');
            }
            if (!Schema::hasColumn('customers', 'price_index')) {
                $table->integer('price_index')->nullable()->comment('价格指数ID');
            }
            if (!Schema::hasColumn('customers', 'innovation_index')) {
                $table->integer('innovation_index')->nullable()->comment('创新指数ID');
            }
            
            // 添加地址相关字段
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country', 100)->nullable()->comment('国籍');
            }
            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable()->comment('详细地址');
            }
            
            // 添加工商信息字段
            if (!Schema::hasColumn('customers', 'company_type')) {
                $table->string('company_type', 100)->nullable()->comment('企业类型');
            }
            if (!Schema::hasColumn('customers', 'industry_classification')) {
                $table->string('industry_classification', 100)->nullable()->comment('行业分类');
            }
            if (!Schema::hasColumn('customers', 'founding_date')) {
                $table->date('founding_date')->nullable()->comment('成立时间');
            }
            if (!Schema::hasColumn('customers', 'registered_capital')) {
                $table->decimal('registered_capital', 15, 2)->nullable()->comment('注册资本');
            }
            if (!Schema::hasColumn('customers', 'paid_capital')) {
                $table->decimal('paid_capital', 15, 2)->nullable()->comment('实缴资本');
            }
            if (!Schema::hasColumn('customers', 'business_term')) {
                $table->string('business_term', 100)->nullable()->comment('营业期限');
            }
            if (!Schema::hasColumn('customers', 'registration_authority')) {
                $table->string('registration_authority', 100)->nullable()->comment('登记机关');
            }
            if (!Schema::hasColumn('customers', 'approval_date')) {
                $table->date('approval_date')->nullable()->comment('核准日期');
            }
            if (!Schema::hasColumn('customers', 'registration_status')) {
                $table->string('registration_status', 50)->nullable()->comment('登记状态');
            }
            
            // 添加资质认证日期字段
            if (!Schema::hasColumn('customers', 'high_tech_date')) {
                $table->date('high_tech_date')->nullable()->comment('高新技术企业认证日期');
            }
            if (!Schema::hasColumn('customers', 'province_enterprise_date')) {
                $table->date('province_enterprise_date')->nullable()->comment('省级企业认证日期');
            }
            if (!Schema::hasColumn('customers', 'city_enterprise_date')) {
                $table->date('city_enterprise_date')->nullable()->comment('市级企业认证日期');
            }
            if (!Schema::hasColumn('customers', 'province_tech_center_date')) {
                $table->date('province_tech_center_date')->nullable()->comment('省级工程技术中心认证日期');
            }
            if (!Schema::hasColumn('customers', 'ip_standard_date')) {
                $table->date('ip_standard_date')->nullable()->comment('知识产权贯标认证日期');
            }
            if (!Schema::hasColumn('customers', 'info_standard_date')) {
                $table->date('info_standard_date')->nullable()->comment('两化融合贯标认证日期');
            }
            
            // 添加备注字段
            if (!Schema::hasColumn('customers', 'remark')) {
                $table->text('remark')->nullable()->comment('备注信息');
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
        Schema::table('customers', function (Blueprint $table) {
            // 恢复资质字段为boolean类型
            $table->boolean('high_tech_enterprise')->default(false)->change();
            $table->boolean('province_enterprise')->default(false)->change();
            $table->boolean('city_enterprise')->default(false)->change();
            $table->boolean('province_tech_center')->default(false)->change();
            $table->boolean('ip_standard')->default(false)->change();
            $table->boolean('it_standard')->default(false)->change();
            
            // 删除新增的字段（如果存在的话）
            $columns_to_drop = [
                'is_jinxin_verified', 'jinxin_verify_date', 'is_science_verified', 'science_verify_date',
                'name', 'name_en', 'legal_representative', 'company_manager', 'level', 'employee_count',
                'business_person', 'country', 'address', 'company_type', 'industry_classification',
                'founding_date', 'registered_capital', 'paid_capital', 'business_term',
                'registration_authority', 'approval_date', 'registration_status',
                'high_tech_date', 'province_enterprise_date', 'city_enterprise_date',
                'province_tech_center_date', 'ip_standard_date', 'info_standard_date', 'remark'
            ];
            
            foreach ($columns_to_drop as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
