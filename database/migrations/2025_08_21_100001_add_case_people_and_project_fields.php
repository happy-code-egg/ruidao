<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCasePeopleAndProjectFields extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'presale_support')) {
                $table->unsignedBigInteger('presale_support')->nullable()->after('country_code')->comment('售前支持联系人ID');
            }
            if (!Schema::hasColumn('cases', 'tech_leader')) {
                $table->unsignedBigInteger('tech_leader')->nullable()->after('presale_support')->comment('技术主导联系人ID');
            }
            if (!Schema::hasColumn('cases', 'tech_contact')) {
                $table->unsignedBigInteger('tech_contact')->nullable()->after('tech_leader')->comment('技术联系人ID');
            }
            if (!Schema::hasColumn('cases', 'is_authorized')) {
                $table->tinyInteger('is_authorized')->nullable()->after('tech_contact')->comment('是否已有项目 1是 0否');
            }
            if (!Schema::hasColumn('cases', 'project_no')) {
                $table->string('project_no', 100)->nullable()->after('is_authorized')->comment('项目编号');
            }
            if (!Schema::hasColumn('cases', 'tech_service_name')) {
                $table->string('tech_service_name', 200)->nullable()->after('project_no')->comment('科技服务名称');
            }
            if (!Schema::hasColumn('cases', 'trademark_category')) {
                $table->string('trademark_category', 50)->nullable()->after('tech_service_name')->comment('商标类别');
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
            if (Schema::hasColumn('cases', 'trademark_category')) {
                $table->dropColumn('trademark_category');
            }
            if (Schema::hasColumn('cases', 'tech_service_name')) {
                $table->dropColumn('tech_service_name');
            }
            if (Schema::hasColumn('cases', 'project_no')) {
                $table->dropColumn('project_no');
            }
            if (Schema::hasColumn('cases', 'is_authorized')) {
                $table->dropColumn('is_authorized');
            }
            if (Schema::hasColumn('cases', 'tech_contact')) {
                $table->dropColumn('tech_contact');
            }
            if (Schema::hasColumn('cases', 'tech_leader')) {
                $table->dropColumn('tech_leader');
            }
            if (Schema::hasColumn('cases', 'presale_support')) {
                $table->dropColumn('presale_support');
            }
        });
    }
}


