<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id('id_receta');
            $table->string('nombre_receta');
            $table->float('redimiento_receta', 8, 3);
            $table->integer('sub_receta')->nullable();
            $table->integer('id_product')->nullable();
            $table->integer('id_warehouse')->nullable();
            $table->boolean('enabled_receta')->default(1);
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
        Schema::dropIfExists('recetas');
    }
}
