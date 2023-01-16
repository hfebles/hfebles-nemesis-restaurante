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
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');
            $table->string('name_product');
            $table->float('price_product', 8, 2);
            $table->boolean('product_usd_product')->default(0);
            $table->float('qty_product', 8, 3)->nullable();
            $table->integer('salable_product')->default(0);
            $table->integer('sub_receta')->default(0);
            $table->integer('id_warehouse')->nullable();
            $table->integer('id_unit_product')->nullable();
            $table->float('merma', 8, 3)->nullable();
            $table->boolean('enabled_product')->default(1);
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
        Schema::dropIfExists('products');
    }
};

