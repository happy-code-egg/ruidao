<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommissionSettingsForMultipleSelection extends Migration
{
    public function up()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // 修改字段类型以支持多选数据（使用TEXT类型存储逗号分隔的字符串）
            $table->text('business_type')->change()->comment('业务类型（多选，逗号分隔）');
            $table->text('application_type')->change()->comment('申请类型（多选，逗号分隔）');
            $table->text('case_coefficient')->change()->comment('项目系数（多选，逗号分隔）');
            $table->text('matter_coefficient')->change()->comment('处理事项系数（多选，逗号分隔）');
            $table->text('processing_matter')->change()->comment('处理事项（多选，逗号分隔）');
        });
    }

    public function down()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // 恢复原来的字段类型
            $table->string('business_type', 50)->change()->comment('业务类型');
            $table->string('application_type', 50)->change()->comment('申请类型');
            $table->decimal('case_coefficient', 5, 2)->change()->comment('项目系数');
            $table->decimal('matter_coefficient', 5, 2)->change()->comment('处理事项系数');
            $table->string('processing_matter', 100)->change()->comment('处理事项');
        });
    }
}
