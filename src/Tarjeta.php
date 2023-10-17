<?php
namespace TrabajoSube;

use Exception;
use PHPUnit\Event\Test\PassedSubscriber;

class Tarjeta
{
    private $id;
    private $saldo;
    private $limiteSaldo = 6600;
    private $montoPendienteAcreditacion = 0;
    private $minSaldo = -211.84;

    public $tipoFranquicia;

    private $cargasPosibles;

    public function __construct($saldoInicial = 0)
    {
        $this->id = $this->generarID();
        $this->cargasPosibles = array(150, 200, 250, 300, 350, 400, 450, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1300, 1400, 1500, 2000, 2500, 3000, 3500, 4000);
        if ($saldoInicial < 0 ) {
            throw new Exception("El saldo inicial no puede ser negativo.");
        }
        if($saldoInicial <= 6600){
            $this->saldo = $saldoInicial;
            $this->tipoFranquicia = 'normal';
        }
        else{
            throw new Exception("La tarjeta no puede almacenar mas de 6600 pesos.");
        }
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
        if (in_array($monto, $this->cargasPosibles)) {
            return true;
        } else {
            return false;
        }
    }

    public function cargarSaldo($monto)
    {
        if ($this->verifyMonto($monto)) {
            if(($this->saldo + $monto) < 6600){
                $this->saldo += $monto;
            }
            else{
                $this->montoPendienteAcreditacion += $this->saldo + $monto - 6600;
                $this->saldo = 6600;
            }
        } else {
            echo "No se puede cargar saldo";
        }
    }

    public function puedeCargarSaldoPendiente(){
        if($this->montoPendienteAcreditacion > 0){
            return true;
        }
    }

    public function cargarSaldoPendiente($saldoAnterior){
        $supuestoSaldoAcreditado = $this->montoPendienteAcreditacion + $this->saldo;
        if($supuestoSaldoAcreditado <= 6600 ){
            $this->montoPendienteAcreditacion = 0;
            $this->saldo = $supuestoSaldoAcreditado;
        }
        else{
            $this->montoPendienteAcreditacion = $supuestoSaldoAcreditado - 6600;
            $this->saldo = 6600;
        }
    }

    public function getMontoPendiente(){
        return $this->montoPendienteAcreditacion;
    }

    public function descontarSaldo($montoDescontar)
    {
        $this->saldo -= $montoDescontar;
    }

    public function getMinSaldo()
    {
        return $this->minSaldo;
    }

    public function getTipoTarjeta(){
        return $this->tipoFranquicia;
    }
}

class FranquiciaCompleta extends Tarjeta
{
    private $ultimoViaje;
    private $cantViajesDia;
    protected $tiempo;

    public function __construct($saldoInicial = 0, TiempoInterface $tiempo)
    {
        parent::__construct($saldoInicial);
        $this->tipoFranquicia = 'completa';
        $this->ultimoViaje = null;
        $this->cantViajesDia = 0;
        $this->tiempo = $tiempo;
    }

    public function getCantViajesDia()
    {
        return $this->cantViajesDia;
    }

    public function setUltimoViaje($tiempo){
        $this->ultimoViaje = $tiempo->time();
        $this->cantViajesDia ++;
    }

    public function getUltimoViaje(){
        return $this->ultimoViaje;
    }

    public function puedeViajarGratis(TiempoInterface $tiempo)
    {
        $fechaActual = date('d/m/Y', $tiempo->time());
        return ($this->cantViajesDia < 2 && ($fechaActual === date('d/m/Y', $this->ultimoViaje) || $this->ultimoViaje === null));
    }

    public function cambiarTiempo($tiempoCambiar){
        $this->tiempo = $tiempoCambiar;
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
        $this->tiempo = $tiempo;
    }

    public function calcularCostoBoleto($costoNormal)
    {
        if ($this->cantViajesDia <= 4) {
            return $costoNormal / 2;
        } else {
            return $costoNormal;
        }
    }

    public function getCantViajesDia()
    {
        return $this->cantViajesDia;
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

    public function puedePagarMedioBoleto(TiempoInterface $tiempo)
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
