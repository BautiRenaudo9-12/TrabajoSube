<?php
use PHPUnit\Framework\TestCase;
use TrabajoSube\Colectivo;
use TrabajoSube\FranquiciaCompleta;
use TrabajoSube\MedioBoleto;

class FranquiciaTest extends TestCase
{
    public function testFranquiciaCompletaPuedePagarBoleto()
    {
        $colectivo = new Colectivo;
        $tarjeta = new FranquiciaCompleta();
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta);

        $this->assertEquals($boleto->getSaldoRestante(), $saldoAntesDePagar); // La franquicia completa siempre puede pagar el boleto
    }

    public function testMedioBoletoCalculaCostoCorrecto()
    {
        $tarjeta = new MedioBoleto();
        $costoNormal = 100; // Supongamos que el costo normal del boleto es 100
        $costoEsperado = $costoNormal / 2;
        $this->assertEquals($costoEsperado, $tarjeta->calcularCostoBoleto($costoNormal));
    }
}
?>
