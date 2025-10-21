<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommissionSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // 移除旧字段
            $table->dropColumn(['name', 'type_id', 'min_amount', 'max_amount', 'description']);
            
            // 添加新字段
            $table->string('handler_level', 50)->after('id')->comment('处理人等级');
            $table->string('case_type', 50)->after('handler_level')->comment('项目类型');
            $table->string('business_type', 50)->after('case_type')->comment('业务类型');
            $table->string('application_type', 50)->after('business_type')->comment('申请类型');
            $table->decimal('case_coefficient', 5, 2)->after('application_type')->comment('项目系数');
            $table->decimal('matter_coefficient', 5, 2)->after('case_coefficient')->comment('处理事项系数');
            $table->string('processing_matter', 100)->after('matter_coefficient')->comment('处理事项');
            $table->string('case_stage', 50)->after('processing_matter')->comment('项目阶段');
            $table->string('commission_type', 50)->after('case_stage')->comment('提成类型');
            $table->decimal('piece_ratio', 5, 2)->after('commission_type')->comment('按件比例');
            $table->integer('piece_points')->after('piece_ratio')->comment('按件点数');
            $table->string('country', 50)->after('piece_points')->comment('国家（地区）');
        });
    }

    public function down()
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // 恢复旧字段
            $table->string('name', 100)->comment('提成配置名称');
            $table->bigInteger('type_id')->comment('类型ID');
            $table->decimal('min_amount', 10, 2)->default(0)->comment('最小金额');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('最大金额');
            $table->text('description')->nullable()->comment('描述');
            
            // 移除新字段
            $table->dropColumn([
                'handler_level',
                'case_type',
                'business_type',
                'application_type',
                'case_coefficient',
                'matter_coefficient',
                'processing_matter',
                'case_stage',
                'commission_type',
                'piece_ratio',
                'piece_points',
                'country'
            ]);
        });
    }
}
