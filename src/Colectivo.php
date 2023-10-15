<?php
namespace TrabajoSube;

use Exception;
use TrabajoSube\Boleto;
use TrabajoSube\Tarjeta;

class Colectivo
{
    private $tarifa = 185;
    private $linea;

    public function __construct($linea)
    {
        $this->linea = $linea;
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
                if($tarjeta->puedeRealizarViaje($tiempo)){
                    if($tarjeta->getCantViajesDia() < 4){
                        $costoNormal    =$tarjeta->calcularCostoBoleto ($costoNormal);
                    }
                    $tipoTarjeta = 'parcial';
                    $tarjeta->descontarSaldo($costoNormal);
                    $tarjeta->setUltimoViaje($tiempo);
                }
                else{
                    return false;
                }
            }
            elseif($tarjeta instanceof FranquiciaCompleta){
                $costoNormal = 0;
                $tipoTarjeta = 'completa';
                $tarjeta->descontarSaldo($costoNormal);
            }
            elseif ($tarjeta instanceof Tarjeta) {
                $tarjeta->descontarSaldo($costoNormal);
            }
            
            if($diferencia < 0){
                $saldoNegativo = abs($diferencia);
            }
            return new Boleto($saldoInicial, $costoNormal,$tipoTarjeta,$this->linea,$tarjeta->getId(),$abonoNegativo = $saldoNegativo);
        } else {
            return false;
        }
    }
}
?>
