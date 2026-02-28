@extends('adminlte::page')

@section('content_header')
    <h1><b>Listado de clientes</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Clientes registrados</h3>

                    <div class="card-tools">
                       
                        <a href="{{ url('/admin/clientes/create') }}" 
                        class="btn btn-primary">Crear nuevo cliente</a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="example1" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                        <tr>
                            <th style="text-align:center">Nro</th>
                            <th style="text-align:center">Codigo Cliente</th>
                            <th style="text-align:center">Celular</th>
                            <th style="text-align:center">Descripción</th>
                            <th style="text-align:center">Ubicación GPS</th>
                            <th style="text-align:center">Latitud</th>
                            <th style="text-align:center">Longitud</th>
                            <th style="text-align:center">Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $contador = 1; @endphp
                        @foreach($clientes as $cliente)
                            <tr>
                                <td style="text-align:center">{{ $contador++ }}</td>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ $cliente->celular_real }}</td>
                                <td>{{ $cliente->direccion }}</td>

                                {{-- NUEVAS COLUMNAS --}}
                                <td>
                                    @if($cliente->ubicacion_gps)
                                        <a href="{{ $cliente->ubicacion_gps }}" target="_blank">Ver enlace</a>
                                    @else
                                        <span class="text-muted">No registrado</span>
                                    @endif
                                </td>
                                <td>{{ $cliente->latitud ?? '—' }}</td>
                                <td>{{ $cliente->longitud ?? '—' }}</td>

                                <td style="text-align:center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ url('/admin/clientes/'.$cliente->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ url('/admin/clientes/'.$cliente->id.'/edit') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-pencil-alt"></i> Editar
                                        </a>
                                        <form action="{{ url('/admin/clientes', $cliente->id) }}" method="post"
                                              onclick="preguntar{{$cliente->id}}(event)" id="miFormulario{{$cliente->id}}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Borrar
                                            </button>
                                        </form>
                                        <script>
                                            function preguntar{{$cliente->id}}(event) {
                                                event.preventDefault();
                                                Swal.fire({
                                                    title: '¿Desea eliminar este cliente?',
                                                    icon: 'question',
                                                    showDenyButton: true,
                                                    confirmButtonText: 'Eliminar',
                                                    confirmButtonColor: '#a5161d',
                                                    denyButtonText: 'Cancelar',
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        document.getElementById('miFormulario{{$cliente->id}}').submit();
                                                    }
                                                });
                                            }
                                        </script>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@stop


@section('css')
    <style>
        #example1_wrapper .dt-buttons {
            background-color: transparent;
            box-shadow: none;
            border: none;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        #example1_wrapper .btn {
            color: #fff;
            border-radius: 4px;
            padding: 5px 15px;
            font-size: 14px;
        }

        .btn-danger { background-color: #dc3545; border: none; }
        .btn-success { background-color: #28a745; border: none; }
        .btn-info { background-color: #17a2b8; border: none; }
        .btn-warning { background-color: #ffc107; color: #212529; border: none; }
        .btn-default { background-color: #6e7176; color: #212529; border: none; }
    </style>
@stop


@section('js')
    <script>
        $(function () {
            $("#example1").DataTable({
                pageLength: 10,
                language: {
                    emptyTable: "No hay información",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ Clientes",
                    infoEmpty: "Mostrando 0 a 0 de 0 Clientes",
                    infoFiltered: "(Filtrado de _MAX_ total Clientes)",
                    lengthMenu: "Mostrar _MENU_ Clientes",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscador:",
                    zeroRecords: "Sin resultados encontrados",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                },
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                buttons: [
                    { text: '<i class="fas fa-copy"></i> COPIAR', extend: 'copy', className: 'btn btn-default' },
                    { text: '<i class="fas fa-file-pdf"></i> PDF', extend: 'pdf', className: 'btn btn-danger' },
                    { text: '<i class="fas fa-file-csv"></i> CSV', extend: 'csv', className: 'btn btn-info' },
                    { text: '<i class="fas fa-file-excel"></i> EXCEL', extend: 'excel', className: 'btn btn-success' },
                    { text: '<i class="fas fa-print"></i> IMPRIMIR', extend: 'print', className: 'btn btn-warning' }
                ]
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>
@stop