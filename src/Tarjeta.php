<?php
namespace TrabajoSube;

use Exception;
use PHPUnit\Event\Test\PassedSubscriber;

class Tarjeta
{
    private $id;
    private $saldo;
    private $limiteSaldo = 6600;
    private $minSaldo = -211.84;
    private $viajesPlus;

    public $tipoFranquicia;

    private $cargasPosibles = array(150, 200, 250, 300, 350, 400, 450, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1300, 1400, 1500, 2000, 2500, 3000, 3500, 4000);

    public function __construct($saldoInicial = 0)
    {
        $this->id = $this->generarID();
        if ($saldoInicial < 0) {
            throw new Exception("El saldo inicial no puede ser negativo.");
        }
        $this->saldo = $saldoInicial;
        $this->viajesPlus = 2;
        $this->tipoFranquicia = 'normal';
    }
    private function generarID()
    {
        static $contador = 1;
        return $contador++;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSaldo()
    {
        return $this->saldo;
    }

    public function verifyMonto($monto)
    {
        if (($this->saldo + $monto) <= 6600 && in_array($monto, $this->cargasPosibles)) {
            return true;
        } else {
            return false;
        }
    }


    public function cargarSaldo($monto)
    {
        if ($this->verifyMonto($monto)) {
            $this->saldo += $monto;
        } else {
            echo "No se puede cargar saldo";
        }
    }

    public function descontarSaldo($montoDescontar)
    {
        $this->saldo -= $montoDescontar;
    }

    public function getMinSaldo()
    {
        return $this->minSaldo;
    }

    public function getViajesPlus()
    {
        return $this->viajesPlus;
    }

    public function usarViajePlus(){
        $this->viajesPlus--;
    }

}

class FranquiciaCompleta extends Tarjeta
{
    public function __construct($saldoInicial = 0)
    {
        parent::__construct($saldoInicial);
        $this->tipoFranquicia = 'completa';
    }
}

class MedioBoleto extends Tarjeta
{
    private $ultimoViaje;
    private $cantViajesDia;
    protected $tiempo;

    public function __construct($saldoInicial = 0, TiempoInterface $tiempo)
    {
        parent::__construct($saldoInicial);
        $this->tipoFranquicia = 'parcial';
        $this->ultimoViaje = null;
        $this->cantViajesDia = 0;
        $this->tiempo = $tiempo; // Inyectar TiempoInterface
    }

    public function calcularCostoBoleto($costoNormal)
    {
        if ($this->cantViajesDia <= 4) {
            return $costoNormal / 2; // El costo del boleto es siempre la mitad del normal
        } else {
            return $costoNormal;
        }
    }

    public function setUltimoViaje(TiempoInterface $tiempo)
    {
        $this->ultimoViaje = $tiempo->time();
        $this->cantViajesDia ++;
    }

    public function getUltimoViaje()
    {
        return $this->ultimoViaje;
    }

    public function getCantViajesDia()
    {
        return $this->cantViajesDia;
    }

    public function puedeRealizarViaje(TiempoInterface $tiempo)
    {
        if ($this->ultimoViaje === null) {
            return true; // Si es el primer viaje, siempre se permite
        }

        if($this->cantViajesDia >= 4){
            return true;
        }

        $tiempoActual = $tiempo->time(); // Utiliza la implementación de TiempoInterface
        $tiempoPasado = $tiempoActual - $this->ultimoViaje;

        // Si es un nuevo día, reiniciar el contador de viajes realizados
        if (date('d/m/Y', $tiempoActual) !== date('d/m/Y', $this->ultimoViaje)) {
            $this->viajesRealizadosHoy = 0;
        }

        // Se permite el viaje si han pasado al menos 5 minutos (300 segundos) y no se han excedido los 4 viajes en un día
        return ($tiempoPasado >= 300);
    }

    public function cambiarTiempo($tiempoCambiar){
        $this->tiempo = $tiempoCambiar;
    }
}


?>
