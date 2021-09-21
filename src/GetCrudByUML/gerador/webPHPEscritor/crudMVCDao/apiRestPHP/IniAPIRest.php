<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\apiRestPHP;

use GetCrudByUML\model\Software;

class IniAPIRest
{

    private $software;
    
    private $listaDeArquivos;
    
    
    public static function main(Software $software){
        $gerador = new IniAPIRest($software);
        return $gerador->gerarCodigo();
    }
    
    
    public function __construct(Software $software)
    {
        $this->software = $software;
        $this->listaDeArquivos = array();
    }
    public function gerarCodigo(){
        $this->geraIniAPI();
        return $this->listaDeArquivos;
        
    }
    
    public function geraIniAPI(){
        
        if (! count($this->software->getObjetos())) {
            return;
        }
        $codigo  = '

;Config ini api rest. 

enable = true
user = usuario
password = senha@12
';
        
        
        $caminho = $this->software->getNomeSnakeCase() .'_api_rest.ini';
        $this->listaDeArquivos[$caminho] = $codigo;
    }

}

?>