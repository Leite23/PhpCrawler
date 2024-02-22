<?php

//Definindo e encapsulando dados da classe
class SpiderSintegraPR {

    //Armazenando URL
    private $url = 'http://www.sintegra.fazenda.pr.gov.br';

    //Criando a função para consulta dos dados
    public function buscarInformacoes($cnpj) {

        //Inicia uma sessão CURL
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'cookies');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['application/x-www-form-urlencoded']);

        //Request inicial
        $response = $this->makeRequest($this->url . "/sintegra/");

        //Pega a imagem captcha e salva
        if (!preg_match('/imgSintegra"\)\.src\s*=\s*\'([^\']*?)\'/i', $response, $match)) {
            throw new \Exception ('Não achou a imagem do captcha');
        }
        $imagem = $this->url . $match[1] . "?1." . rand(11111111111111111,99999999999999999);
        $response = $this->makeRequest($imagem);
        file_put_contents('captcha.jpg', $response);

        //Exibe a imagem
        print_r('Digite o valor da imagem: ');
        $captcha = readline();

        //Criando o array para envio da requisição ao site Sintegra
        $postData = array(
            '_method' =>  'POST',
            'data[Sintegra1][CodImage]' => $captcha,
            'data[Sintegra1][Cnpj]' => $cnpj,
            'empresa' => 'Consultar Empresa',
            'data[Sintegra1][Cadicms]' => '',
            'data[Sintegra1][CadicmsProdutor]' => '',
            'data[Sintegra1][CnpjCpfProdutor]' => ''
        );

        //Envia a requisição POST para o site do Sintegra e armazena a resposta
        $response = $this->makeRequest($this->url . "/sintegra/", $postData);
        file_put_contents('urlpesquisa.txt', $response);

        //Verifica se o CAPTCHA foi digitado incorretamente
        if (strpos($response, 'APRESENTADO NA IMAGEM') !== false) {
            return 'Captcha incorreto';
        }

        //Verifica se o CNPJ/IE não foi encontrado
        if (strpos($response, 'Por favor, verifique, corrija e tente novamente') !== false) {
            return 'CNPJ/IE não encontrado ou invalido';
        }

       //Extrai as informações utilizando expressões regulares
        preg_match_all('/<td[^>]*>(.*?)<\/td>\s*<td[^>]*>(.*?)<\/td>/', $response, $matches);
        //Armazena Informações extraidas
        $infoArray = array();
        
        //Monta o array com as informações encontradas
        for ($i = 0; $i < count($matches[1]); $i++) {
            $key = strip_tags($matches[1][$i]);
            $value = strip_tags($matches[2][$i]);
            $infoArray[$key] = $this->fixEncoding($value);
        }
        //Retorna o array com as informações extraídas
        return $infoArray;
    }

    //Recebe os dados a serem enviados na requisição
    private function makeRequest($url, $postData = []) {

        print_r ("URL " . $url . "\n");

        //Configura as opções da requisição para retornar o resultado como uma string
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));

        //Executa a requisição e armazena a resposta
        $response = curl_exec($this->ch);

        //Retorna a resposta da requisição
        return $response;
    }

    //Cria uma função para corrigir a codificação da string
    private function fixEncoding($string) {

        //Verifica se a string não está codificada em UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {

            //Converte para UTF-8 usando iconv
            $string = iconv('ISO-8859-1', 'UTF-8', $string);
        }

        //Retorna a string corrigida
        return $string;
    }
}