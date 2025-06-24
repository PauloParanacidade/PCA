@extends('adminlte::page')

@section('title', 'Meus PPPs')

@section('content_header')
    <h1>Meus PPPs</h1>
@endsection

@section('content')
<div class="container">
    <h3 class="mb-4">Meus PPPs</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($ppps->isEmpty())
        <div class="alert alert-info">Você ainda não cadastrou nenhum PPP.</div>
    @else
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>PPP</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ppps as $ppp)
                    <tr>
                        <td>{{ $ppp->nome_item }}</td>
                        <td>{{ $ppp->descricao }}</td>
                        <td>—</td>
                        <td class="text-center">
                            <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-sm btn-warning">Editar</a>

                            <!-- Botão Histórico (abre modal) -->
                            <button type="button" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#historicoModal-{{ $ppp->id }}">
                                Histórico
                            </button>

                            <a href="{{ route('ppp.show', $ppp->id) }}" class="btn btn-sm btn-info">Ver</a>

                            <form action="{{ route('ppp.destroy', $ppp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Você tem certeza que deseja remover este PPP?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Histórico -->
                    <div class="modal fade" id="historicoModal-{{ $ppp->id }}" tabindex="-1" role="dialog" aria-labelledby="historicoModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Histórico do PPP: {{ $ppp->nome_item }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Ainda não há histórico registrado.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
