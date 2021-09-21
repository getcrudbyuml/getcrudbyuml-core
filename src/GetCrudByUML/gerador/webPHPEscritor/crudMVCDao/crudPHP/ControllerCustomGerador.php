<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ControllerCustomGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    
    public static function main(Software $software){
        $gerador = new ControllerCustomGerador($software);
        return $gerador->gerarCodigo();
    }
    public function __construct(Software $software){
        $this->software = $software;
    }

    public function gerarCodigo(){
        foreach ($this->software->getObjetos() as $objeto){
            $this->geraControllers($objeto);
        }
        return $this->listaDeArquivos;
        
    }
    
    private function construct(Objeto $objeto){
        $codigo = '

	public function __construct(){
		$this->dao = new ' . ucfirst($objeto->getNome()) . 'CustomDAO();
		$this->view = new ' . ucfirst($objeto->getNome()). 'CustomView();
	}

';
        return $codigo;
    }
    private function geraControllers(Objeto $objeto)
    {
        $codigo = '<?php
            
/**
 * Customize o controller do objeto ' . $objeto->getNome() . ' aqui 
 * @author Jefferson Uchôa Ponte <jefponte@gmail.com>
 */

namespace '.$this->software->getNome().'\\\\custom\\\\controller;
use '.$this->software->getNome().'\\\\controller\\\\'.ucfirst($objeto->getNome()).'Controller;
use '.$this->software->getNome().'\\\\custom\\\\dao\\\\'.ucfirst($objeto->getNome()).'CustomDAO;
use '.$this->software->getNome().'\\\\custom\\\\view\\\\'.ucfirst($objeto->getNome()).'CustomView;

class ' . ucfirst($objeto->getNome()) . 'CustomController  extends ' . ucfirst($objeto->getNome()) . 'Controller {
    ';
        
        $codigo .= $this->construct($objeto);
        $codigo .= '
	        
}
?>';
        $caminho = ucfirst($objeto->getNome()).'CustomController.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    
}


?>