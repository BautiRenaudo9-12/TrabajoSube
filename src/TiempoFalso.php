<?php
namespace TrabajoSube;

use Exception;
use PHPUnit\Event\Test\PassedSubscriber;

interface TiempoInterface {
    public function time();
}

class Tiempo implements TiempoInterface {
    public function time() {
        return time();
    }
}

class TiempoFalso implements TiempoInterface
{
    protected $tiempo;
    
    public function __construct($iniciarEn = 0){
        $this->tiempo = $iniciarEn;
    }

    public function avanzarSegundos($segundos)
    {
        $this->tiempo += $segundos;
    }

    public function time()
    {
        return $this->tiempo;
    }
}
?>