@extends('layouts.app')

@section('title-section', $conf['title-section'])

@section('btn')
    <x-btns :back="$conf['back']" :group="$conf['group']" />
@endsection


@section('content')

    {!! Form::open(['route' => 'recetas.store', 'method' => 'POST', 'novalidate', 'class' => 'needs-validation']) !!}
    <div class="row g-4">
        <x-cards>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Nombre de la receta</label>
                    {!! Form::text('nombre_receta', null, [
                        'autocomplete' => 'off',
                        'required',
                        'placeholder' => 'Ingrese el nombre de la receta',
                        'class' => 'form-control form-control-sm',
                    ]) !!}
                    <div class="invalid-feedback">
                        Para guardar debe ingresar el nombre de la receta
                    </div>
                </div>
                <div class="clearfix"></div>


                <div class="col-md-3">
                    <label class="form-label">Rendimiento de la receta</label>
                    {!! Form::text('redimiento_receta', null, [
                        'autocomplete' => 'off',
                        'required',
                        'placeholder' => 'rendimiento de la receta',
                        'class' => 'form-control form-control-sm',
                    ]) !!}
                    <div class="invalid-feedback">
                        Para guardar debe ingresar el rendimiento de la receta
                    </div>
                </div>
                <div class="col-3 d-flex align-items-start justify-content-start">
                    <div class="form-check">
                        {!! Form::checkbox('sub_receta', '1', '', ['class' => 'form-check-input']) !!}
                        <label class="form-check-label">
                            ¿sub-receta?
                        </label>
                    </div>
                </div>
            </div>

        </x-cards>
    </div>

    <div class="row g-4 mt-4">
        <x-cards>
            <table class="table table-sm border-dark table-bordered mb-4" id="myTable">
                <tr>

                    <th scope="col" class="align-middle">PRODUCTO</th>
                    <th scope="col" class="text-center align-middle" width="10%">CANTIDAD</th>
                    <th scope="col" class="text-center align-middle bg-success" width="4%"><a onclick="addRow()"
                            class="btn btn-success btn-sm mb-0 btn-block"><i class="fas fa-plus-circle fa-lg"></i></a></th>
                </tr>
            </table>
        </x-cards>
    </div>
    <x-btns-save />
    {!! Form::close() !!}

@endsection


@section('js')
    <script>
        var i = 0;
        const csrfToken = "{{ csrf_token() }}";

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


        function borrarRow(x) {
            var i = x.parentNode.parentNode.rowIndex;
            document.getElementById("myTable").deleteRow(i);
        }

        function addRow() {
            var data = [];
            select = "";

            var inp = $('.ids');
            for (let o = 0; o < inp.length; o++) {
                data.push(inp[o].value)
            }

            //console.log()


            var table = document.getElementById("myTable");
            var row = table.insertRow(-1);
            row.id = 'tr_' + i
            var cell2 = row.insertCell(-1);
            var cell3 = row.insertCell(-1);
            var cell5 = row.insertCell(-1);
            if (inp.length == 0) {
                select = `{!! Form::select('id_product_details[]', $productos, null, [
                    'required',
                    'class' => 'form-select form-control-sm ids',
                    'placeholder' => 'Seleccione',
                ]) !!}`;
                cell2.innerHTML = select;
            } else {
                fetch('{{ route('recetas.listar') }}', {
                    method: 'POST',
                    body: JSON.stringify({
                        ids: data,
                    }),
                    headers: {
                        'content-type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).then(response => {
                    return response.json();
                }).then(data => {
                    select = `<select name="id_product_details[]" class="form-select form-control-sm ids">`
                    for (d in data[0]) {
                        select +=
                            `<option value="${data[0][d].id_product}">${data[0][d].name_product} - ${data[0][d].name_unit_product}</option>`
                    }
                    select += `</select>`
                    cell2.innerHTML = select;
                });

            }
            cell3.innerHTML =
                "<input type='text' name='qtys[]' required class='form-control form-control-sm' onkeypress='return soloNumeros(event);' autocomplete='off'>";
            cell5.innerHTML =
                '<a onclick="borrarRow(this)" class="btn btn-block mb-0 btn-danger mb-0"><i class="fas fa-minus-circle"></i></a>';
            i++
        }

        function soloNumeros(e) {
            key = e.keyCode || e.which;
            tecla = String.fromCharCode(key).toLowerCase();
            letras = "1234567890.";
            especiales = [];

            tecla_especial = false
            for (var i in especiales) {
                if (key == especiales[i]) {
                    tecla_especial = true;
                    break;
                }
            }

            if (letras.indexOf(tecla) == -1 && !tecla_especial)
                return false;
        }
    </script>
@endsection
