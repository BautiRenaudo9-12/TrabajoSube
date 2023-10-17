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

    public function testFranquiciaCompletaNoMasDeDosViajesPorDia(){
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $colectivo = new Colectivo(145);
        $tarjeta = new FranquiciaCompleta(185, $tiempoFalso);

        // Primer viaje del dia gratiuto
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(1, $tarjeta->getCantViajesDia());

        // Segundo viaje del dia gratuito
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(2, $tarjeta->getCantViajesDia());

        // Terecer viaje del dia pago
        $viajesGratuitosAntes = $tarjeta->getCantViajesDia();
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertTrue(3 > $tarjeta->getCantViajesDia());
    }

    public function testFranquiciaCompletaPrecioNormalDespuesDeDosViajes()
    {
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $colectivo = new Colectivo(145);
        $tarjeta = new FranquiciaCompleta(185, $tiempoFalso);

        // Boleto gratuito 1
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals($boleto->getSaldoRestante(), $saldoAntesDePagar);

        // Boleto gratuito 2
        $saldoAntesDePagar = $tarjeta->getSaldo(); 
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals($boleto->getSaldoRestante(), $saldoAntesDePagar);

        // Boleto normal
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals('completa', $tarjeta->getTipoTarjeta());
        $this->assertEquals(0, $boleto->getSaldoRestante());
    }

    public function testMedioBoletoCalculaCostoCorrecto()
    {
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $tarjeta = new MedioBoleto(100, $tiempoFalso);
        $costoNormal = 100; // Supongamos que el costo normal del boleto es 100
        $costoEsperado = $costoNormal / 2;
        $this->assertEquals($costoEsperado, $tarjeta->calcularCostoBoleto($costoNormal));
    }

    public function testViajeDentroDeFranjaHoraria()
    {
        // Medio Boleto
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));

        $tarjeta = new MedioBoleto(100, $tiempoFalso);
        $colectivo = new Colectivo(145);

        $this->assertTrue($tarjeta->esFranjaHorariaValida($tiempoFalso));
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertInstanceOf(Boleto::class, $boleto);

        // Franquicias Completas
        $tarjeta = new FranquiciaCompleta(100, $tiempoFalso);
        $this->assertTrue($tarjeta->esFranjaHorariaValida($tiempoFalso));

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertInstanceOf(Boleto::class, $boleto);
    }

    public function testViajeFueraDeFranjaHoraria()
    {
        // Medio Boleto
        $tiempoFalso = new TiempoFalso(mktime(0, 0, 0, 10, 17, 2023));

        $tarjeta = new MedioBoleto(100, $tiempoFalso);
        $colectivo = new Colectivo(145);

        $this->assertFalse($tarjeta->esFranjaHorariaValida($tiempoFalso));
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertFalse($boleto);

        // Franquicias Completas
        $tarjeta = new FranquiciaCompleta(100, $tiempoFalso);
        $this->assertFalse($tarjeta->esFranjaHorariaValida($tiempoFalso));

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertFalse($boleto);
    }
}
?>
