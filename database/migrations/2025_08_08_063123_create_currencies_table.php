<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('名称');
            $table->string('code', 50)->unique()->comment('编码');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('currency_name', 100)->nullable()->comment('currency_name');
            $table->string('currency_code', 10)->nullable()->comment('currency_code');
            $table->string('symbol', 10)->nullable()->comment('symbol');
            $table->decimal('exchange_rate', 10, 4)->default(0)->comment('exchange_rate');
            $table->bigInteger('created_by')->nullable()->comment('创建人');
            $table->bigInteger('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
            
            $table->index(['status', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}