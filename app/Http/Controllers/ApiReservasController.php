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
use Validator;

class ApiReservasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return response()->json(['sites' => auth()->user()->sites], 200);
        return response()->json(Reserva::where([['user_id', Auth::user()->id],['fecha_reserva','>=', new Carbon("now")]])->get(), 200);
        // Paginación?
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_reserva'=>'required|date_format:"Y-m-d',
            'hora_inicio'=> 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->getMessages();
            $status = 422;
            //return response()->json( $validator->errors()->getMessages(), 422);
        } else {
            $fecha = new Carbon($request->fecha_reserva);
            $inicio = new Carbon($request->hora_inicio);
            $fin = new Carbon($request->hora_fin);

            if(!(DB::table('reservas')->where('fecha_reserva', '=', $request->fecha_reserva)->where('user_id','=',Auth::user()->id)->exists())){
                $errores = $this->validarDatos($fecha,$inicio,$fin);
                if(count($errores)){
                    $message = 'La reserva no pudo ser añadida';
                    foreach ($errores as $error){
                        $message .= PHP_EOL."".$error.".";
                    }
                    $status = 500;
                }else{
                    $reserva = new Reserva([
                        'fecha_reserva' => $request->fecha_reserva,
                        'hora_inicio'=> $request->hora_inicio,
                        'hora_fin'=> $request->hora_fin,
                        //'user_id' => Auth::user()->id
                        'user_id' => Auth::user()->id
                    ]);
    
                    if (auth()->user()->reservas()->save($reserva)) {
                        //return response()->json( $site, 201);
                        $message = $reserva;
                        $status = 201;
                        $to = Auth::user()->email;
                        try {
                            Mail::to($to)->send(new NewReservasEmail($reserva));
                        } catch (Exception $exception) {
                            $status = "error";
                            $message = $message . ', but the email has not been sent. Error: ' . $exception->getMessage();              
                        }
                    } else {
                        //return response()->json( 'El sitio no pudo ser añadido', 500);
                        $message = 'La reserva no pudo ser añadida';
                        $status = 500;
                    }
                
                }
            }else{
                $message =" Ya tienes una reserva en ese dia";
                $status = 500;
            }
        }
        return response()->json($message, $status);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reserva = auth()->user()->reservas()->find($id);

        if (!$reserva) {
            $message = 'Reserva no encontrada';
            $status = 404;
        } else {
            $message['reserva'] = $reserva;
            $status = 200;
        }

        return response()->json($message, $status); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $reserva = auth()->user()->reservas()->find($id);

        if (!$reserva) {
            $message = 'Reserva no encontrada';
            $status = 404;
        } else {
            $validator = Validator::make($request->all(), [
                'fecha_reserva'=>'required|required|date_format:"Y-m-d',
                'hora_inicio'=> 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors()->getMessages();
                $status = 422;
                //return response()->json( $validator->errors()->getMessages(), 422);
            } else {

                $fecha = new Carbon($request->get('fecha_reserva'));
                $inicio = new Carbon($request->get('hora_inicio'));
                $fin = new Carbon($request->get('hora_fin'));

                $errores = $this->validarDatos($fecha,$inicio,$fin);
                if(count($errores)){
                    $message = 'La reserva ' . $reserva->fecha_reserva . ' no pudo ser actualizada';
                    foreach ($errores as $error){
                        $message .= PHP_EOL."".$error.".";
                    }
                    $status = 422;
                }else{
                    $updated = $reserva->update($request->all());
                    //$updated = $site->fill($request->all())->save();
                    if ($updated) {
                        $message = $reserva;
                        $status = 201;
                    } else {
                        $message = 'La reserva ' . $reserva->fecha_reserva . ' no pudo ser actualizada';
                        $status = 500;
                    }
                }
                
            }
        }    
        return response()->json($message, $status); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {       
       $reserva = auth()->user()->reservas()->find($id);

        if ($reserva == null) {
            $message = 'Reserva no encontrada';
            $status = 405;
        } else {
            if ($reserva->delete()) {
                $message = null;
                $status = 204;
            } else {
                $message = 'La reserva no pudo ser eliminada';
                $status = 500;
            }
        }

        return response()->json($message, $status);
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
