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

    public function pagarCon(Tarjeta $tarjeta)
    {
        $costoNormal = $this->tarifa;
        $tipoTarjeta = 'normal';
        $saldoNegativo = 0;
        $diferencia = $tarjeta->getSaldo() - $costoNormal;
        $saldoInicial = $tarjeta->getSaldo();

        // Verificar si la tarjeta es de tipo MedioBoleto y ajustar el costo del boleto
        if ($tarjeta instanceof MedioBoleto) {
            $costoNormal = $tarjeta->calcularCostoBoleto($costoNormal);
            $tipoTarjeta = 'parcial';
        }
        elseif($tarjeta instanceof FranquiciaCompleta){
            $costoNormal = 0;
            $tipoTarjeta = 'completa';
        }

        if ($diferencia >= $tarjeta->getMinSaldo()) {
            $tarjeta->descontarSaldo($costoNormal);
            if($diferencia < 0){
                $saldoNegativo = abs($diferencia);
            }
            return new Boleto($saldoInicial, $costoNormal,$tipoTarjeta,$this->linea,$tarjeta->getId(),0,$abonoNegativo = $saldoNegativo);
        } else {
            return false;
        }
    }
}
?>
