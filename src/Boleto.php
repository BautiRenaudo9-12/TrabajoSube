<?php
namespace TrabajoSube;

use Exception;

class Boleto
{
    private $dataTarjeta;
    public function __construct($saldoInicial, $tarifa, $tipoTarjeta='normal', $lineaColectivo, $idTarjeta, $fecha=0, $abonoNegativo) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');

        $fecha = time();

        $this->dataTarjeta = [
            'saldoInicial' => $saldoInicial,
            'saldoRestante' => $saldoInicial - $tarifa,
            'tarifa' => $tarifa,
            'tipoTarjeta' => $tipoTarjeta,
            'lineaColectivo' => $lineaColectivo,
            'idTarjeta' => $idTarjeta,
            'fecha' => date('d/m/Y H:i:s', $fecha),
            'abonoNegativo' => 'Abona saldo ' . $abonoNegativo,
        ];
    }

    public function getDataTarjeta(){
        return $this->dataTarjeta;
    }
    public function getTarifa()
    {
        return $this->dataTarjeta['tarifa'];
    }

    public function getSaldoRestante()
    {
        return $this->dataTarjeta['saldoRestante'];
    }
    public function getTipoTarjeta()
    {
        return $this->dataTarjeta['tipoTarjeta'];
    }
    public function getFecha()
    {
        return $this->dataTarjeta['fecha'];
    }
}
?>