<?php



namespace GetCrudByUML\gerador\sqlGerador;
use GetCrudByUML\model\Software;
use GetCrudByUML\model\Objeto;





class DMLGenerator
{
    
    private $listaDeArquivos;
    
    private $software;
    
    
    public static function main(Software $software)
    {
        $matrix = new DMLGenerator($software);
        return $matrix->generate();
    }
    
    public function __construct(Software $software)
    {
        $this->listaDeArquivos = array();
        $this->software = $software;
    }
    
    public function getListaDeArquivos()
    {
        return $this->listaDeArquivos;
    }
    
    public function generate()
    {
        $codigo = $this->select();
        $this->listaDeArquivos['dml'] = $codigo;
        return $this->listaDeArquivos;
        
    }
    
    public function select(){
        $codigo = "";
        $sqlGerador = new SQLGerador($this->software);
        
        foreach($this->software->getObjetos() as $objeto){

            $codigo .= $sqlGerador->getSQLSelect($objeto);
            $codigo .= ' LIMIT 100;';
            $codigo .= '
';
            $codigo .= $this->insert($objeto);
            $codigo .= '

';
            $codigo .= $this->update($objeto);
            $codigo .= '
                
';
            $codigo .= $this->delete($objeto);
            $codigo .= '
                
';
            
            
        }
        return $codigo;
        
        
    }
    private function insert(Objeto $objeto)
    {
        $objetos1N = array();
        foreach ($this->software->getObjetos() as $objeto2){
            foreach($objeto2->getAtributos() as $atributo){
                if($atributo->isArray1N()){
                    if($atributo->getTipoDeArray() == $objeto->getNome()){
                        $objetos1N[] = $objeto2;
                    }
                    
                }
            }
        }
        $codigo = '';
        
        $varPrimary = array();
        foreach($objetos1N as $objeto2){
            
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $varPrimary[] = '
        $'.lcfirst($attr->getNome()).ucfirst($objeto2->getNome()).' = $'.lcfirst($objeto2->getNome()).'->get'.ucfirst($attr->getNome()).'();';
                }
            }
        }
        
        
        $codigo .= '
INSERT INTO ' . $objeto->getNomeSnakeCase() . '(';
        $listaAtributos = array();
        $listaAtributosVar = array();
        foreach ($objeto->getAtributos() as $atributo)
        {
            if($atributo->isPrimary()){
                continue;
            }
            if($atributo->tipoListado()){
                $listaAtributos[] = $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = ':' .lcfirst($atributo->getNome());
                
            }else if($atributo->isObjeto()){
                $listaAtributos[] = 'id_' . $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = ':' .lcfirst($atributo->getNome());
                
            }else{
                continue;
            }
        }
        foreach($objetos1N as $objeto2){
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $listaAtributos[] = $attr->getNomeSnakeCase().'_'.$objeto2->getNomeSnakeCase();
                    $listaAtributosVar[] = ':' .lcfirst($attr->getNome()).ucfirst($objeto2->getNome());
                }
            }
        }
        
        
        $codigo .= implode(", ", $listaAtributos);
        $codigo .= ') VALUES (';
        $codigo .= implode(", ", $listaAtributosVar);
        $codigo .= ');';
        
      
        $codigo .= implode('', $varPrimary);
        
        
        
        return $codigo;
        
    }
    private function update(Objeto $objeto){
        $codigo = '';
        $atributosComuns = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            }
        }
        $atributoPrimary = $objeto->getAtributos()[0];
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isPrimary()) {
                $atributoPrimary = $atributo;
                break;
            }
        }
        
        
        $codigo = 'UPDATE ' . $objeto->getNomeSnakeCase() . '
                SET
                ';
        $listaAtributo = array();
        foreach ($atributosComuns as $atributo) {
            if ($atributo->isPrimary()) {
                continue;
            }
            if (substr($atributo->getTipo(), 0, 6) == 'Array ') {
                continue;
            }
            $listaAtributo[] = $atributo;
        }
        $i = 0;
        foreach ($listaAtributo as $atributo) {
            $i ++;
            $codigo .= $atributo->getNomeSnakeCase() . ' = :' . $atributo->getNome();
            if ($i != count($listaAtributo)) {
                $codigo .= ',
                ';
            }
        }
        if ($atributoPrimary != null) {
            $codigo .= '
                WHERE ' . $objeto->getNomeSnakeCase() . '.' . $atributoPrimary->getNomeSnakeCase() . ' = :' . lcfirst($atributoPrimary->getNome()) . ';';
        }
        return $codigo;
    }
    private function delete(Objeto $objeto) : string {
        $atributoPrimary = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isPrimary()) {
                $atributoPrimary = $atributo;
                break;
            }
        }
        if ($atributoPrimary == null) {
            $atributoPrimary = $objeto->getAtributos()[0];
        }
        $codigo = 'DELETE FROM ' . $objeto->getNomeSnakeCase() . ' WHERE ' . $atributoPrimary->getNomeSnakeCase() . ' = :' . $atributoPrimary->getNomeSnakeCase() . ';';
        return $codigo;
    }
}

?>