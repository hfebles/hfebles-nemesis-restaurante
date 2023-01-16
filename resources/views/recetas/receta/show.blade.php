@extends('layouts.app')

@section('title-section', $conf['title-section'])

@section('btn')
    <x-btns :back="$conf['back']" :edit="$conf['edit']" :group="$conf['group']" :delete="$conf['delete']" />
@endsection

@section('content')

    <div class="row mb-3">
        <x-cards>
            <div class="table-responsive-lg">
                <table class="table table-bordered table-hover table-sm mb-3">
                    <tr class="align-middle">
                        <th scope="col" width="20%">Nombre de la receta:</th>
                        <td width="40%">{{ $data->nombre_receta }}</td>
                    </tr>
                </table>
                <table class="table table-bordered table-hover table-sm mb-0">
                    <tr>
                        <th scope="col" class="text-center">Producto:</th>
                        <th scope="col" class="text-center">Cantidad</th>
                        <th scope="col" class="text-center">Merma</th>
                        <th scope="col" class="text-center">Crudo</th>
                        <th scope="col" class="text-center">Und</th>
                        <th scope="col" class="text-center">Costo</th>
                        <th scope="col" class="text-center">Total</th>
                    </tr>

                    @foreach ($details['name_product'] as $k => $d)
                        <tr>
                            <td class="text-center">
                                <input type="hidden" class="ids" value="{{ $details['id_product_details'][$k] }}">
                                {{ strtoupper($d) }}</td>
                            <td class="text-end">
                                <input type="hidden" class="cant" value="{{ $details['qtys'][$k] }}"
                                    id="td_{{ $k }}">
                                {{ number_format($details['qtys'][$k], 3, ',', '.') }}
                            </td>
                            <td class="text-end">{{ number_format($details['merma'][$k], 3, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($details['crudo'][$k], 3, ',', '.') }}</td>
                            <td class="text-center">{{ $details['und'][$k] }}</td>
                            <td class="text-end">{{ number_format($details['costo'][$k], 3, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($details['total'][$k], 3, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td>Rendimiento</td>
                        <td class="text-end"><b>{{ number_format($data->redimiento_receta, 3, ',', '.') }}</b></td>
                        <td colspan="3"></td>
                        <td>Costo Total</td>
                        <td class="text-end"><b>{{ number_format($data->costo_total, 2, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td>Costo Unitario</td>
                        <td class="text-end"><b>{{ number_format($data->costo_unitario, 2, ',', '.') }}</b></td>
                    </tr>

                </table>
            </div>
        </x-cards>
    </div>
    <div class="row mb-3">
        <x-cards>
            <table class="table table-bordered table-hover table-sm mb-3">
                <tr>
                    <th>Lista de compras</th>
                </tr>
                <tr>
                    <td class="align-middle">Cantidad de platos a preparar</td>
                    <td class="align-middle"><input type="number" class="form-control form-control-sm" id="cant" />
                    </td>
                    <td class="align-middle text-center"><button id="btn" class="btn btn-success"
                            onclick="actualzarCantidades();">Actualizar</button></td>
                </tr>
            </table>

            <table id="myTable" class="table table-bordered table-hover table-sm mb-0">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                </tr>
                @foreach ($details['name_product'] as $k => $d)
                        <tr>
                            <td class="text-center">{{ strtoupper($d) }}</td>
                            <td class="text-end">
                                <span class="x"></span>
                            </td>
                            <td class="text-center">{{ $details['und'][$k] }}</td>
                        </tr>
                    @endforeach
                        
                        <tr>
                            <td colspan="3" class="text-center"><a target="_blank" id="imprimir" class="btn btn-success" >Imprimir</a></td>
                        </tr>
            </table>
        </x-cards>
    </div>





@endsection

@section('js')
    <script>
        function actualzarCantidades() {
            var inp = $('.cant');
            var cant = document.getElementById("cant");
            var x = $('.x')
            for (let o = 0; o < inp.length; o++) {
                x[o].innerHTML =inp[o].value * cant.value
            }

            document.getElementById("imprimir").href='/recetas/impimir-lista-comras/{{ $data->id_receta }}/'+cant.value;
            //$('#btn').disabled;

        }

    </script>
@endsection
