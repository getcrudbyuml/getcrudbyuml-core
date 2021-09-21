<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Software;

class StyleCssGerador
{

    private $software;

    private $listaDeArquivos;


    public static function main(Software $software)
    {
        $gerador = new StyleCssGerador($software);
        return $gerador->geraCodigo();
    }

    public function __construct(Software $software)
    {
        $this->software = $software;
        
    }

    /**
     * Selecione uma linguagem
     *
     * @param int $linguagem
     */
    public function geraCodigo()
    {
        $this->geraStyle();
        return $this->listaDeArquivos;
        
        
    }
    public function geraStyle()
    {
        $codigo = "/*Digite aqui seu arquivo css*/";
        $caminho = 'style.css';
        $this->listaDeArquivos[$caminho] = $codigo;
        
    }
    
}

?>