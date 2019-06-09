<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Validator;

class PassportController extends Controller
{
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {       
         $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {      
            $message['error'] = $validator->errors();
            $status = 401;          
       } else {        
           $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            //Generar token de activación
            //$token = $user->createToken('MyApp')->accessToken;
            //$message['token'] = $token;
            //$status = 200;

            //enviar email de validación
            $user->sendEmailVerificationNotification();
            $message['info'] = "Registro OK. Verifique su email. Le hemos enviado un correo para confirmarlo";
            $status = 200;
        }

        return response()->json($message, $status);
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($credentials)) {
        // Control de verificacion del email.
            if (auth()->user()->email_verified_at != null) {
                $token = auth()->user()->createToken('MyApp')->accessToken;
                $message['token'] = $token;
                $status = 200;
            } else {
                $message['error'] = 'No puede acceder hasta verificar su email mediante el correo que ha recibido';
                $status = 401;
            }
        } else {
            $message['error'] = 'Usuario o contraseña incorrectos';
            $status = 401;
        }

        return response()->json($message, $status);
        //return response()->json($credentials, $status);
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails()
    {
        return response()->json(['user' => auth()->user()], 200);
    }

     /**
     * Handles Logout Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $result = $request->user()->token()->revoke();

        if ($result) {
            $message['message'] = "Logout OK";
            $status = 200;
        } else {
            $message['error'] = "Error al intentar hacer logout";
            $status = 401;
        }

        return response()->json($message, $status);
    }
}
