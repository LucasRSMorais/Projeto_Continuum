<?php

// Ponto de entrada usado para confirmar que o sistema de logs está ativo.
require_once "config/logger.php";

// Registra a inicialização do backend e exibe uma mensagem simples para teste.
acessadolog_Continuum("O sistema esta sendo iniciado" , "LOG");

echo "Log esta em funcionamento ";

