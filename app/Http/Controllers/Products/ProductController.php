<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Products\Product;
use App\Models\Warehouse\Warehouse;
use App\Models\Warehouse\ProductHistory;

use App\Models\Conf\Warehouse\PresentationProduct;
use App\Models\Conf\Warehouse\UnitProduct;
use App\Models\Conf\Warehouse\ProductCategory;
use App\Models\Recetas\Recetas;

class ProductController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:product-product-list|adm-list', ['only' => ['index']]);
        $this->middleware('permission:adm-create|product-product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:adm-edit|product-product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:adm-delete|product-product-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {

        if ($request->param == 1) {
            $data = Product::select('products.*', 'warehouses.name_warehouse')->whereSalableProduct(1)->whereEnabledProduct(1)->join('warehouses', 'warehouses.id_warehouse', '=', 'products.id_warehouse')->orderBy('name_product', 'DESC')->paginate(15);
        } else {
            $data = Product::select('products.*', 'warehouses.name_warehouse')->whereEnabledProduct(1)->join('warehouses', 'warehouses.id_warehouse', '=', 'products.id_warehouse', 'left')->orderBy('name_product', 'ASC')->paginate(15);
        }

        // return Product::select('products.*', 'warehouses.name_warehouse')->where('enabled_product', '=', '1')->join('warehouses', 'warehouses.id_warehouse', '=', 'products.id_warehouse')->paginate(15);

        $conf = [
            'title-section' => 'Productos',
            'group' => 'product-product',
            'create' => ['route' => 'product.create', 'name' => 'Nuevo producto'],
            'url' => '/products/product'
        ];

        $table = [
            'c_table' => 'table table-bordered table-hover mb-0 text-uppercase',
            'c_thead' => 'bg-dark text-white',
            'ths' => ['#', 'Almacen', 'Producto', 'Disponible'],
            'w_ts' => ['3', '15', '68', '7',],
            'c_ths' =>
            [
                'text-center align-middle',
                'text-center align-middle',
                'align-middle',
                'text-center align-middle',
            ],
            'td_number' => [false, false, true,],
            'tds' => ['name_warehouse', 'name_product', 'qty_product'],
            'switch' => false,
            'edit' => false,
            'show' => true,
            'edit_modal' => false,
            'url' => "/products/product",
            'id' => 'id_product',
            'data' => $data,
            'i' => (($request->input('page', 1) - 1) * 15),
        ];


        return view('products.product.index', compact('table', 'conf'));
    }



    public function create()
    {

        $conf = [
            'title-section' => 'Cargar un nuevo producto',
            'group' => 'product-product',
            'back' => ['url' => $_SERVER['HTTP_REFERER'],],
            'url' => '/products/product'
        ];



        $getCategories = ProductCategory::whereEnabledProductCategory(1)->pluck('name_product_category', 'id_product_category');
        $getUnits = UnitProduct::select(
            DB::raw("CONCAT(short_unit_product,' - ',name_unit_product) AS name_unit_product"),
            'id_unit_product'
        )
            ->whereEnabledUnitProduct(1)
            ->pluck('name_unit_product', 'id_unit_product');

        $getPresentations = PresentationProduct::whereEnabledPresentationProduct(1)->pluck('name_presentation_product', 'id_presentation_product');
        $getWarehouses = Warehouse::select(
            DB::raw("CONCAT(code_warehouse,' - ',name_warehouse) AS name_warehouse"),
            'id_warehouse'
        )
            ->whereEnabledWarehouse(1)
            ->pluck('name_warehouse', 'id_warehouse');

        return view('products.product.create', compact('conf', 'getCategories', 'getUnits', 'getPresentations', 'getWarehouses'));
    }


    public function store(Request $request)
    {

        $data = $request->except('_token');
        $save = new Product();

        $save->name_product = strtoupper($data["name_product"]);
        $save->price_product = $data["price_product"];

        if (isset($data["salable_product"])) {
            $save->salable_product = $data["salable_product"];
        }

        if (isset($data["product_usd_product"])) {
            $save->product_usd_product = $data["product_usd_product"];
        }

        $save->id_warehouse = $data["id_warehouse"];

        $save->id_unit_product = $data["id_unit_product"];

        $save->save();


        return redirect()->route('product.index')->with('success', 'Producto creado con exito');
    }

    public function show(Request $request, $id)
    {

        $data = Product::select('products.*', 'w.name_warehouse', 'w.code_warehouse', 'u.name_unit_product', 'u.short_unit_product',)
            ->join('warehouses as w', 'w.id_warehouse', '=', 'products.id_warehouse', 'left')
            ->join('unit_products as u', 'u.id_unit_product', '=', 'products.id_unit_product', 'left outer')
            ->whereIdProduct($id)->get()[0];

        $tableData = ProductHistory::select('p.code_product', 'p.name_product', 'product_histories.date_product_history', 'product_histories.price_product_history', 'product_histories.qty_product_history')
            ->join('products as p', 'p.id_product', '=', 'product_histories.id_product')
            ->where('product_histories.id_product', '=', $id)->paginate(5);

        //return $data;

        $table = [
            'c_table' => 'table table-bordered table-hover mb-0 text-uppercase',
            'c_thead' => 'bg-dark text-white',
            'ths' => ['#', 'Fecha', 'Código', 'Producto', 'Cantidad', 'Precio'],
            'w_ts' => ['3', '10', '7', '60', '7', '7',],
            'c_ths' =>
            [
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
                'align-middle',
                'text-center align-middle',
                'text-center align-middle',
            ],
            'tds' => ['date_product_history', 'code_product', 'name_product', 'qty_product_history', 'price_product_history',],
            'switch' => false,
            'edit' => false,
            'show' => false,
            'edit_modal' => false,
            'url' => "/products/product",
            'id' => 'id_product_history',
            'data' => $tableData,
            'i' => (($request->input('page', 1) - 1) * 5),
            'caption' => 'Histórico de cambios',
        ];

        $conf = [
            'title-section' => 'Producto: ' . $data->name_product,
            'group' => 'product-product',
            'back' => (url()->previous() == 'http://localhost:8000/products/product?param=1') ? 'product.salable' : 'product.index',
            'edit' => ['route' => 'product.edit', 'id' => $id,],
            'url' => '/products/product/salable',
            'delete' => ['name' => 'Eliminar producto']
        ];




        return view('products.product.show', compact('conf', 'data', 'table'));
    }

    public function edit($id)
    {

        $data = Product::select('products.*', 'w.name_warehouse', 'u.name_unit_product', 'u.short_unit_product')
            ->join('warehouses as w', 'w.id_warehouse', '=', 'products.id_warehouse', 'left')
            ->join('unit_products as u', 'u.id_unit_product', '=', 'products.id_unit_product', 'left outer')
            ->whereIdProduct($id)->get()[0];

        $conf = [
            'title-section' => 'Producto:' . $data->name_product,
            'group' => 'product-product',
            'back' => ['route' => "./", 'show' => true],
            'url' => '/products/product/salable',

        ];

        $getCategories = ProductCategory::whereEnabledProductCategory(1)->pluck('name_product_category', 'id_product_category');
        $getUnits = UnitProduct::select(
            DB::raw("CONCAT(short_unit_product,' - ',name_unit_product) AS name_unit_product"),
            'id_unit_product'
        )
            ->whereEnabledUnitProduct(1)
            ->pluck('name_unit_product', 'id_unit_product');

        $getPresentations = PresentationProduct::whereEnabledPresentationProduct(1)->pluck('name_presentation_product', 'id_presentation_product');
        $getWarehouses = Warehouse::select(
            DB::raw("CONCAT(code_warehouse,' - ',name_warehouse) AS name_warehouse"),
            'id_warehouse'
        )
            ->whereEnabledWarehouse(1)
            ->pluck('name_warehouse', 'id_warehouse');




        return view('products.product.edit', compact('conf', 'data', 'getCategories', 'getUnits', 'getPresentations', 'getWarehouses'));
    }

    public function update(Request $request, $id)
    {

        $data = $request->except('_token', '_method');
        $dataSave = Product::select('price_product', 'qty_product')->whereIdProduct($id)->get()[0];

        //return $dataSave;



        $data['name_product'] = strtoupper($data['name_product']);


        $data['price_product'] = $data['price_product'];
        $data['qty_product'] = $data['qty_product'];
        if (isset($data['salable_product']) && $data['salable_product'] != null) {
            $data['salable_product'] = $data['salable_product'];
        } else {
            $data['salable_product'] = 0;
        }
        if (isset($data['product_usd_product']) && $data['product_usd_product'] != null) {
            $data['product_usd_product'] = $data['product_usd_product'];
        } else {
            $data['product_usd_product'] = 0;
        }
        $data['id_warehouse'] = $data['id_warehouse'];
        $data['id_unit_product'] = $data['id_unit_product'];
        Product::whereIdProduct($id)->update($data);


        $message = [
            'type' => 'success',
            'message' => 'Se edito con éxito',
        ];

        return redirect()->route('product.show', $id)->with('success', 'Se edito con éxito');
    }

    function destroy($id)
    {
        Product::whereIdProduct($id)->update([
            'enabled_product' => 0,
        ]);

        return redirect()->route('product.index')->with('success', 'Producto eliminado con exito');
    }

    /*========================================*/

    function searchCode(Request $request)
    {
        $data = Product::where('code_product', '=', $request->text)->get();
        if (count($data) > 0) {
            return response()->json(['res' => false, 'msg' => 'El código ya fue registrado']);
        } else {
            return response()->json(['res' => true, 'msg' => 'El código es valido']);
        }
        return $data;
    }

    function search(Request $request)
    {
        $data = DB::select('SELECT id_product, name_product, code_product, price_product, qty_product, salable_product, product_usd_product, tax_exempt_product, id_unit_product, id_presentation_product 
                            FROM products 
                            WHERE name_product LIKE "%' . $request->text . '%" 
                            OR code_product LIKE "%' . $request->text . '%"
                            AND enabled_product = 1');

        return response()->json(['lista' => $data]);
    }

    public function indexSalable()
    {
        return redirect()->route('product.index', ['param' => 1]);
    }

    public function storeRecetaWithProduct($data){

        $save = new Product();
        $save->name_product = strtoupper($data["name_product"]);
        $save->price_product = $data["price_product"];
        $save->salable_product = $data["salable_product"];
        $save->product_usd_product = $data["product_usd_product"];
        $save->id_warehouse = $data["id_warehouse"];
        $save->id_unit_product = 1;
        $save->qty_product = 1;
        if(isset($data["sub_receta"])){
            $save->sub_receta = $data["sub_receta"];
        }
        $save->save();

        Recetas::whereIdReceta($data['id_receta'])->update(['id_product' => $save->id_product]);

        return response()->json(['status' => 1]);
    }
}
