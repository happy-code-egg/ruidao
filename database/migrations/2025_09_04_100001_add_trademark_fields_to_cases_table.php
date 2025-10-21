<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrademarkFieldsToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加商标相关字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加商标相关字段
            if (!Schema::hasColumn('cases', 'sound_trademark')) {
                $table->string('sound_trademark', 10)->default('否')->after('trademark_category')->comment('声音商标');
            }
            if (!Schema::hasColumn('cases', 'specified_color')) {
                $table->string('specified_color', 10)->default('否')->after('sound_trademark')->comment('指定颜色');
            }
            if (!Schema::hasColumn('cases', 'is_3d_mark')) {
                $table->string('is_3d_mark', 10)->default('否')->after('specified_color')->comment('是否三维标志');
            }
            if (!Schema::hasColumn('cases', 'color_format')) {
                $table->string('color_format', 20)->default('黑白')->after('is_3d_mark')->comment('颜色格式');
            }
            if (!Schema::hasColumn('cases', 'preliminary_announcement_date')) {
                $table->date('preliminary_announcement_date')->nullable()->after('color_format')->comment('初审公告日期');
            }
            if (!Schema::hasColumn('cases', 'preliminary_announcement_period')) {
                $table->string('preliminary_announcement_period', 50)->nullable()->after('preliminary_announcement_date')->comment('初审公告期号');
            }
            if (!Schema::hasColumn('cases', 'renewal_date')) {
                $table->date('renewal_date')->nullable()->after('preliminary_announcement_period')->comment('续展日期');
            }
            if (!Schema::hasColumn('cases', 'end_date')) {
                $table->date('end_date')->nullable()->after('renewal_date')->comment('结束日期');
            }
            if (!Schema::hasColumn('cases', 'customer_file_number')) {
                $table->string('customer_file_number', 100)->nullable()->after('end_date')->comment('客户档案号');
            }
            if (!Schema::hasColumn('cases', 'trademark_description')) {
                $table->text('trademark_description')->nullable()->after('customer_file_number')->comment('商标说明');
            }
            if (!Schema::hasColumn('cases', 'registration_no')) {
                $table->string('registration_no', 100)->nullable()->after('trademark_description')->comment('注册号');
            }
            if (!Schema::hasColumn('cases', 'publication_date')) {
                $table->date('publication_date')->nullable()->after('registration_no')->comment('公告日期');
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
                'sound_trademark',
                'specified_color', 
                'is_3d_mark',
                'color_format',
                'preliminary_announcement_date',
                'preliminary_announcement_period',
                'renewal_date',
                'end_date',
                'customer_file_number',
                'trademark_description',
                'registration_no',
                'publication_date'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
