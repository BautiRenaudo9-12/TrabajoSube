<?php

namespace TrabajoSube;

use PHPUnit\Framework\TestCase;
use TrabajoSube\Tarjeta;

class TarjetaTest extends TestCase
{
    public function testSaldoInicialEsCorrecto()
    {
        $tarjeta = new Tarjeta(100);
        $this->assertEquals(100, $tarjeta->getSaldo());
    }

    public function testCargarSaldoValido()
    {
        $tarjeta = new Tarjeta(0);
        $tarjeta->cargarSaldo(500);
        $this->assertEquals(500, $tarjeta->getSaldo());
    }

    public function testCargarSaldoInvalido()
    {
        $tarjeta = new Tarjeta(0);
        $tarjeta->cargarSaldo(7000);
        $this->assertEquals(0, $tarjeta->getSaldo());
    }

    public function testDescontarSaldo()
    {
        $tarjeta = new Tarjeta(1000);
        $tarjeta->descontarSaldo(200);
        $this->assertEquals(800, $tarjeta->getSaldo());
    }

    public function testVerifyMontoValido()
    {
        $tarjeta = new Tarjeta(0);
        $result = $tarjeta->verifyMonto(500);
        $this->assertTrue($result);
    }

    public function testVerifyMontoInvalido()
    {
        $tarjeta = new Tarjeta(0);
        $result = $tarjeta->verifyMonto(7000);
        $this->assertFalse($result);
    }

    public function testIntervaloMedioBoleto()
    {
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023)); // Crear una instancia de TiempoFalso
        $tarjeta = new MedioBoleto(1000, $tiempoFalso); // Inyectar TiempoFalso en MedioBoleto
        $colectivo = new Colectivo(145, false);

        // Realizar el primer viaje, que siempre debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(907.5, $tarjeta->getSaldo());
        $this->assertEquals(1, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time(), $tarjeta->getUltimoViaje());

        // Avanzar el tiempo en 4 minutos (240 segundos)
        $tiempoFalso->avanzarSegundos(240);

        // Intentar realizar el segundo viaje en menos de 5 minutos
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertFalse($boleto);
        $this->assertEquals(907.5, $tarjeta->getSaldo());
        $this->assertEquals(1, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time()-240, $tarjeta->getUltimoViaje());

        // Avanzar el tiempo en 1 minuto (60 segundos) para permitir el siguiente viaje
        $tiempoFalso->avanzarSegundos(60);

        // Realizar el segundo viaje después de mas de 5 minutos, debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(815, $tarjeta->getSaldo());
        $this->assertEquals(2, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time(), $tarjeta->getUltimoViaje());

        $tiempoFalso->avanzarSegundos(300);

        // Realizar el tercer viaje después de 5 minutos, debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(722.5, $tarjeta->getSaldo());
        $this->assertEquals(3, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time(), $tarjeta->getUltimoViaje());
        $this->assertInstanceOf(Boleto::class, $boleto);

        $tiempoFalso->avanzarSegundos(300);

        $this->assertEquals($tiempoFalso->time(), $tiempoFalso->time());

        // Realizar el cuarto viaje, que debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(630, $tarjeta->getSaldo());
        $this->assertEquals(4, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time(), $tarjeta->getUltimoViaje());
        $this->assertInstanceOf(Boleto::class, $boleto);

        $tiempoFalso->avanzarSegundos(300);

        //Realizar el quinto viaje, que ya debería tener su valor normal
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(445, $tarjeta->getSaldo());
        $this->assertEquals(5, $tarjeta->getCantViajesDia());
        $this->assertEquals($tiempoFalso->time(), $tarjeta->getUltimoViaje());
        $this->assertInstanceOf(Boleto::class, $boleto);
    }

    public function testSaldoPendienteAcreditacionNormal()
    {
        $tarjeta = new Tarjeta(6500);
        $colectivo = new Colectivo(145, false);
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));

        $tarjeta->cargarSaldo(150);
        $this->assertEquals(50, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(6465, $tarjeta->getSaldo());

        $tarjeta->cargarSaldo(600);
        $this->assertEquals(6600, $tarjeta->getSaldo());
        $this->assertEquals(465, $tarjeta->getMontoPendiente());

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(280, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());
    }

    public function testSaldoPendienteAcreditacionParcial()
    {
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $tarjeta = new MedioBoleto(6500, $tiempoFalso);
        $colectivo = new Colectivo(145, false);

        $tarjeta->cargarSaldo(150);
        $this->assertEquals(50, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(6557.5, $tarjeta->getSaldo());

        $tarjeta->cargarSaldo(600);
        $this->assertEquals(6600, $tarjeta->getSaldo());
        $this->assertEquals(557.5, $tarjeta->getMontoPendiente());

        $tiempoFalso->avanzarSegundos(301);

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(465, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());
    }

    public function testSaldoPendienteAcreditacionCompleta()
    {
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $tarjeta = new FranquiciaCompleta(6500, $tiempoFalso);
        $colectivo = new Colectivo(145, false);

        $tarjeta->cargarSaldo(150);
        $this->assertEquals(50, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(50, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());

        $tarjeta->cargarSaldo(600);
        $this->assertEquals(6600, $tarjeta->getSaldo());
        $this->assertEquals(650, $tarjeta->getMontoPendiente());

        $tiempoFalso->avanzarSegundos(301);

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(650, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());

        $tiempoFalso->avanzarSegundos(301);

        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(465, $tarjeta->getMontoPendiente());
        $this->assertEquals(6600, $tarjeta->getSaldo());
    }

    public function testBoletoUsoFrecuente(){
        $tiempoFalso = new TiempoFalso(mktime(10, 0, 0, 10, 17, 2023));
        $tarjeta = new Tarjeta(6600, $tiempoFalso);
        $colectivo = new Colectivo(145, false);

        $boleto = false;
        
        $tarjeta->cargarSaldo(4000);
        $tarjeta->cargarSaldo(4000);

        for ($i = 1; $i <= 81; $i++) {
            $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
            if($i <= 29){
                $this->assertEquals(1, $tarjeta->calcularCostoBoletoNormal($tiempoFalso));
            }
            elseif($i >= 30 && $i <= 79 ){
                $this->assertEquals(0.8, $tarjeta->calcularCostoBoletoNormal($tiempoFalso));
            }
             else{
                 $this->assertEquals(0.75, $tarjeta->calcularCostoBoletoNormal($tiempoFalso));
             }
        }
    }
}
?>