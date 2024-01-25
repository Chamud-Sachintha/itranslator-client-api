<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCSServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_s_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_index');
            $table->string('invoice_no');
            $table->string('client');
            $table->string('json_value');
            $table->float('total_amount')->nullable()->default(0);
            $table->integer('payment_type')->nullable()->default(1);
            $table->string('bank_slip')->nullable();
            $table->integer('payment_status')->nullable()->default(0);
            $table->integer('order_status')->nullable()->default(0);
            $table->integer('is_customer_complete')->nullable()->default(0);
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
        Schema::dropIfExists('c_s_services');
    }
}
