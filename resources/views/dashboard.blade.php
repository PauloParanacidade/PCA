@extends('layouts.adminlte-custom')

@section('title', 'Dashboard')

@section('page_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bem-vindo ao Sistema PCA</h3>
                    </div>
                    <div class="card-body">
                        <!-- Adicione aqui widgets, estatísticas, etc. -->
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page_css')
    <!-- CSS específico do dashboard, se necessário -->
@stop

@section('page_js')
    <script>
        console.log('Dashboard carregado!');
    </script>
@stop