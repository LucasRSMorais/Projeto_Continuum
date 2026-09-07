<?php 

// Grava mensagens do backend em um arquivo com data, hora e categoria.
function acessadolog_Continuum(String $a , String $tipo = "LOG") : void{

// Mantém os logs na pasta de armazenamento do servidor.
$pasta = __DIR__ . "/../var/log/sistema.log";

// Monta o registro temporal que será salvo no arquivo.
$data_do_dia = date("d/m/Y");
$horario_do_processo = date("H:i:s");
$processo = "[$data_do_dia $horario_do_processo]  [$tipo] $a".PHP_EOL;

// Acrescenta o novo evento sem apagar os registros anteriores.
file_put_contents($pasta , $processo , FILE_APPEND);


}




