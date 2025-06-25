@extends('adminlte::page')

@section('title', 'Editar PPP')

@section('content_header')
    <h1>Editar PPP</h1>
@endsection

@section('content')
    @include('ppp.form', [
        'method' => 'PUT',
        'action' => route('ppp.update', $ppp->id),
        'ppp' => $ppp
    ])
@endsection
