@extends('layouts.app')

@section('content')
<style>
  .uper {
    margin-top: 40px;
  }
</style>
<div class="card uper">
  <div class="card-header">
    Añadir Reserva
  </div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
      </div><br />
    @endif
      <form method="post" action="{{ route('reservas.store') }}">
          <div class="form-group">
              @csrf
              <label for="fecha_reserva">Fecha Reserva:</label>
              <input type="date" class="form-control" name="fecha_reserva"/>
          </div>
          <div class="form-group">
              <label for="hora_inicio">Hora Inicio:</label>
              <input type="time" class="form-control" name="hora_inicio"/>
          </div>
          <div class="form-group">
              <label for="hora_fin">Hora Fin:</label>
              <input type="time" class="form-control" name="hora_fin"/>
          </div>
          <button type="submit" class="btn btn-primary">Crear Reserva</button>
          <a class="btn btn-secondary" href="{{ route('reservas.index') }}"> Cancelar</a>
      </form>
  </div>
</div>
@endsection