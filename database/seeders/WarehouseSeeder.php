<?php

namespace Database\Seeders;

use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Warehouse::create([
            'name_warehouse' => strtoupper('materia prima'),
            'code_warehouse' => strtoupper('mp')
        ]);

        Warehouse::create([
            'name_warehouse' => strtoupper('sub-recetas'),
            'code_warehouse' => strtoupper('sr')
        ]);
        
        Warehouse::create([
            'name_warehouse' => strtoupper('producto terminado'),
            'code_warehouse' => strtoupper('pt')
        ]);

        

    }
}
