<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;


class ViewCustomGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    public static function main(Software $software){
        $gerador = new ViewCustomGerador($software);
        return $gerador->gerarCodigo();
        
    }
    
    public function __construct(Software $software){
        $this->software = $software;
    }
    /**
     * Selecione uma linguagem
     * @param int $linguagem
     */
    public function gerarCodigo(){
        foreach($this->software->getObjetos() as $objeto){
            $this->geraViews($objeto);
        }
        
        return $this->listaDeArquivos;
        
    }

    
    private function geraViews(Objeto $objeto)
    {
        $codigo = '';

        $codigo = '<?php
            
/**
 * Classe de visao para ' . $objeto->getNome() . '
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 *
 */

namespace '.$this->software->getNome().'\\\\custom\\\\view;
use '.$this->software->getNome().'\\\\view\\\\'.ucfirst($objeto->getNome()).'View;


class ' . $objeto->getNome() . 'CustomView extends ' . $objeto->getNome() . 'View {

    ////////Digite seu código customizado aqui.

';
        
        $codigo .= '
}';

        
        $caminho = ucfirst($objeto->getNome()).'CustomView.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
   
    
}


?>