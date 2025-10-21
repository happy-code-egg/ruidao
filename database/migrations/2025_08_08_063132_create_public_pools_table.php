<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicPoolsTable extends Migration
{
    public function up()
    {
        Schema::create('public_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('名称');
            $table->string('code', 50)->unique()->comment('编码');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('pool_name', 100)->nullable()->comment('pool_name');
            $table->string('pool_type', 50)->nullable()->comment('pool_type');
            $table->integer('capacity')->default(0)->comment('capacity');
            $table->bigInteger('created_by')->nullable()->comment('创建人');
            $table->bigInteger('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
            
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_pools');
    }
}