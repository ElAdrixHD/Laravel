@extends('layouts.app')

@section('content')
<style>
  .uper {
    margin-top: 40px;
  }
</style>

<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Listado de tus reservas</h2>
        </div>
        <div class="pull-right" align="right">
                <a class="btn btn-info mr-4 mb-2" href="{{ route('home') }}">Home</a>
                <a class="btn btn-success mr-4 mb-2" href="{{ route('reservas.create') }}">Crear Nueva Reserva</a>
        </div>
    </div>
</div>

<div class="uper">
  @if (session()->get('success'))
    <div class="alert alert-success">
      {{ session()->get('success') }}  
    </div><br />
  @endif
  @if (session()->get('error'))
    <div class="alert alert-danger">
      {{ session()->get('error') }}  
    </div><br />
  @endif

  <table class="table table-striped">
    <thead>
        <tr>
          <td>ID</td>
          <td>Fecha Reserva</td>
          <td>Hora Inicio</td>
          <td>Hora Fin</td>
          <td colspan="2">Action</td>
        </tr>
    </thead>
    <tbody>
        @foreach($reservas as $reserva)
        <tr>
            <td>{{$reserva->id}}</td>
            <td>{{$reserva->fecha_reserva}}</td>
            <td>{{$reserva->hora_inicio}}</td>
            <td>{{$reserva->hora_fin}}</td>
            <td><a href="{{ route('reservas.edit',$reserva->id)}}" class="btn btn-primary">Editar</a></td>
            <td>
                <form action="{{ route('reservas.destroy', $reserva->id)}}" method="post">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger" onclick="return confirm('Are you sure to delete this site?')" type="submit">Borrar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
  </table>
</div>
@endsection