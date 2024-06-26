<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->string('client_id');
            $table->integer('payment_status');
            $table->integer('order_status');
            $table->integer('is_customer_complete')->default(0);
            $table->string('bank_slip')->nullable();
            $table->integer('delivery_time_type');
            $table->integer('delivery_method');
            $table->integer('payment_type');
            $table->float('total_amount');
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
        Schema::dropIfExists('orders');
    }
}
