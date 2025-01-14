<?php
namespace TrabajoSube;

use Exception;
use TrabajoSube\Boleto;
use TrabajoSube\Tarjeta;

class Colectivo
{
    private $tarifa;
    private $linea;
    private $esInterurbana;
    public function __construct($linea, $esInterurbana)
    {
        $this->linea = $linea;
        $this->esInterurbana = (bool) $esInterurbana;

        if( $this->esInterurbana ) {
            $this->tarifa = 184;
        }
        else {
            $this->tarifa = 185;
        }
    }

    public function pagarCon(Tarjeta $tarjeta, TiempoInterface $tiempo)
    {
        $costoNormal = $this->tarifa;
        $tipoTarjeta = 'normal';
        $saldoNegativo = 0;
        $diferencia = $tarjeta->getSaldo() - $costoNormal;
        $saldoInicial = $tarjeta->getSaldo();

        if ($diferencia >= $tarjeta->getMinSaldo()) {
            // Verificar si la tarjeta es de tipo MedioBoleto y ajustar el costo del boleto
            if ($tarjeta instanceof MedioBoleto) {
                $tarjeta->cambiarTiempo($tiempo);
                $tipoTarjeta = 'parcial';
                if($tarjeta->esFranjaHorariaValida($tiempo)){
                    if($tarjeta->pasaron5Mins($tiempo)){
                        if($tarjeta->puedePagarMedioBoleto($tiempo)){
                            $costoNormal    =$tarjeta->calcularCostoBoleto ($costoNormal);
                            $tarjeta->descontarSaldo($costoNormal);
                            $tarjeta->incrementarViaje();
                            if($tarjeta->puedeCargarSaldoPendiente()){
                                $tarjeta->cargarSaldoPendiente($saldoInicial);
                            }
                            $tarjeta->setUltimoViaje($tiempo);
                        }
                        else{
                            $tarjeta->descontarSaldo($costoNormal);
                            $tarjeta->incrementarViaje();
                            if($tarjeta->puedeCargarSaldoPendiente()){
                                $tarjeta->cargarSaldoPendiente($saldoInicial);
                            }
                            $tarjeta->setUltimoViaje($tiempo);
                        }
                    }
                    else{
                        return false;
                    }
                }
                else{
                    return false;
                }
            }
            elseif($tarjeta instanceof FranquiciaCompleta){
                $tarjeta->cambiarTiempo($tiempo);
                $tipoTarjeta = 'completa';
                if($tarjeta->esFranjaHorariaValida($tiempo)){
                    if($tarjeta->puedeViajarGratis($tiempo)){
                        $costoNormal = 0;
                        $tarjeta->descontarSaldo($costoNormal);
                        $tarjeta->incrementarViaje();
                        if($tarjeta->puedeCargarSaldoPendiente()){
                            $tarjeta->cargarSaldoPendiente($saldoInicial);
                        }
                        $tarjeta->setUltimoViaje($tiempo);
                    }
                    else{
                        $tarjeta->descontarSaldo($costoNormal);
                        $tarjeta->incrementarViaje();
                        if($tarjeta->puedeCargarSaldoPendiente()){
                            $tarjeta->cargarSaldoPendiente($saldoInicial);
                        }
                    }
                }
                else{
                    return false;
                }
            }
            elseif ($tarjeta instanceof Tarjeta) {
                $costoNormal *=
                $tarjeta->calcularCostoBoletoNormal($tiempo);
                $tarjeta->descontarSaldo($costoNormal);
                $tarjeta->incrementarViaje();
                $tarjeta->setFechaUltimoViaje($tiempo);
                if($tarjeta->puedeCargarSaldoPendiente()){
                    $tarjeta->cargarSaldoPendiente($saldoInicial);
                }
            }
            
            if($diferencia < 0){
                $saldoNegativo = abs($diferencia);
            }
            return new Boleto($saldoInicial, $costoNormal,$tipoTarjeta,$this->linea,$tarjeta->getId(),$abonoNegativo = $saldoNegativo, $tiempo);
        } else {
            return false;
        }
    }
}
?>
