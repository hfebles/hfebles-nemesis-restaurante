<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetails;
use App\Models\Products\Product;
use App\Models\Sales\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\HumanResources\Workers;
use App\Models\Conf\Exchange;
use App\Models\Conf\Sales\SaleOrderConfiguration;
use App\Models\Conf\Tax;
use App\Models\Payments\Payments;
use App\Models\Sales\DeliveryNotes;
use App\Models\Sales\DeliveryNotesDetails;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:sales-order-list|adm-list', ['only' => ['index']]);
        $this->middleware('permission:adm-create|sales-order-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:adm-edit|sales-order-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:adm-delete|sales-order-delete', ['only' => ['destroy']]);
    }

    function getNroControl($dataConfiguration)
    {
        $facturas = SalesOrder::orderBy('ctrl_num', 'ASC')->get();
        $nro2 = [];

        for ($i = 0; $i < sizeof($facturas); $i++) {
            $nro2[$i] = $facturas[$i]->ctrl_num;
        }

        if (sizeof($facturas)) {
            $existe = SalesOrder::select('ctrl_num')->whereCtrlNum($dataConfiguration->control_number_sale_order_configuration)->get();
            if (sizeof($existe) > 0) {
                if (sizeof(SalesOrder::select('ctrl_num')->whereCtrlNum($dataConfiguration->control_number_sale_order_configuration + 1)->get())) {
                    $compare_array = range(1, max($nro2));
                    $missing_values = array_diff($compare_array, $nro2);
                    if (range(1, max($nro2)) >= $dataConfiguration->control_number_sale_order_configuration && min($missing_values) < $dataConfiguration->control_number_sale_order_configuration) {
                        $ctrl = $nro2[sizeof($nro2) - 1] + 1;
                    } else if (sizeof(SalesOrder::select('ctrl_num')->whereCtrlNum($missing_values[key($missing_values)])->get()) == 0) {
                        $ctrl = $missing_values[key($missing_values)];
                    }
                } else {
                    $ctrl = $dataConfiguration->control_number_sale_order_configuration + 1;
                }
            } else {
                $ctrl = $dataConfiguration->control_number_sale_order_configuration;
            }
        } else {
            $ctrl = $dataConfiguration->control_number_sale_order_configuration;
        }

        return $ctrl;
    }

    public function index(Request $request)
    {
        $conf = [
            'title-section' => 'Pedidos de venta',
            'group' => 'sales-order',
            'create' => ['route' => 'sales-order.create', 'name' => 'Nuevo pedido'],
        ];
        $data = SalesOrder::select('id_sales_order', 'date_sales_order', 'ref_name_sales_order', 'total_amount_sales_order', 'os.name_order_state', 'c.name_client')
            ->join('clients as c', 'c.id_client', '=', 'sales_orders.id_client', 'left outer')
            ->join('order_states as os', 'os.id_order_state', '=', 'sales_orders.id_order_state', 'left outer')
            ->whereEnabledSalesOrder(1)
            ->orderBy('date_sales_order', 'DESC')
            ->orderBy('sales_orders.id_order_state', 'ASC')
            ->orderBy('id_sales_order', 'DESC')
            ->paginate(15);
        $table = [
            'c_table' => 'table table-bordered table-hover mb-0 text-uppercase',
            'c_thead' => 'bg-dark text-white',
            'ths' => ['#', 'Fecha', 'Pedido', 'Cliente', 'Estado', 'Total'],
            'w_ts' => ['3', '10', '10', '43', '12', '12',],
            'c_ths' =>
            [
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
                'text-center align-middle',
            ],
            'tds' => ['date_sales_order', 'ref_name_sales_order', 'name_client', 'name_order_state', 'total_amount_sales_order'],
            'edit' => false,
            'show' => true,
            'edit_modal' => false,
            'td_number' => [false, false, false, false, true,],
            'url' => "/sales/sales-order",
            'id' => 'id_sales_order',
            'data' => $data,
            'i' => (($request->input('page', 1) - 1) * 15),
        ];
        return view('sales.sales-order.index', compact('conf', 'table'));
    }

    public function create()
    {
        $conf = [
            'title-section' => 'Nuevo pedido',
            'group' => 'sales-order',
            'back' => 'sales-order.index',
        ];

        $dataExchange = Exchange::whereEnabledExchange(1)->where('date_exchange', '=', date('Y-m-d'))->orderBy('id_exchange', 'DESC')->get();
        $dataConfiguration = SaleOrderConfiguration::all();
        $datax = SalesOrder::whereEnabledSalesOrder(1)->orderBy('id_sales_order', 'DESC')->get();
        $taxes = Tax::where('billable_tax', '=', 1)->get();

        if (count(Client::whereEnabledClient(1)->get()) == 0) {
            return redirect()->route('clients.index')->with('error', 'Debe registrar un cliente');
        }
        if (count($dataExchange) == 0) {
            return redirect()->route('exchange.index')->with('error', 'Debe registrar un tasa de cambio');
        } else {
            $dataExchange = $dataExchange[0];
        }

        if (count($dataConfiguration) == 0) {
            return redirect()->route('sales-order.index')->with('error', 'No tiene configurado los pedidos');
        } else {
            $dataConfiguration = $dataConfiguration[0];
        }
        
        $ctrl = $this->getNroControl($dataConfiguration);

        //return $ctrl;


        $dataWorkers =  Workers::select('workers.id_worker', DB::raw('CONCAT(firts_name_worker," ",last_name_worker) as name'), 'group_workers.name_group_worker')
            ->join('group_workers', 'group_workers.id_group_worker', '=', 'workers.id_group_worker')
            ->where('name_group_worker', '=', 'VENDEDORES')
            ->pluck('name', 'workers.id_worker');

        return view('sales.sales-order.create', compact('conf', 'ctrl', 'dataWorkers', 'dataExchange', 'dataConfiguration', 'taxes'));
    }


    public function store(Request $request)

    

    {
        return $request;
        $dataSalesOrder = $request->except('_token');
        $dataDetails = $request->except(
            '_token',
            'id_client',
            'subFac',
            'exento',
            'total_taxes',
            'total_con_tax',
            'noExento',
            'subtotal',
            'exempt_product',
            'subtotal_exento',
            'id_worker',
            'id_exchange',
            'ref_name_purchase_order',
            'ctrl_num'
        );
        $saveSalesOrder = new SalesOrder();
        $saveSalesOrder->ref_name_purchase_order = $dataSalesOrder['ref_name_purchase_order'];
        $saveSalesOrder->ctrl_num = $dataSalesOrder['ctrl_num'];
        $saveSalesOrder->ctrl_num_sales_order = $dataSalesOrder['ctrl_num_sales_order'];
        $saveSalesOrder->id_client = $dataSalesOrder['id_client'];
        $saveSalesOrder->id_exchange = $dataSalesOrder['id_exchange'];
        
        
        if (isset($dataSalesOrder['id_worker'])) {
            $saveSalesOrder->id_worker = $dataSalesOrder['id_worker'];
        }
        $saveSalesOrder->id_user = Auth::id();
        $saveSalesOrder->total_amount_sales_order = $dataSalesOrder['total_con_tax'];
        $saveSalesOrder->exempt_amout_sales_order = $dataSalesOrder['exento'];
        $saveSalesOrder->no_exempt_amout_sales_order = $dataSalesOrder['subFac'];
        $saveSalesOrder->total_amount_tax_sales_order = $dataSalesOrder['total_taxes'];
        $saveSalesOrder->date_sales_order = $dataSalesOrder['date_sales_order'];
        $saveSalesOrder->save();
        $saveDetails = new salesOrderDetails();
        $saveDetails->id_sales_order = $saveSalesOrder->id_sales_order;
        $saveDetails->ref_name_purchase_order = $saveSalesOrder->ref_name_purchase_order;
        $saveDetails->ctrl_num_purchase_order = $saveSalesOrder->ctrl_num;
        $saveDetails->details_order_detail = json_encode($dataDetails);
        $saveDetails->save();

        for ($i = 0; $i < count($dataSalesOrder['id_product']); $i++) {
            $restar =  Product::select('qty_product')->whereIdProduct($dataSalesOrder['id_product'][$i])->get();
            $operacion = $restar[0]->qty_product - $dataSalesOrder['cantidad'][$i];
            Product::whereIdProduct($dataSalesOrder['id_product'][$i])->update(['qty_product' => $operacion]);
        }
        return redirect()->route('sales-order.show', $saveSalesOrder->id_sales_order)->with('message', 'Se registro el pedido con ??xito');
    }

    public function show($id)
    {
        $data = SalesOrder::select('sales_orders.*', 'c.address_client', 'c.phone_client', 'c.idcard_client', 'c.name_client', 'w.firts_name_worker', 'w.last_name_worker', 'e.amount_exchange', 'e.date_exchange')
            ->join('clients AS c', 'c.id_client', '=', 'sales_orders.id_client')
            ->join('exchanges AS e', 'e.id_exchange', '=', 'sales_orders.id_exchange')
            ->join('workers AS w', 'w.id_worker', '=', 'sales_orders.id_worker', 'left outer')
            ->find($id);
        $conf = [
            'title-section' => 'Pedido: ',
            'group' => 'sales-order',
            'back' => 'sales-order.index',
            'edit' => ['route' => 'sales-order.edit', 'id' => $id],
        ];
        $dataSalesOrderDetails = salesOrderDetails::whereIdSalesOrder($id)->get()[0];
        $obj = json_decode($dataSalesOrderDetails->details_order_detail, true);
        for ($i = 0; $i < count($obj['id_product']); $i++) {
            $dataProducts[$i] =  DB::select("SELECT products.*, p.name_presentation_product, u.name_unit_product, u.short_unit_product
                                                FROM products 
                                                INNER JOIN presentation_products AS p ON p.id_presentation_product = products.id_presentation_product
                                                INNER JOIN unit_products AS u ON u.id_unit_product = products.id_unit_product
                                                WHERE products.id_product =" . $obj['id_product'][$i]);
        }
        return view('sales.sales-order.show', compact('conf', 'data', 'dataProducts', 'obj'));
    }

    public function edit($id)
    {

        $data = SalesOrder::select('sales_orders.*', 'c.address_client', 'c.phone_client', 'c.idcard_client', 'c.name_client', 'w.firts_name_worker', 'w.last_name_worker', 'e.amount_exchange', 'e.date_exchange')
            ->join('clients AS c', 'c.id_client', '=', 'sales_orders.id_client')
            ->join('exchanges AS e', 'e.id_exchange', '=', 'sales_orders.id_exchange')
            ->join('workers AS w', 'w.id_worker', '=', 'sales_orders.id_worker', 'left outer')
            ->find($id);


        if ($data->id_order_state == 2) {
            return redirect()->route('sales-order.show', $data->id_sales_order)->with('error', 'No puede editar la orden si ya fue facturada.');
        } else if ($data->id_order_state == 3) {
            return redirect()->route('sales-order.show', $data->id_sales_order)->with('message', 'No puede editar la orden si ya fue cancelada.');
        } else {
            $conf = [
                'title-section' => 'Pedido: ',
                'group' => 'sales-order',
                'back' => 'sales-order.index',
                'edit' => ['route' => 'sales-order.edit', 'id' => $id],
            ];
            $taxes = Tax::where('billable_tax', '=', 1)->get();
            $dataExchange = Exchange::whereEnabledExchange(1)->where('date_exchange', '=', date('Y-m-d'))->orderBy('id_exchange', 'DESC')->get()[0];
            $dataSalesOrderDetails = salesOrderDetails::whereIdSalesOrder($id)->get()[0];
            $obj = json_decode($dataSalesOrderDetails->details_order_detail, true);
            for ($i = 0; $i < count($obj['id_product']); $i++) {
                $dataProducts[$i] =  DB::select("SELECT products.*, p.name_presentation_product, u.name_unit_product, u.short_unit_product
                            FROM products 
                            INNER JOIN presentation_products AS p ON p.id_presentation_product = products.id_presentation_product
                            INNER JOIN unit_products AS u ON u.id_unit_product = products.id_unit_product
                            WHERE products.id_product =" . $obj['id_product'][$i]);
            }
            return view('sales.sales-order.edit', compact('conf', 'data', 'dataProducts', 'obj', 'taxes', 'dataExchange'));
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('_token', '_method');
        $saleOrderData = SalesOrder::whereIdSalesOrder($id)->get()[0];
        $dataSalesOrderDetails = salesOrderDetails::whereIdSalesOrder($id)->get()[0];
        $obj = json_decode($dataSalesOrderDetails->details_order_detail, true);
        for ($i = 0; $i < count($obj['id_product']); $i++) {
            $sumar =  Product::select('qty_product')->whereIdProduct($obj['id_product'][$i])->get()[0];
            $operacion = $sumar->qty_product + $obj['cantidad'][$i];
            Product::whereIdProduct($obj['id_product'][$i])->update(['qty_product' => $operacion]);
        }
        $dataDetails = $request->except('_token', 'id_client', 'type_payment_sales_order', 'subFac', 'exento', 'total_taxes', 'total_con_tax', 'noExento', 'subtotal', 'exempt_product', 'subtotal_exento', 'id_worker', 'id_exchange', 'ref_name_sales_order', 'ctrl_num');
        salesOrder::whereIdSalesOrder($id)->update([
            'type_payment' => $data['type_payment_sales_order'],
            'id_client' => $data['id_client'],
            'id_exchange' => $data['id_exchange'],
            'id_user' => Auth::id(),
            'total_amount_sales_order' => $data['total_con_tax'],
            'exempt_amout_sales_order' => $data['exento'],
            'no_exempt_amout_sales_order' => $data['subFac'],
            'total_amount_tax_sales_order' => $data['total_taxes'],
        ]);

        salesOrderDetails::whereIdSalesOrder($id)->update(['details_order_detail' => json_encode($dataDetails)]);
        for ($i = 0; $i < count($data['id_product']); $i++) {
            $restar =  Product::select('qty_product')->whereIdProduct($data['id_product'][$i])->get();
            $operacion = $restar[0]->qty_product - $data['cantidad'][$i];
            Product::whereIdProduct($data['id_product'][$i])->update(['qty_product' => $operacion]);
        }


        $deliveryNote = DeliveryNotes::where('ref_name_delivery_note', '=', $saleOrderData->ref_name_sales_order)->get();

        // return $deliveryNote;
        if (count($deliveryNote) > 0) {

            $total = $data['subFac'] + $data['exento'];

            // return $total;
            $payments = Payments::where('id_delivery_note', '=', $deliveryNote[0]->id_delivery_note)->get();
            if (count($payments) > 0) {
                $pagos = 0;
                foreach ($payments as $pay) {
                    $pagos = $pagos + $pay->amount_payment;
                }
                $state = ($pagos > $total) ? 7 : 6;
                DeliveryNotes::where('id_delivery_note', '=', $deliveryNote[0]->id_delivery_note)
                    ->update(
                        [
                            'type_payment' => $saleOrderData->type_payment,
                            'id_client' => $saleOrderData->id_client,
                            'id_exchange' => $saleOrderData->id_exchange,
                            'ctrl_num' => $saleOrderData->ctrl_num,
                            'ref_name_delivery_note' => $saleOrderData->ref_name_sales_order,
                            'residual_amount_delivery_note' => $pagos - $total,
                            'total_amount_delivery_note' => $total,
                            'exempt_amout_delivery_note' => $data['exento'],
                            'id_order_state' => $state,
                            'no_exempt_amout_delivery_note' => $data['subFac'],
                            'date_delivery_note' => date('Y-m-d'),
                        ]
                    );
                DeliveryNotesDetails::where('id_delivery_note', '=', $deliveryNote[0]->id_delivery_note)
                    ->update(
                        [
                            'id_delivery_note' => $deliveryNote[0]->id_delivery_note,
                            'details_delivery_notes' => json_encode($dataDetails),
                        ]
                    );
            } else {
                DeliveryNotes::where('id_delivery_note', '=', $deliveryNote[0]->id_delivery_note)
                    ->update(
                        [
                            'type_payment' => $saleOrderData->type_payment,
                            'id_client' => $saleOrderData->id_client,
                            'id_exchange' => $saleOrderData->id_exchange,
                            'ctrl_num' => $saleOrderData->ctrl_num,
                            'ref_name_delivery_note' => $saleOrderData->ref_name_sales_order,
                            'residual_amount_delivery_note' => $total,
                            'total_amount_delivery_note' => $total,
                            'exempt_amout_delivery_note' => $data['exento'],
                            'id_order_state' => 6,
                            'no_exempt_amout_delivery_note' => $data['subFac'],
                            'date_delivery_note' => date('Y-m-d'),
                        ]
                    );
                DeliveryNotesDetails::where('id_delivery_note', '=', $deliveryNote[0]->id_delivery_note)
                    ->update(
                        [
                            'id_delivery_note' => $deliveryNote[0]->id_delivery_note,
                            'details_delivery_notes' => json_encode($dataDetails),
                        ]
                    );
            }
        }
        return redirect()->route('sales-order.index')->with('message', 'Se actualizo el pedido con ??xito');
    }


    public function anular($id, $id_delivery_note="")
    {

        
        $dataSalesOrderDetails = salesOrderDetails::whereIdSalesOrder($id)->get()[0];
        $obj = json_decode($dataSalesOrderDetails->details_order_detail, true);
        for ($i = 0; $i < count($obj['id_product']); $i++) {
            $sumar =  Product::select('qty_product')->whereIdProduct($obj['id_product'][$i])->get()[0];
            $operacion = $sumar->qty_product + $obj['cantidad'][$i];
            Product::whereIdProduct($obj['id_product'][$i])->update(['qty_product' => $operacion]);
        }
        SalesOrder::whereIdSalesOrder($id)->update(['id_order_state' => 3]);

        //$deliveryNote = DeliveryNotes::where('ref_name_delivery_note', '=', SalesOrder::find($id)->ref_name_sales_order)->get();



        if ($id_delivery_note) {
            DeliveryNotes::where('id_delivery_note', '=', $id_delivery_note)->update(['id_order_state' => 3,  'residual_amount_delivery_note' => (new DeliveryNotesController)->getDataDN($id_delivery_note)->total_amount_delivery_note]);
        }


        return redirect()->route('sales-order.show', $id);
    }

    public function listar(Request $request)
    {
        if ($request->texto == 'clientes') {
            if (isset($request->param)) {
                $dataClientes =  DB::select("SELECT * 
                                                FROM clients 
                                                WHERE name_client LIKE '%" . $request->param . "%' 
                                                OR idcard_client LIKE '%" . $request->param . "%'");
                return response()->json(
                    [
                        'lista' => $dataClientes,
                        'th' => ['Cedula', 'Nombre o Razon social'],
                        'success' => true,
                        'title' => 'Lista de Clientes'
                    ]
                );
            }
            $dataClientes = Client::whereEnabledClient(1)->get();
            return response()->json(
                [
                    'lista' => $dataClientes,
                    'th' => ['Cedula', 'Nombre o Razon social'],
                    'success' => true,
                    'title' => 'Lista de Clientes'
                ]
            );
        } else {
            if (is_int($request->param) == true) {
                $request->param = "";
            }
            if ($request->param != "") {
                $dataProductos =  DB::select("SELECT products.*, p.name_presentation_product, u.name_unit_product, u.short_unit_product
                                                FROM products 
                                                INNER JOIN presentation_products AS p ON p.id_presentation_product = products.id_presentation_product
                                                INNER JOIN unit_products AS u ON u.id_unit_product = products.id_unit_product
                                                INNER JOIN warehouses AS w ON w.id_warehouse = products.id_warehouse
                                                WHERE qty_product > 0 
                                                AND salable_product = 1
                                                AND name_product LIKE '%" . $request->param . "%' 
                                                OR code_product LIKE '%" . $request->param . "%'
                                                ORDER BY products.name_product ASC");
                return response()->json(
                    [

                        'lista' => $dataProductos,
                        'th' => ['Codigo', 'Descripcion', 'Unidad', 'Presentacion', 'Cantidad', 'Precio', 'Ref $'],
                        'success' => true,
                        'title' => 'Lista de Productos'

                    ]
                );
            } else {

                $dataProductos =  DB::select("SELECT products.*, p.name_presentation_product, u.name_unit_product, u.short_unit_product
                                                FROM products 
                                                INNER JOIN presentation_products AS p ON p.id_presentation_product = products.id_presentation_product
                                                INNER JOIN unit_products AS u ON u.id_unit_product = products.id_unit_product
                                                INNER JOIN warehouses AS w ON w.id_warehouse = products.id_warehouse
                                                WHERE qty_product > 0 
                                                AND salable_product = 1
                                                ORDER BY products.name_product ASC");
                return response()->json(
                    [

                        'lista' => $dataProductos,
                        'th' => ['Codigo', 'Descripcion', 'Unidad', 'Presentacion', 'Cantidad', 'Precio', 'Ref $'],
                        'success' => true,
                        'title' => 'Lista de Productos'

                    ]
                );
            }
        }
    }

    public function disponible(Request $request)
    {
        $data = $request;
        $actual = Product::select('qty_product', 'tax_exempt_product', 'product_usd_product')->whereIdProduct($data['producto'])->get();
        if ($data['cantidad'] <= $actual[0]->qty_product) {
            if ($actual[0]->tax_exempt_product == 1) {
                return response()->json(['respuesta' => true, 'exento' => true]);
            } else {
                return response()->json(['respuesta' => true, 'exento' => false]);
            }
        } else {
            return response()->json(['respuesta' => false, 'cantid' => $actual[0]->qty_product]);
        }
    }

    public function getDataSO($id)
    {
        return SalesOrder::find($id);
    }
}
