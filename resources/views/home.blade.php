@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                <div class="content">
                    <div class="title m-b-md">
                        Reservas de pistas de padel
                    </div>

                    <div class="links">
                        <a class="btn btn-success" href="{{ route('reservas.create') }}"> Crear una nueva reserva</a>
                        <a class="btn btn-info" href="{{ route('reservas.index') }}"> Ver Reservas</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
