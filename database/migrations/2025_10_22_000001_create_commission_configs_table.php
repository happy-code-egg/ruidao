<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_name')->comment('配置名称');
            $table->enum('config_type', ['business', 'agent', 'consultant'])->comment('配置类型');
            $table->string('level')->comment('等级');
            $table->decimal('base_rate', 5, 2)->comment('基础提成率(%)');
            $table->decimal('bonus_rate', 5, 2)->comment('奖励提成率(%)');
            $table->decimal('min_amount', 12, 2)->comment('最低金额');
            $table->decimal('max_amount', 12, 2)->comment('最高金额');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_configs');
    }
}

