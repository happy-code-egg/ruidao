<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加product_id字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加产品ID字段
            if (!Schema::hasColumn('cases', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('trademark_category')->comment('产品ID');
                
                // 添加外键约束（如果products表存在）
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
                }
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
            if (Schema::hasColumn('cases', 'product_id')) {
                // 先删除外键约束
                if (Schema::hasTable('products')) {
                    $table->dropForeign(['product_id']);
                }
                $table->dropColumn('product_id');
            }
        });
    }
}
