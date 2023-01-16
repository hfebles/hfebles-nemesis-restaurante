<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{!! date('dmY', strtotime($data->date_invoicing)) . '_' . $data->ref_name_invoicing . '_' . $data->name_client !!}</title>
    <style>
        * {
            font-size: 11pt;
            font-family: sans-serif, arial;
        }

        /* .divs {
            width: 100%;
            height: auto;
        }
        .divs.header{
            margin-bottom:20px;
            margin-top: 20px;
        }

        .divs.body table tr td{
            padding: 5px 5px;
        }

        .divs.footer {
            margin-top: 20px;
            position: fixed;
            bottom: 0%;
        }

        .divs.footer table{
            width: 100%;
            
        }

        .text-center{
            text-align: center;
        }

        .text-end{
            text-align: right;
        } */
    </style>
</head>

<body>
    <div class="">
        <table width="100%">
            <tr>
                <td style="text-align:center;" colspan="2">
                    <h3>Lista de compras</h3>
                </td>
            </tr>
            <tr>
                <td width="5%"><strong>Receta: </strong></td>
                <td>{{ $data->nombre_receta }}</td>
            </tr>
            <tr>
                <td width="20%"><strong>Cantidad de platos: </strong></td>
                <td>{{ $cant }}</td>
            </tr>
        </table>
    </div>
    <div style="margin-top:40px;">
        <table width="100%" border="1" style="border-collapse: collapse">
            <tr>
                <td style="text-align:center; font-weight:bold;">Producto</td>
                <td style="text-align:center; font-weight:bold;">Cantidad</td>
                <td style="text-align:center; font-weight:bold;">Unidad</td>
            </tr>
            @foreach ($detalles['name_product'] as $k => $d)
                <tr>
                    <td style="text-align:center">{{ strtoupper($d) }}</td>
                    <td style="text-align:right">
                        {{ number_format($detalles['qtys'][$k], 3, ',', '.') }}
                    </td>
                    <td style="text-align:center">{{ $detalles['und'][$k] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @for ($j = 0; $j < count($details2); $j++)
        <div style="margin-top:40px;">
            <table width="100%">
                <tr>
                    <td width="15%"><strong>Sub-Receta: </strong></td>
                    <td>{{ $details2[$j * 2]['nombre_receta'] }}</td>
                </tr>
            </table>
            <table width="100%" border="1" style="border-collapse: collapse">
                @foreach ($detaill['name_product'][$j * 2] as $key => $d)
                    <tr>
                        <td style="text-align:center">{{ strtoupper($d) }}</td>
                        <td style="text-align:right">
                            {{ number_format($detaill['qtys'][$j * 2][$key], 3, ',', '.') }}
                        </td>
                        <td style="text-align:center">{{ $detaill['und'][$j * 2][$key] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endfor
</body>

</html>
