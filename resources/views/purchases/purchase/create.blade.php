@extends('layouts.app')

@section('title-section', $conf['title-section'])

@section('btn')
    <x-btns :back="$conf['back']" :group="$conf['group']" />
@endsection

@section('content')
    {!! Form::open([
        'route' => 'purchase.store',
        'method' => 'POST',
        'novalidate',
        'class' => 'needs-validation',
        'id' => 'myForm',
    ]) !!}
    <div class="row">
        <x-cards>
            <table class="table table-sm table-bordered mb-0">
                <tr>
                    <td class="text-end align-middle">Compra número:</td>
                    <td width="15%" class="text-end">
                        <span class="fs-4">{{ $dataConfiguration->correlative_purchase_config }}{{ str_pad($ctrl, 6, '0', STR_PAD_LEFT) }}</span>
                        <input type="hidden" value="{{ $dataConfiguration->correlative_purchase_config }}{{ str_pad($ctrl, 6, '0', STR_PAD_LEFT) }}" name="ref_name_purchase" />
                    </td>

                </tr>
                <tr>
                    <td class="text-end align-middle">Nro control:</td>
                    <td width="15%" class="text-end">
                        <span class="fs-4">{{ str_pad($ctrl, 6, '0', STR_PAD_LEFT) }}</span>
                        <input type="hidden" value="{{ $ctrl }}" name="ctrl_num" />
                        <input type="hidden" value="{{ str_pad($ctrl, 6, '0', STR_PAD_LEFT) }}" name="ctrl_num_purchase" />
                    </td>
                </tr>

                <tr>
                    <td class="text-end align-middle">Factura proveedor:</td>
                    <td width="15%" class="text-end">
                        <input type="text" class='form-control form-control-sm' name="supplier_order" />
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle">Fecha Factura:</td>
                    <td width="15%" class="text-end">
                        {!! Form::date('date_purchase', \Carbon\Carbon::now(), ['max' => date('Y-m-d'), 'class' => 'form-control form-control-sm', 'required']) !!}
                    </td>
                </tr>
            </table>

            <table class="table table-sm table-bordered mb-4">

                <tr>
                    <td width="100%" class="d-flex justify-content-between">
                        <span>Razón social: </span>
                        <a onclick="abreModal('proveedor')" class="btn btn-sm btn-info btn-circle" type="button"><i
                                class="fas fa-search fa-lg"></i></a>
                    </td>
                    <td>
                        <input type="hidden" id="id_supplier" name="id_supplier">
                        <span id="razon_social"></span>
                    </td>
                </tr>
                <tr>
                    <td width="25%">Cédula ó R.I.F.:</td>
                    <td>
                        <span id="dni"></span>
                    </td>
                </tr>
                <tr>
                    <td width="25%">Teléfono: </td>
                    <td>
                        <span id="telefono"></span>
                    </td>
                </tr>
                <tr>
                    <td width="25%">Dirección: </td>
                    <td>
                        <span id="direccion"></span>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle" width="25%">Tipo de Pago: </td>
                    <td>
                        <select class="form-select form-control-sm" required name="type_payment">
                            <option value="">Seleccione</option>
                            <option value="1">Contado</option>
                            <option value="2">Credito</option>
                        </select>
                    </td>
                </tr>

            </table>
            <table class="table table-sm border-dark table-bordered mb-4" id="myTable">
                <tr>

                    <th scope="col" colspan="2" class="align-middle">DESCRIPCIÓN</th>
                    <th scope="col" class="text-center align-middle" width="10%">CANTIDAD</th>
                    <th scope="col" class="text-center align-middle" width="10%">P/U</th>
                    <th scope="col" class="text-center align-middle " width="10%">SUB-TOTAL</th>
                    <th scope="col" class="text-center align-middle bg-success" width="4%"><a onclick="addRow()"
                            class="btn btn-success btn-sm mb-0 btn-block"><i class="fas fa-plus-circle fa-lg"></i></a></th>
                </tr>
            </table>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="85%" scope="col" class="text-end align-middle">BASE IMPONIBLE: </th>
                    <td class="text-end align-middle">
                        <p class='align-middle mb-0' id="subFacs"></p><input type="hidden" id="subFac" name="subFac">
                    </td>
                </tr>
                <tr>
                    <th width="85%" scope="col" class="text-end align-middle">EXENTO: </th>
                    <td class="text-end align-middle">
                        <p class='align-middle mb-0' id="exentos"></p><input type="hidden" id="exento" name="exento">
                    </td>
                </tr>
                <tr>
                    <th width="85%" scope="col" class="text-end align-middle">IMPUESTO:
                        @foreach ($taxes as $tax)
                            <div class="form-check form-switch">
                                <input checked class="form-check-input" onchange="calculateTaxes({{ $tax->amount_tax }})"
                                    value="{{ $tax->amount_tax }}" type="checkbox" id="taxt_{{ $tax->amount_tax }}">
                                <label class="form-check-label" for="">{{ $tax->name_tax }}
                                    {{ $tax->amount_tax }}%</label>
                            </div>
                        @endforeach

                    </th>
                    <td class="text-end align-middle">
                        <p class='align-middle mb-0' id="totalIVaas"></p><input type="hidden" id="totalIVa"
                            name="total_taxes">
                    </td>
                </tr>
                <tr>
                    <th width="85%" scope="col" class="text-end align-middle">TOTAL A PAGAR: </th>
                    <td class="text-end align-middle">
                        <p class='align-middle mb-0' id="totalTotals"></p><input type="hidden" id="totalTotal"
                            name="total_con_tax">
                    </td>
                </tr>
            </table>



        </x-cards>

    </div>
    <input type="hidden" id="tasa" value="{{ $dataExchange->amount_exchange }}" />
    <input type="hidden" id="id_tasa" name="id_exchange" value="{{ $dataExchange->id_exchange }}" />
    <x-btns-save />
    {!! Form::close() !!}




    <!-- Modal -->
    <div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title-modal"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cierraModal()"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div id="buscar"></div>
                        </div>
                        <div class="col-12">
                            <div id="divsito"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cierraModal()"
                            data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    @endsection
    @section('js')
        <script>
            var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));

            function cierraModal() {
                myModal.hide()
            }

            var i = 0;

            function validateClient() {
                var cliente = document.getElementById('id_supplier').value;
                if (cliente == "") {
                    return false
                } else {
                    return true
                }
            }

            function addRow() {
                if (validateClient() == true) {
                    var table = document.getElementById("myTable");
                    var row = table.insertRow(-1);
                    row.id = 'tr_' + i

                    var cell2 = row.insertCell(-1);
                    var cell3 = row.insertCell(-1);
                    var cell4 = row.insertCell(-1);
                    var cell5 = row.insertCell(-1);
                    var cell6 = row.insertCell(-1);
                    var cell7 = row.insertCell(-1);

                    cell2.innerHTML = '<a id="search_productos_' + i + '" onclick="abreModal(\'producto\', ' + i +
                        ')" class="btn btn-info btn-block mb-0"><i class="fas fa-search fa-lg"></i></a>';
                    //cell2.id = "td_"+i
                    cell2.className = "align-middle bg-info"
                    cell2.width = "3%"

                    cell3.innerHTML = "<p class='align-middle mb-0' id='name_product" + i + "'></p>";
                    cell3.id = "td_" + i
                    cell3.className = "align-middle"


                    cell4.innerHTML = "<input type='hidden' name='id_product[]' id='id_product_" + i +
                        "'><input onkeyup='calculate(" + i +
                        ", this.value)' class='form-control' autocomplete='off' id='cant_" + i +
                        "' type='number' name='cantidad[]'>";
                    cell4.className = "text-center align-middle"

                    cell5.innerHTML = "<input type='text' name='precio_producto[]' onkeyup='calculate("+i+
                        ",this.value)' class='form-control' autocomplete='off' id='price_product_" + i + "'>";
                    cell5.className = "text-center align-middle"

                    cell6.innerHTML = "<p class='align-middle  mb-0' id='subtotals_" + i + "'></p>";
                    cell6.className = "text-center align-middle"
                    cell6.id = "tds_" + i

                    cell7.innerHTML =
                        '<a onclick="borrarRow(this)" class="btn btn-block mb-0 btn-danger mb-0"><i class="fas fa-minus-circle"></i></a>';
                    cell7.className = "text-center align-middle bg-danger"

                    i++

                } else {
                    alert('Debe seleccionar primero al proveedor')
                    abreModal('proveedor')
                }
            }

a

            function calculate(x = "", y = "", xx = "", precio) {
                var id_product = document.getElementById('id_product_' + x).value
                var cc = document.getElementById('cant_' + x)

     
                    const csrfToken = "{{ csrf_token() }}";
                    fetch('{{ route("purchase.check-aviavility") }}', {
                        method: 'POST',
                        body: JSON.stringify({
                            cantidad: y,
                            producto: id_product
                        }),
                        headers: {
                            'content-type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }).then(response => {
                        return response.json();
                    }).then(data => {
                        if (data.respuesta == true) {

                            let precio_unitario = document.getElementById('price_product_' + x).value
                            let cantidad = document.getElementById('cant_' + x).value
                            let subtotal = (precio_unitario * cantidad).toFixed(2)


                            document.getElementById('subtotals_' + x).innerHTML = 'Bs. ' + subtotal
                            document.getElementById('subtotal_' + x).value = subtotal

                            var suma = 0
                            var sumaNo = 0
                            var total = 0

                            var exe = document.getElementsByName('subtotal_exento[]')
                            var noExe = document.getElementsByName('subtotal[]')

                            for (let e = 0; e < exe.length; e++) {
                                valor = exe[e].value || 0
                                suma += parseFloat(valor)
                            }
                            for (let e = 0; e < noExe.length; e++) {
                                valor = noExe[e].value || 0
                                sumaNo += parseFloat(valor)
                            }
                            // sumado = Math.round(sumaNo * 100) / 100



                            document.getElementById('subFacs').innerHTML = 'Bs. ' + sumaNo.toFixed(2)
                            document.getElementById('exentos').innerHTML = 'Bs. ' + suma.toFixed(2)


                            document.getElementById('subFac').value = sumaNo.toFixed(2)
                            document.getElementById('exento').value = suma.toFixed(2)

                            if (document.getElementById('taxt_16').checked == true) {
                                calculateTaxes(16)
                            }

                        } else {
                            alert('Intruduce una cantidad valida o mayor a la cantidad actual que es: ' + data.cantid)
                            cc.value = ""
                        }
                    });
       
            }

            function calculateTaxes(valueTax) {

                var subFac = document.getElementById('subFac').value
                var exento = document.getElementById('exento').value

                if (valueTax == 16) {
                    if (document.getElementById('taxt_' + valueTax).checked == true) {
                        IvaCalculado = (parseFloat(valueTax) / 100) * parseFloat(subFac)
                    } else {
                        IvaCalculado = 0.00;
                    }
                    totalTotalito = ((parseFloat(subFac) + IvaCalculado) + parseFloat(exento))
                    document.getElementById('totalIVaas').innerHTML = 'Bs. ' + IvaCalculado.toFixed(2);
                    document.getElementById('totalTotals').innerHTML = 'Bs. ' + totalTotalito.toFixed(2)
                    document.getElementById('totalIVa').value = IvaCalculado.toFixed(2);
                    document.getElementById('totalTotal').value = totalTotalito;
                }

            }

            function borrarRow(x) {

                var i = x.parentNode.parentNode.rowIndex;
                document.getElementById("myTable").deleteRow(i);
                var suma = 0
                var sumaNo = 0
                var total = 0
                var exe = document.getElementsByName('subtotal_exento[]')
                var noExe = document.getElementsByName('subtotal[]')
                for (let e = 0; e < exe.length; e++) {
                    valor = exe[e].value || 0
                    suma += parseFloat(valor)
                }
                for (let e = 0; e < noExe.length; e++) {
                    valor = noExe[e].value || 0
                    sumaNo += parseFloat(valor)
                }
                document.getElementById('subFacs').innerHTML = 'Bs. ' + sumaNo.toFixed(2)
                document.getElementById('exentos').innerHTML = 'Bs. ' + suma.toFixed(2)
                document.getElementById('subFac').value = sumaNo.toFixed(2)
                document.getElementById('exento').value = suma.toFixed(2)
                if (document.getElementById('taxt_16').checked == true) {
                    calculateTaxes(16)
                }
            }

            function abreModal(x, y = "") {
                myModal.show()
                if (x == 'proveedor') {
                    seleccionar(x);
                } else if (x == 'producto') {
                    seleccionar(x, y)
                }
            }


            function cargar(x, y, z = "", tasa = "") {
                if (y == 'proveedor') {
                    seleccionarCliente(x)
                } else {
                    seleccionarProducto(x, z, tasa)
                }
            }

            function seleccionarCliente(x) {
                document.getElementById('id_supplier').value = x.id_supplier
                document.getElementById('razon_social').innerHTML = x.name_supplier
                document.getElementById('dni').innerHTML = x.idcard_supplier
                document.getElementById('direccion').innerHTML = x.address_supplier
                document.getElementById('telefono').innerHTML = x.phone_supplier
                myModal.hide()
            }

            function seleccionarProducto(x, y, tasa) {
                y = y - 1

                var exchangeRate = document.getElementById('tasa').value

                var input = document.createElement("input");
                document.getElementById('id_product_' + y).value = x.id_product
                input.setAttribute("type", "hidden");
                input.setAttribute("name", "exempt_product[]");
                input.setAttribute("id", 'exempt_product_' + y);


                var input2 = document.createElement("input");
                input2.setAttribute("type", "hidden");
                input2.setAttribute("id", 'subtotal_' + y);

                var input3 = document.createElement("input");
                input3.setAttribute("type", "hidden");
                input3.setAttribute("name", "noExento[]");
                input3.setAttribute("id", 'noExempt_product_' + y);

                if (x.tax_exempt_product == 1) {
                    input.setAttribute("value", x.price_product);
                    input2.setAttribute("name", "subtotal_exento[]");
                    document.getElementById("tds_" + y).appendChild(input2);
                    document.getElementById("td_" + y).appendChild(input);
                    document.getElementById('name_product' + y).innerHTML = x.code_product + " " + x.name_product + " (E)"
                } else {
                    input2.setAttribute("name", "subtotal[]");

                    document.getElementById("td_" + y).appendChild(input3);
                    document.getElementById("tds_" + y).appendChild(input2);
                    document.getElementById('name_product' + y).innerHTML = x.code_product + " " + x.name_product

                }
                document.getElementById('search_productos_' + y).style.display = 'none'
                document.getElementById("td_" + y).colSpan = "2";
                document.getElementById('tr_' + y).deleteCell(0);

                myModal.hide()

            }

            function creaBusqueda(tipo, valorActual = "") {
                document.getElementById('buscar').innerHTML =
                    '<input class="form-control" placeholder="Buscar" type="text" id="searchModal" onkeyup="seleccionar(\'' +
                    tipo + '\', this.value);" />'
                const input = document.getElementById('searchModal')
                if (valorActual != "") {
                    input.value = valorActual
                } else if (isNaN(valorActual)) {
                    input.value = ""
                } else {
                    input.value = ""
                }
                const end = input.value.length;
                input.setSelectionRange(end, end);
                input.focus();


            }


            function seleccionar(x, y = "") {

                creaBusqueda(x, y);
                var exchangeRate = document.getElementById('tasa').value

                var linea2 = "";
                const csrfToken = "{{ csrf_token() }}";
                fetch('/purchase/search', {
                    method: 'POST',
                    body: JSON.stringify({
                        texto: x,
                        param: y
                    }),
                    headers: {
                        'content-type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).then(response => {
                    return response.json();
                }).then(data => {
                    var col = document.getElementById('divsito');
                    document.getElementById('title-modal').innerHTML = data.title;
                    var a = "";
                    var b = "";
                    var c = "";
                    linea2 += '<div class="col-12">'
                    linea2 += '<table class="table table-sm table-bordered table-hover">'
                    linea2 += '<thead class="bg-dark text-white">'
                    linea2 += '<tr>'
                    for (let ths in data.th) {
                        linea2 += '<th col="scope" class="text-center">'
                        linea2 += data.th[ths]
                        linea2 += '</th>'
                    }
                    linea2 += '</thead>'
                    linea2 += '</tr>'



                    for (let t in data.lista) {
                        c = data.lista[t]

                        var a = JSON.stringify(c);
                        if (c.id_product != undefined) {
                            linea2 += '<tr onclick=\'cargar(' + a + ', "' + x + '", ' + i + ')\'>'
                            linea2 += '<td class="text-center">' + c.code_product + '</td>'
                            if (c.tax_exempt_product == 0) {
                                linea2 += '<td class="text-center">' + c.name_product + '</td>'
                            } else {
                                linea2 += '<td class="text-center">' + c.name_product + ' (E)</td>'
                            }

                            linea2 += '<td class="text-center">' + c.name_unit_product + '</td>'
                            linea2 += '<td class="text-center">' + c.name_presentation_product + '</td>'
                            linea2 += '<td class="text-center">' + c.qty_product + '</td>'
                            if (c.product_usd_product == 0) {
                                linea2 += '<td class="text-center">Bs. ' + c.price_product + '</td>'
                                linea2 += '<td class="text-center">N/A</td>'
                            } else {
                                linea2 += '<td class="text-center">Bs. ' + (c.price_product * exchangeRate) + '</td>'
                                linea2 += '<td class="text-center">$ ' + c.price_product + '</td>'
                            }
                            linea2 += '</tr>'
                        } else {
                            linea2 += '<tr onclick=\'cargar(' + a + ', "' + x + '", ' + i + ')\'>'
                            linea2 += '<td class="text-center">' + c.name_supplier + '</td>'
                            linea2 += '<td class="text-center">' + c.idcard_supplier + '</td>'
                            linea2 += '</tr>'
                        }


                    }
                    linea2 += '</table>'
                    col.innerHTML = linea2
                });
            }

            (function() {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }

                            form.classList.add('was-validated')
                        }, false)
                    })
            })()

            (function() {

                function decimalAdjust(type, value, exp) {
                    // Si el exp no está definido o es cero...
                    if (typeof exp === 'undefined' || +exp === 0) {
                        return Math[type](value);
                    }
                    value = +value;
                    exp = +exp;
                    // Si el valor no es un número o el exp no es un entero...
                    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
                        return NaN;
                    }
                    // Shift
                    value = value.toString().split('e');
                    value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
                    // Shift back
                    value = value.toString().split('e');
                    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
                }

                // Decimal round
                if (!Math.round10) {
                    Math.round10 = function(value, exp) {
                        return decimalAdjust('round', value, exp);
                    };
                }
                // Decimal floor
                if (!Math.floor10) {
                    Math.floor10 = function(value, exp) {
                        return decimalAdjust('floor', value, exp);
                    };
                }
                // Decimal ceil
                if (!Math.ceil10) {
                    Math.ceil10 = function(value, exp) {
                        return decimalAdjust('ceil', value, exp);
                    };
                }
            })();
        </script>
    @endsection
