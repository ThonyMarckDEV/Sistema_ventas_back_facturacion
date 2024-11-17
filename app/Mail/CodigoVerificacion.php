<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoVerificacion extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;

    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }
    
    public function build()
    {
        return $this->view('emails.codigoVerificacion') // Asegúrate de que la vista exista
                    ->subject('Código de Verificación')
                    ->with(['codigo' => $this->codigo]);
    }
}
