@extends('adminlte::page')

@section('title', 'Novo PPP')

@section('content_header')
    <h1>Novo PPP</h1>
@endsection

@section('content')
    @include('ppp.form', ['method' => 'POST', 'action' => route('ppp.store')])
@endsection
