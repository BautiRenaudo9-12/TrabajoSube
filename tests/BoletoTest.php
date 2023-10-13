<?php
namespace TrabajoSube\Tests;

use PHPUnit\Framework\TestCase;
use TrabajoSube\Boleto;
use TrabajoSube\Colectivo;
use TrabajoSube\FranquiciaCompleta;
use TrabajoSube\MedioBoleto;
use TrabajoSube\Tarjeta;

class BoletoTest extends TestCase
{
    public function testTarifaCorrecta()
    {
        $boleto = new Boleto(1000, 500,'normal',145,1,0,0);
        $this->assertEquals(500, $boleto->getTarifa());
    }

    public function testSaldoRestanteCorrecto()
    {
        $tarjetaNormal = new Tarjeta(100);
        $tarjetaMedioBoleto = new MedioBoleto(100);
        $tarjetaCompleta = new FranquiciaCompleta(100);

        $colectivo = new Colectivo(145);

        $boletoNormal = $colectivo->pagarCon($tarjetaNormal);

        $boletoMedio = $colectivo->pagarCon($tarjetaMedioBoleto);

        $boletoCompleta = $colectivo->pagarCon($tarjetaCompleta);

        $dataTarjetaNormal = $boletoNormal->getDataTarjeta();
        $this->assertEquals(-85, $dataTarjetaNormal['saldoRestante']);

        $dataTarjetaMedioBoleto = $boletoMedio->getDataTarjeta();
        $this->assertEquals(7.5, $dataTarjetaMedioBoleto['saldoRestante']);

        $dataTarjetaCompleta = $boletoCompleta->getDataTarjeta();
        $this->assertEquals(100, $dataTarjetaCompleta['saldoRestante']);
    }

    public function testTiposBoleto()
    {
        $tarjetaNormal = new Tarjeta(100);
        $tarjetaMedioBoleto = new MedioBoleto(100);
        $tarjetaCompleta = new FranquiciaCompleta(100);

        $colectivo = new Colectivo(145);

        $boletoNormal = $colectivo->pagarCon($tarjetaNormal);

        $boletoMedio = $colectivo->pagarCon($tarjetaMedioBoleto);

        $boletoCompleta = $colectivo->pagarCon($tarjetaCompleta);

        $dataTarjetaNormal = $boletoNormal->getDataTarjeta();
        $this->assertEquals('normal', $dataTarjetaNormal['tipoTarjeta']);

        $dataTarjetaMedioBoleto = $boletoMedio->getDataTarjeta();
        $this->assertEquals('parcial', $dataTarjetaMedioBoleto['tipoTarjeta']);

        $dataTarjetaCompleta = $boletoCompleta->getDataTarjeta();
        $this->assertEquals('completa', $dataTarjetaCompleta['tipoTarjeta']);
    }
    // public function testFecha()
    // {
    //     $tarjetaNormal = new Tarjeta(100);
    //     $colectivo = new Colectivo(145);

    //     $boletoNormal = $colectivo->pagarCon($tarjetaNormal);
    //     $dataTarjeta = $boletoNormal->getDataTarjeta();
        
    //     echo $dataTarjeta['fecha'];
    // }
}
?>