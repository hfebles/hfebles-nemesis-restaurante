<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecetasDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recetas_details', function (Blueprint $table) {
            $table->id('id_receta_details');
            $table->integer('id_receta');
            $table->json('details');
            $table->float('costo_total', 8, 2);
            $table->float('costo_unitario', 8, 2);
            $table->float('precio_venta', 8, 2);
            $table->float('precio_iva', 8, 2);
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
        Schema::dropIfExists('recetas_details');
    }
}
