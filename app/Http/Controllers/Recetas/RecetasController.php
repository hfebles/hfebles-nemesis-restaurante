<?php

namespace App\Http\Controllers\Recetas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Products\ProductController;
use App\Models\Conf\Warehouse\UnitProduct;
use App\Models\Products\Product;
use App\Models\Recetas\Recetas;
use App\Models\Recetas\RecetasDetails;
use Illuminate\Http\Request;

class RecetasController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:recetas-list|adm-list', ['only' => ['index']]);
        $this->middleware('permission:adm-create|recetas-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:adm-edit|recetas-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:adm-delete|recetas-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Recetas::whereEnabledReceta(1)
            ->orderBy('nombre_receta', 'ASC')->paginate(15);

        $conf = [
            'title-section' => 'Recetas',
            'group' => 'recetas',
            'create' => ['route' => 'recetas.create', 'name' => 'Nueva receta'],
            'url' => '/recetas/recetas'
        ];

        $table = [
            'c_table' => 'table table-bordered table-hover mb-0 text-uppercase',
            'c_thead' => 'bg-dark text-white',
            'ths' => ['#', 'Nombre de la receta'],
            'w_ts' => ['3', '',],
            'c_ths' =>
            [
                'text-center align-middle',
                'text-center align-middle',
            ],
            'td_number' => [false, false,],
            'tds' => ['nombre_receta',],
            'switch' => false,
            'edit' => false,
            'show' => true,
            'edit_modal' => false,
            'url' => '/recetas/recetas',
            'id' => 'id_receta',
            'data' => $data,
            'i' => (($request->input('page', 1) - 1) * 15),
        ];


        return view('recetas.receta.index', compact('table', 'conf'));
    }

    public function create()
    {

        $conf = [
            'title-section' => 'Crear nueva receta',
            'group' => 'recetas',
            'back' => ['url' => $_SERVER['HTTP_REFERER'],],
            'url' => '/recetas/recetas',
        ];

        $productos = Product::select('id_product', \DB::raw("CONCAT(name_product,' - ',unit_products.name_unit_product) AS name"))
            ->where('salable_product', '=', 0)
            ->join('unit_products', 'unit_products.id_unit_product', '=', 'products.id_unit_product')
            ->orderBy('name', 'ASC')
            ->pluck('name', 'id_product');
        return view('recetas.receta.create', compact('conf', 'productos'));
    }

    public function store(Request $request)
    {

        // return $request;

        $data = $request->except('_token');
        $dataDetails = $request->except('_token', 'nombre_receta', 'redimiento_receta', 'sub_receta');

        $receta = new Recetas();

        $receta->nombre_receta = strtoupper($data['nombre_receta']);
        $receta->redimiento_receta = $data['redimiento_receta'];
        if (isset($data['sub_receta'])) {
            $receta->sub_receta = $data['sub_receta'];
            $receta->id_warehouse = 2;
        }
        $receta->save();

        $detalleReceta = new RecetasDetails();
        $detalleReceta->id_receta = $receta->id_receta;




        //$data_producto = [];
        for ($i = 0; $i < count($data['id_product_details']); $i++) {

            $data_producto = Product::whereIdProduct($data['id_product_details'][$i])->get()[0];
            $crudo = round(($data['qtys'][$i] * 1) / (1 * (1 - $data_producto->merma)), 2);
            $data['merma'][$i] = $data_producto->merma;
            $data['crudo'][$i] = $crudo;
            $data['costo'][$i] = $data_producto->price_product;
            $data['total'][$i] = round($crudo * $data_producto->price_product, 2);
        }

        $detalleReceta->costo_total = $data['costo_total'] = round(array_sum($data['total']), 2);
        $detalleReceta->costo_unitario = $data['costo_unitario'] = $data['costo_total'] / $data['redimiento_receta'];
        $detalleReceta->precio_venta = $data['precio_venta'] = round(1 * $data['costo_total'] / env('OBJETIVO_COSTO'), 2);
        $detalleReceta->precio_iva = $data['precio_iva'] = round($data['costo_unitario'] * env('IVA'), 2);

        //return $detalleReceta;


        $removeFields = ['nombre_receta', 'sub_receta', 'redimiento_receta', 'costo_total', 'costo_unitario', 'precio_venta', 'precio_iva'];
        $newCustomer = array_diff_key($data, array_flip($removeFields));
        $result = array_merge($dataDetails, $newCustomer);

        $detalleReceta->details = json_encode($result);
        $detalleReceta->save();

        $arr['name_product'] = strtoupper($data['nombre_receta']);
        $arr['price_product'] = $data['costo_unitario'] = $data['costo_total'] / $data['redimiento_receta'];
        $arr['product_usd_product'] = 1;
        $arr['salable_product'] = 0;
        if (!isset($data['sub_receta'])) {
            $arr['id_warehouse'] = 3;
        } else {
            $arr['id_warehouse'] = 2;
            $arr['sub_receta'] = 1;
        }

        $arr['id_receta'] = $receta->id_receta;
        (new ProductController)->storeRecetaWithProduct($arr);

        // return $salvar;

        return redirect()->route('recetas.index')->with('success', 'Receta creado con éxito');
    }

    public function show($id)
    {
        $conf = [
            'title-section' => 'Recetas',
            'group' => 'recetas',
            'back' => ['url' => $_SERVER['HTTP_REFERER'],],
            'url' => '/recetas/recetas',
            'edit' => ['route' => 'recetas.edit', 'id' => $id,],
            'delete' => ['name' => 'Eliminar receta']
        ];

        $data = Recetas::select('recetas.*', 'recetas_details.*')
            ->join('recetas_details', 'recetas.id_receta', '=', 'recetas_details.id_receta')
            ->where('recetas.id_receta', $id)->get()[0];

        $details = json_decode($data['details'], true);

        $d = $data['details'];
        for ($i = 0; $i < count($details['id_product_details']); $i++) {
            $details['name_product'][$i] = Product::select('name_product')->whereIdProduct($details['id_product_details'][$i])->get()[0]->name_product;
            $details['und'][$i] = UnitProduct::select('short_unit_product')->whereIdUnitProduct(Product::select('id_unit_product')->whereIdProduct($details['id_product_details'][$i])->get()[0]->id_unit_product)->get()[0]->short_unit_product;
        }
        //   return $data;



        return view('recetas.receta.show', compact('conf', 'data', 'details', 'd'));
    }

    public function imprimir_lista_compras($id, $cant)
    {
        $data = Recetas::select('recetas.id_receta', 'nombre_receta', 'details')
            ->join('recetas_details', 'recetas.id_receta', '=', 'recetas_details.id_receta')
            ->where('recetas.id_receta', $id)->get()[0];
        //return $data;

        $details = json_decode($data['details'], true);
        $details_2 = [];
        $details2 = [];
        $detaill = [];
        
        for ($i = 0; $i < count($details['id_product_details']); $i++) {

            $details['name_product'][$i] = Product::select('name_product')
                ->whereIdProduct($details['id_product_details'][$i])
                ->get()[0]->name_product;

            $details['und'][$i] = UnitProduct::select('short_unit_product')
                ->whereIdUnitProduct(Product::select('id_unit_product')
                    ->whereIdProduct($details['id_product_details'][$i])
                    ->get()[0]->id_unit_product)
                ->get()[0]->short_unit_product;

            $details['qtys'][$i] = round($details['qtys'][$i] * $cant, 3);

            if (Product::select('sub_receta')->whereIdProduct($details['id_product_details'][$i])->get()[0]->sub_receta == 1) {
                $details2[$i] = Recetas::select('recetas.id_receta', 'nombre_receta', 'details')
                    ->join('recetas_details', 'recetas.id_receta', '=', 'recetas_details.id_receta')
                    ->where('id_product', $details['id_product_details'][$i])->get()[0];

                $details_2[$i] = json_decode($details2[$i]['details'], true);

                for ($j = 0; $j < count($details_2[$i]['id_product_details']); $j++) {
                    
                    $detaill['name_product'][$i][$j] = Product::select('name_product')
                        ->join('unit_products', 'unit_products.id_unit_product', '=', 'products.id_unit_product')
                        ->whereIdProduct($details_2[$i]['id_product_details'][$j])
                        ->get()[0]->name_product;

                        $detaill['und'][$i][$j] = UnitProduct::select('short_unit_product')
                        ->whereIdUnitProduct(Product::select('id_unit_product')
                            ->whereIdProduct($details_2[$i]['id_product_details'][$j])
                            ->get()[0]->id_unit_product)
                        ->get()[0]->short_unit_product;
        
                    $detaill['qtys'][$i][$j] = round($details_2[$i]['qtys'][$j] * $cant, 3);
                }
            }
        }



        // return $detaill;


        $removeFields = ['id_product_details', 'id_product_details', 'crudo', 'costo', 'total', 'precio_venta', 'merma'];
        $detalles = array_diff_key($details, array_flip($removeFields));     

        //return "...";

        $pdf = \PDF::loadView('recetas.reportes.lista-compras', compact('data', 'detalles', 'cant', 'details2', 'detaill'));
        return $pdf->stream('prueba.pdf');
    }



    public function listar(Request $request)
    {
        if (empty($request->ids)) {
            return response()->json([
                'data' => 'relaj'
            ]);
        } else {
            return response()->json([
                Product::select('id_product', 'name_product', 'name_unit_product')
                    ->join('unit_products', 'unit_products.id_unit_product', '=', 'products.id_unit_product')
                    ->where('salable_product', '=', 0)
                    ->whereNotIn('id_product', $request->ids)
                    ->orderBy('name_product', 'ASC')
                    ->get()
            ]);
        }
    }
}
