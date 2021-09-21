<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ModelGerador
{

    private $software;

    private $listaDeArquivos;

    public static function main(Software $software)
    {
        $gerador = new ModelGerador($software);
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
        $this->listaDeArquivos = array();
        foreach($this->software->getObjetos() as $objeto){
            $this->geraModel($objeto);
        }        
        return $this->listaDeArquivos;
    }
    
    public function geraModel(Objeto $objeto)
    {
        $codigo = '<?php
            
/**
 * Classe feita para manipulação do objeto ' . $objeto->getNome() . '
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */

namespace '.$this->software->getNome().'\\\\model;

class ' . ucfirst($objeto->getNome()) . ' {';
        if (count($objeto->getAtributos()) == 0) {
            $codigo .= '}';
            return $codigo;
        }

        foreach ($objeto->getAtributos() as $atributo) {
            $codigo .= '
	private $' . lcfirst($atributo->getNome()) . ';';
        
        }
        $codigo .= '
    public function __construct(){
';
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                continue;
            } else if ($atributo->isArray()) 
            {
                $codigo .= '
        $this->' . $atributo->getNome() . ' = array();';
            } else if ($atributo->isObjeto()) {
                $codigo .= '
        $this->' . lcfirst($atributo->getNome()) . ' = new ' . ucfirst($atributo->getTipo()) . '();';
            }
        }
        $codigo .= '
    }';
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {

                $codigo .= '
	public function set' . ucfirst($atributo->getNome()) . '($' . lcfirst($atributo->getNome()) . ') {';
                $codigo .= '
		$this->' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($atributo->getNome()) . ';
	}
		    
	public function get' . ucfirst($atributo->getNome()) . '() {
		return $this->' . lcfirst($atributo->getNome()) . ';
	}';
            } else if ($atributo->isArray()) {
                $codigo .= '

	public function set' . ucfirst($atributo->getNome()) . '($' . lcfirst($atributo->getNome()) . ') {';
                $codigo .= '
		$this->' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($atributo->getNome()) . ';
	}
         
    public function add' . ucfirst($atributo->getTipoDeArray()) . '(' . ucfirst($atributo->getTipoDeArray()) . ' $' . lcfirst($atributo->getTipoDeArray()) . '){
        $this->' . lcfirst($atributo->getNome()) . '[] = $' . lcfirst($atributo->getTipoDeArray()) . ';
            
    }
	public function get' . ucfirst($atributo->getNome()) . '() {
		return $this->' . lcfirst($atributo->getNome()) . ';
	}';
            } else if ($atributo->isObjeto()) {

                $codigo .= '
	public function set' . ucfirst($atributo->getNome()) . '(' . ucfirst($atributo->getTipo()) . ' $' . lcfirst($atributo->getTipo()) . ') {';

                $codigo .= '
		$this->' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($atributo->getTipo()) . ';
	}
		    
	public function get' . ucfirst($atributo->getNome()) . '() {
		return $this->' . lcfirst($atributo->getNome()) . ';
	}';
            }
        }
        $codigo .= '
	public function __toString(){
	    return ';
        
        $pedacos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            
            if($atributo->tipoListado() || $atributo->isObjeto()){
                $pedacos[] = '$this->' . lcfirst($atributo->getNome());
                
            }else if($atributo->isArray()){
                $pedacos[] = '\'Lista: \'.implode(", ", $this->' . lcfirst($atributo->getNome()).')';
                
            }
        }
        $codigo .= implode('.\' - \'.', $pedacos);
        $codigo .= ';
	}
                
';

        $codigo .= '
}
?>';
        
        $this->listaDeArquivos[ucfirst($objeto->getNome()).'.php'] = $codigo;
        return $codigo;
    }

}

?>