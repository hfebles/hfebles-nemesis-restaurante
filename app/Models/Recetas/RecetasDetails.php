<?php

namespace App\Models\Recetas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetasDetails extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_receta_details';

    protected $fillable = [
        'details',
        'id_receta',
        'costo_total',
        'costo_unitario',
        'precio_venta',
        'precio_iva',       
    ];
}
