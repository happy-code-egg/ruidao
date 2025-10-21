<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('commission_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('提成配置名称');
            $table->bigInteger('type_id')->comment('类型ID');
            $table->decimal('rate', 5, 2)->comment('比例');
            $table->decimal('min_amount', 10, 2)->default(0)->comment('最小金额');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('最大金额');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('commission_settings');
    }
}
