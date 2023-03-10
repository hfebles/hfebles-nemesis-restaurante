<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_order_configurations', function (Blueprint $table) {
            $table->id('id_sale_order_configuration');
            $table->string('print_name_sale_order_configuration')->nullable();
            $table->string('correlative_sale_order_configuration')->nullable();
            $table->integer('control_number_sale_order_configuration')->nullable();
            $table->integer('type_Ledger')->nullable();
            $table->boolean('enabled_sale_order_configuration')->default(1);
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
        Schema::dropIfExists('sale_order_configurations');
    }
};
