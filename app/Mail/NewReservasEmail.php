<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Reserva;

class NewReservasEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $reserva;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.newreserva')
                    ->with('reserva', $this->reserva)
                    //->from($address, $name)
                    ->from('correo@adrianmmudarra.es', 'Mr.Correo');
                    //->cc($address, $name)
                    //->bcc($address, $name)
                    //->replyTo($address, $name)
                    //->subject($subject);
    }
}
