<?php 
function acessadolog_Continuum(String $a , String $tipo = "LOG") : void{



$pasta = __DIR__ . "/../var/log/sistema.log";

$data_do_dia = date("d/m/Y");
$horario_do_processo = date("H:i:s");
$processo = "[$data_do_dia $horario_do_processo]  [$tipo] $a".PHP_EOL;
file_put_contents($pasta , $processo , FILE_APPEND);


}




