<?php 


function registrarLog ($pdo , $usuario_id , $acao , $descricao){


$sql = "INSERT INTO logs (usuario_id , acao , descricao)VALUES (:usuario_id, :acao, :descricao)";

$stmt = $pdo -> prepare($sql);

$stmt->execute(

[

':usuario_id' => $usuario_id,
':acao'=> $acao,
':descricao'=> $descricao


]);




}



