{{-- resources/views/layouts/adminlte-custom.blade.php --}}
@extends('adminlte::page')

@section('content_header')
    <!-- Banner de Impersonate -->
    <x-impersonate-banner />
    
    @yield('content_header_content')
    @yield('page_header')
@stop

@section('css')
    @parent
    <!-- Adicione aqui seus estilos CSS customizados, se precisar -->
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

        /* Faz o card inteiro clicável com cursor pointer */
        .info-box-link {
            cursor: pointer;
            display: block;
            text-decoration: none;
            color: inherit;
        }
    </style>
    @yield('page_css')
@stop

@section('js')
    @parent
    <!-- Scripts customizados, se precisar -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.info-box-link').forEach(function(card) {
                card.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    if(url) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
    @yield('page_js')
@stop
