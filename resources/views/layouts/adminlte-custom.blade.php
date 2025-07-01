@extends('adminlte::page')

@section('content_header')
    <!-- Banner de Impersonate -->
    <x-impersonate-banner />
    
    @yield('page_header')
@stop

@section('css')
    <style>
        /* Ajustes específicos para AdminLTE */
        .impersonate-banner {
            margin: -15px -15px 20px -15px;
            border-radius: 0;
        }
        
        /* Ajuste para o content-header quando banner estiver ativo */
        .content-header {
            padding-top: 0;
        }
    </style>
    @yield('page_css')
@stop

@section('js')
    @yield('page_js')
@stop