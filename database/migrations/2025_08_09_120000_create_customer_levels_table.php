<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCustomerLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('level_name', 100)->comment('客户等级名称');
            $table->string('level_code', 50)->comment('客户等级编码')->nullable();
            $table->text('description')->nullable()->comment('描述');
            $table->integer('is_valid')->default(1)->comment('是否有效');
            $table->bigInteger('created_by')->nullable()->comment('创建人');
            $table->bigInteger('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
            
            $table->index(['is_valid', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_levels');
    }
}
