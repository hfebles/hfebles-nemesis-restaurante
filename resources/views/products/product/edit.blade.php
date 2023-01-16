@extends('layouts.app')

@section('title-section', $conf['title-section'])

@section('btn')
<x-btns :back="$conf['back']" :group="$conf['group']" />
@endsection


@section('content')

{!! Form::model($data, ['novalidate', 'class' => 'needs-validation', 'method' => 'PATCH', 'route' => ['product.update', $data->id_product]]) !!}
<div class="row g-4">
    <x-cards>  
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">Nombre del producto</label>
                {!! Form::text('name_product', null, array( 'autocomplete' => 'off','required', 'placeholder' => 'Ingrese el nombre del producto','class' => 'form-control form-control-sm')) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar el nombre del producto
                </div>  
            </div>
            <div class="clearfix"></div>
            <div class="col-md-3">
                <label class="form-label">Precio del producto</label>
                {!! Form::number('price_product', null, array( 'autocomplete' => 'off','required', 'placeholder' => 'Ingrese el precio del producto','class' => 'form-control form-control-sm', 'min'=>'0', 'step' => '0.01')) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar el precio del producto
                </div>  
            </div>

            <div class="col-md-3">
                <label class="form-label">Cantidad</label>
                {!! Form::number('qty_product', null, array( 'autocomplete' => 'off','required', 'placeholder' => 'Ingrese la cantidad del producto','class' => 'form-control form-control-sm', 'min'=>'0', 'step' => '0.01')) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar la cantidad del producto
                </div>  
            </div>
            <div class="col-md-3">
                <label class="form-label">Merma</label>
                {!! Form::number('merma', null, array( 'autocomplete' => 'off','required', 'placeholder' => 'Ingrese la cantidad de la merma','class' => 'form-control form-control-sm', 'min'=>'0', 'step' => '0.01')) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar la cantidad de la merma
                </div>  
            </div>
            
            <div class="clearfix"></div>
            <div class="col-3 d-flex align-items-start justify-content-start ml-2">
                <div class="form-check">
                    {!! Form::checkbox('salable_product', '1', null, ['class' => 'form-check-input'],) !!}
                    <label class="form-check-label">
                        ¿Producto Vendible?
                    </label>
                </div> 
            </div>
            <div class="col-3 d-flex align-items-start justify-content-start">
                <div class="form-check">
                    {!! Form::checkbox('product_usd_product', '1', null, ['class' => 'form-check-input'],) !!}
                    <label class="form-check-label">
                        ¿Producto en dolares?
                    </label>
                </div>  
            </div>          
            <div class="clearfix"></div>
            <div class="col-md-3">
                <label class="form-label">Almacen</label>
                {!! Form::select('id_warehouse', $getWarehouses, null, ['required', 'class' => 'form-select form-control-sm', 'placeholder' => 'Seleccione']) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar un almacen para el producto
                </div>  
            </div>

            <div class="col-md-3">
                <label class="form-label">Unidad de medida</label>
                {!! Form::select('id_unit_product', $getUnits, null, ['required', 'class' => 'form-select form-control-sm', 'placeholder' => 'Seleccione']) !!}
                <div  class="invalid-feedback">
                    Para guardar debe ingresar el nombre de la unidad de medida producto
                </div>  
            </div>

        

            
            
            
            
                   
        </div>
        
    </x-cards>


    <x-cards>    
        <div class="text-center">
            <button type="submit" id="btnGuardar" class="btn btn-success">Guardar</button>
            <input class="btn btn-danger" type="reset" value="Deshacer">
        </div>
    </x-cards>
</div>
{!! Form::close() !!}

@endsection


@section('js')
    <script>

(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()


function searchCode(value){



    const csrfToken = "{{ csrf_token() }}";
    
    
    var lineas = "";

    fetch('/products/product/search-code', {
        method: 'POST',
        body: JSON.stringify({text: value}),
        headers: {
            'content-type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        } 
    }).then(response => {
        return response.json();
    }).then( data => {
        //console.log(data);

        if (data.res == true) {
            document.querySelector('#valido_code_product').innerHTML=data.msg;
            document.querySelector('#valido_code_product').style.color = "green";
            document.querySelector('#valido_code_product').style.display = "block";
            document.querySelector('#btnGuardar').disabled = false;
        }else{
            document.querySelector('#valido_code_product').innerHTML=data.msg;
            document.querySelector('#valido_code_product').style.display = "block";
            document.querySelector('#valido_code_product').style.color = "red";
            document.querySelector('#btnGuardar').disabled = true;
        }


        
    });
}

    </script>
@endsection