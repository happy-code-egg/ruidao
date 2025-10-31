<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToInvoiceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 添加 deleted_at 到 invoice_applications 表
        if (Schema::hasTable('invoice_applications') && !Schema::hasColumn('invoice_applications', 'deleted_at')) {
            Schema::table('invoice_applications', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 添加 deleted_at 到 invoice_application_history 表
        if (Schema::hasTable('invoice_application_history') && !Schema::hasColumn('invoice_application_history', 'deleted_at')) {
            Schema::table('invoice_application_history', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 添加 deleted_at 到 invoice_download_records 表
        if (Schema::hasTable('invoice_download_records') && !Schema::hasColumn('invoice_download_records', 'deleted_at')) {
            Schema::table('invoice_download_records', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('invoice_applications', 'deleted_at')) {
            Schema::table('invoice_applications', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('invoice_application_history', 'deleted_at')) {
            Schema::table('invoice_application_history', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('invoice_download_records', 'deleted_at')) {
            Schema::table('invoice_download_records', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}

