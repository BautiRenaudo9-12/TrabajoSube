<?php
namespace TrabajoSube;

use PHPUnit\Framework\TestCase;
use TrabajoSube\Boleto;
use TrabajoSube\Colectivo;
use TrabajoSube\FranquiciaCompleta;
use TrabajoSube\MedioBoleto;
use TrabajoSube\Tarjeta;

class FranquiciaTest extends TestCase
{
    public function testFranquiciaCompletaPuedePagarBoleto()
    {
        $tiempoFalso = new TiempoFalso();
        $colectivo = new Colectivo(145);
        $tarjeta = new FranquiciaCompleta();
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertEquals($boleto->getSaldoRestante(), $saldoAntesDePagar); // La franquicia completa siempre puede pagar el boleto
    }

    public function testMedioBoletoCalculaCostoCorrecto()
    {
        $tiempoFalso = new TiempoFalso();
        $tarjeta = new MedioBoleto(100, $tiempoFalso);
        $costoNormal = 100; // Supongamos que el costo normal del boleto es 100
        $costoEsperado = $costoNormal / 2;
        $this->assertEquals($costoEsperado, $tarjeta->calcularCostoBoleto($costoNormal));
    }
}
?>
