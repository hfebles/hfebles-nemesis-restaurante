<?php

namespace App\Models\Recetas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recetas extends Model
{
    use HasFactory;


    protected $primaryKey = 'id_receta';

    protected $fillable = [
        'nombre_receta',
        'redimiento_receta',
        'sub_receta',
        'id_warehouse',
    ];
}
