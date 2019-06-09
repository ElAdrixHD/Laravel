<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = ['fecha_reserva', 'hora_inicio', 'hora_fin', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
