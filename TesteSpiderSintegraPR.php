<?php

//Definindo o caminho
require_once 'SpiderSintegraPR.php';

//Criando uma instância do spider
$spider = new SpiderSintegraPR();


//Exibe a imagem
print_r('Digite o valor do CNPJ a ser consultado ( somente numeros ) : ');
$cnpj = readline();


//Buscando informações pelo CNPJ fornecido
$resultado = $spider->buscarInformacoes($cnpj);

//Exibindo informações e formatação de saída
echo "Resultado para o CNPJ $cnpj:\n";
print_r($resultado);
echo "\n";