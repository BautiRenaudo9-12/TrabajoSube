<?php
namespace TrabajoSube;

use PHPUnit\Framework\TestCase;
use TrabajoSube\Colectivo;
use TrabajoSube\Tarjeta;
use TrabajoSube\Boleto;
use Exception;

class ColectivoTest extends TestCase
{
    public function testPagarConSaldoSuficiente()
    {
        $tiempoFalso = new TiempoFalso();
        $colectivo = new Colectivo(145);
        $tarjeta = new Tarjeta(200);

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(15, $tarjeta->getSaldo());

    }

    public function testGetTarifa()
    {
        $tiempoFalso = new TiempoFalso();
        $colectivo = new Colectivo(145);
        $tarjeta = new Tarjeta(200);

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);

        $this->assertEquals(185, $boleto->getTarifa());
    }

    public function testGetSaldo()
    {
        $tiempoFalso = new TiempoFalso();
        $colectivo = new Colectivo(145);
        $tarjeta = new Tarjeta(200);

        $boleto = $colectivo->pagarCon($tarjeta,$tiempoFalso);

        $this->assertEquals(15, $tarjeta->getSaldo());
    }
}

?>
