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
        $tiempoFalso = new TiempoFalso(); // Crear una instancia de TiempoFalso
        $tarjeta = new MedioBoleto(1000, $tiempoFalso); // Inyectar TiempoFalso en MedioBoleto
        $colectivo = new Colectivo(145);

        // Realizar el primer viaje, que siempre debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(907.5, $tarjeta->getSaldo());
        $this->assertEquals(1, $tarjeta->getCantViajesDia());
        $this->assertEquals(0, $tarjeta->getUltimoViaje());

        // Avanzar el tiempo en 4 minutos (240 segundos)
        $tiempoFalso->avanzarSegundos(240);

        // Intentar realizar el segundo viaje en menos de 5 minutos
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertFalse($boleto);
        $this->assertEquals(907.5, $tarjeta->getSaldo());
        $this->assertEquals(1, $tarjeta->getCantViajesDia());
        $this->assertEquals(0, $tarjeta->getUltimoViaje());

        // Avanzar el tiempo en 1 minuto (60 segundos) para permitir el siguiente viaje
        $tiempoFalso->avanzarSegundos(60);

        // Realizar el segundo viaje después de mas de 5 minutos, debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertInstanceOf(Boleto::class, $boleto);
        $this->assertEquals(815, $tarjeta->getSaldo());
        $this->assertEquals(2, $tarjeta->getCantViajesDia());
        $this->assertEquals(300, $tarjeta->getUltimoViaje());

        $tiempoFalso->avanzarSegundos(300);

        $this->assertEquals(600, $tiempoFalso->time());

        // Realizar el tercer viaje después de 5 minutos, debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(722.5, $tarjeta->getSaldo());
        $this->assertEquals(3, $tarjeta->getCantViajesDia());
        $this->assertEquals(600, $tarjeta->getUltimoViaje());
        $this->assertInstanceOf(Boleto::class, $boleto);

        $tiempoFalso->avanzarSegundos(300);

        $this->assertEquals(900, $tiempoFalso->time());

        // Realizar el cuarto viaje, que debería ser exitoso
        $boleto = $colectivo->pagarCon($tarjeta, $tiempoFalso);
        $this->assertEquals(630, $tarjeta->getSaldo());
        $this->assertEquals(4, $tarjeta->getCantViajesDia());
        $this->assertEquals(900, $tarjeta->getUltimoViaje());
        $this->assertInstanceOf(Boleto::class, $boleto);

        //Realizar el cuarto viaje, que ya debería tener su valor normal

    }
}


?>