<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCopyrightFieldsToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加版权相关字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加版权相关字段
            if (!Schema::hasColumn('cases', 'acceptance_number')) {
                $table->string('acceptance_number', 100)->nullable()->after('priority_examination')->comment('受理号');
            }
            if (!Schema::hasColumn('cases', 'has_materials')) {
                $table->string('has_materials', 10)->default('否')->after('acceptance_number')->comment('有无材料');
            }
            if (!Schema::hasColumn('cases', 'acceptance_date')) {
                $table->date('acceptance_date')->nullable()->after('has_materials')->comment('受理日');
            }
            if (!Schema::hasColumn('cases', 'location')) {
                $table->string('location', 100)->nullable()->after('acceptance_date')->comment('归属地');
            }
            if (!Schema::hasColumn('cases', 'software_abbr')) {
                $table->string('software_abbr', 100)->nullable()->after('location')->comment('软件简称');
            }
            if (!Schema::hasColumn('cases', 'version_number')) {
                $table->string('version_number', 50)->nullable()->after('software_abbr')->comment('版本号');
            }
            if (!Schema::hasColumn('cases', 'development_complete_date')) {
                $table->date('development_complete_date')->nullable()->after('version_number')->comment('开发完成日期');
            }
            if (!Schema::hasColumn('cases', 'publish_status')) {
                $table->string('publish_status', 20)->default('未发表')->after('development_complete_date')->comment('发表状态');
            }
            if (!Schema::hasColumn('cases', 'source_code_amount')) {
                $table->string('source_code_amount', 100)->nullable()->after('publish_status')->comment('源程序量');
            }
            if (!Schema::hasColumn('cases', 'hardware_env')) {
                $table->string('hardware_env', 200)->nullable()->after('source_code_amount')->comment('硬件环境');
            }
            if (!Schema::hasColumn('cases', 'software_env')) {
                $table->string('software_env', 200)->nullable()->after('hardware_env')->comment('软件环境');
            }
            if (!Schema::hasColumn('cases', 'programming_language')) {
                $table->string('programming_language', 100)->nullable()->after('software_env')->comment('编程语言');
            }
            if (!Schema::hasColumn('cases', 'software_description')) {
                $table->string('software_description', 50)->default('原创')->after('programming_language')->comment('软件作品说明');
            }
            if (!Schema::hasColumn('cases', 'main_features')) {
                $table->text('main_features')->nullable()->after('software_description')->comment('主要功能和技术特点');
            }
            if (!Schema::hasColumn('cases', 'author')) {
                $table->string('author', 200)->nullable()->after('main_features')->comment('作者');
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
                'acceptance_number',
                'has_materials',
                'acceptance_date',
                'location',
                'software_abbr',
                'version_number',
                'development_complete_date',
                'publish_status',
                'source_code_amount',
                'hardware_env',
                'software_env',
                'programming_language',
                'software_description',
                'main_features',
                'author'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
