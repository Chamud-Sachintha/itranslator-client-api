<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnePayGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('one_pay_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('order_id');
            $table->integer('reference');
            $table->float('amount');
            $table->integer('status');
            $table->integer('create_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('one_pay_gateways');
    }
}
