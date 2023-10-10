<?php
namespace TrabajoSube;

use Exception;
use TrabajoSube\Boleto;
use TrabajoSube\Tarjeta;

class Colectivo
{
    private $tarifa = 185;


    public function pagarCon(Tarjeta $tarjeta)
    {
        if($tarjeta->getSaldo() - $this->tarifa >= $tarjeta->getMinSaldo()){
            $tarjeta->descontarSaldo($this->tarifa);
            return new Boleto($tarjeta->getSaldo(), $this->tarifa);
        }
        elseif (condition) {
            # code...
        }
        else{
            return false;
        }
    }
}
?>