<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Reserva;
use Exception;
use Carbon\Carbon;
use App\Mail\NewReservasEmail;
use Illuminate\Support\Facades\Mail;

class ReservasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reservas = Reserva::where([['user_id', Auth::user()->id],['fecha_reserva','>=', new Carbon("now")]])->get();
        return view('reservas.index', compact('reservas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('reservas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_reserva'=>'required|date',
            'hora_inicio'=> 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
          ]);

          $fecha = new Carbon($request->get('fecha_reserva'));
          $inicio = new Carbon($request->get('hora_inicio'));
          $fin = new Carbon($request->get('hora_fin'));
        
          if(!(DB::table('reservas')->where('fecha_reserva', '=', $request->get('fecha_reserva'))->where('user_id','=',Auth::id())->exists())){
            $errores = $this->validarDatos($fecha,$inicio,$fin);
            if(count($errores)){
                return redirect('/reservas/create')->withInput()->withErrors($errores);
            }else{
                $reserva = new Reserva([
                    'fecha_reserva' => $request->get('fecha_reserva'),
                    'hora_inicio'=> $request->get('hora_inicio'),
                    'hora_fin'=> $request->get('hora_fin'),
                    //'user_id' => Auth::user()->id
                    'user_id' => Auth::id()
                  ]);
                  $reserva->save();     
                  $status = "success";
                  $message = 'A new site ' . $reserva . ' has been added';

                  $to = Auth::user()->email;
                try {
                        Mail::to($to)->send(new NewReservasEmail($reserva));
                        $message = $message . " and an email has been sent";
                } catch (Exception $exception) {
                    $status = "error";
                    $message = $message . ', but the email has not been sent. Error: ' . $exception->getMessage();              
                    }

            
                  return redirect('/reservas')->with($status, $message);
            }
          }else{
              $error = "Ya existe una reserva en este dia";
              return redirect('/reservas/create')->withInput()->withErrors($error);
          }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reserva = Reserva::findOrFail($id);

        return view('reservas.edit', compact('reserva'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            //'name'=>'required|string|unique:sites|max:20',
            'fecha_reserva'=>'required|date',
            'hora_inicio'=> 'required|date_format:H:i:s',
            'hora_fin' => 'required|date_format:H:i:s|after:hora_inicio',
        ]);

          $fecha = new Carbon($request->get('fecha_reserva'));
          $inicio = new Carbon($request->get('hora_inicio'));
          $fin = new Carbon($request->get('hora_fin'));

          $errores = $this->validarDatos($fecha,$inicio,$fin);
            if(count($errores)){
                return redirect('/reservas/create')->withInput()->withErrors($errores);
            }else{
                $reserva = Reserva::findOrFail($id);
          $reserva->fecha_reserva = $request->get('fecha_reserva');
          $reserva->hora_inicio = $request->get('hora_inicio');
          $reserva->hora_fin = $request->get('hora_fin');
          $reserva->save();
    
          return redirect('/reservas')->with('success', 'Reserva ' . $reserva->fecha_reserva . ' has been updated');
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reserva = Reserva::find($id);
        $temp = $reserva->fecha_reserva;
        $reserva->delete();

     return redirect('/reservas')->with('success', 'Reserva ' . $temp . ' has been deleted Successfully');
    }

    function validarDatos(Carbon $fecha,Carbon $inicio,Carbon $fin){
        $horaMin = new Carbon('09:00');
        $horaMax = new Carbon('23:00');
        $errores = array();

        $fecha->setTime($inicio->hour, $inicio->minute);
        if ($fecha->lessThanOrEqualTo(new Carbon("now"))) {
            array_push($errores,"Las fechas deben estar en el futuro");
        }

        if ($inicio->diffInHours($fin, true) > 2) {
            array_push($errores,"La duración máxima debe ser de 2 horas");
        }

        if ($fecha->diffInDays(new Carbon("now")) > 7) {
            array_push($errores,"No puedes hacer una reserva con mas de 7 dias de antelación");
        }

        if ($inicio->lessThan($horaMin) || $inicio->greaterThan($horaMax) || $fin->lessThan($horaMin) || $fin->greaterThan($horaMax)) {
            array_push($errores,"Solo se puede reservar de 9 de la mañana a 11 de la noche.");
        }

        if (DB::table('reservas')->where('fecha_reserva', '=', $fecha->toDateString())->where('hora_inicio','>', $inicio->toTimeString())->where('hora_inicio','<',$fin->toTimeString())->exists()){
            array_push($errores,"La hora fin está dentro de un tramo que ya hay una reserva");
        }

        if (DB::table('reservas')->where('fecha_reserva', '=', $fecha->toDateString())->where('hora_fin','>', $inicio->toTimeString())->where('hora_fin','<=',$fin->toTimeString())->exists()){
            array_push($errores,"La hora inicio está dentro de un tramo que ya hay una reserva");
        }

        if (DB::table('reservas')->where('fecha_reserva', '=', $fecha->toDateString())->where('hora_inicio','<=', $inicio->toTimeString())->where('hora_fin','>=',$fin->toTimeString())->exists()){
            array_push($errores,"Estas reservando dentro de un tramo de otra reserva");
        }

        if (DB::table('reservas')->where('fecha_reserva', '=', $fecha->toDateString())->where('hora_inicio','>=', $inicio->toTimeString())->where('hora_fin','<=',$fin->toTimeString())->exists()){
            array_push($errores,"Los horarios escogidos ya estan ocupados");
        }

        return $errores;
    }
}
