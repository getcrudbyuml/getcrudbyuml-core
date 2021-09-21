<?php 

namespace GetCrudByUML\gerador\python;

use GetCrudByUML\model\Software;

class MainPyGerador{
    
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software){
        $gerador = new MainPyGerador($software);
        return $gerador->gerarCodigo();
    }
    public function __construct(Software $software){
        $this->software = $software;
    }
    
    public function gerarCodigo(){
        $this->geraMain();
        return $this->listaDeArquivos;
    }
    
    
    
    public function geraMain(){
        $codigo = '';
        foreach($this->software->getObjetos() as $objeto){
            $codigo  .= '
from '.$objeto->getNomeSnakeCase().' import '.ucfirst($objeto->getNome()).'';
            
        }
        
        $codigo .= '
def main():
    while(True):
        comando = input("';
        $i = 0;
        foreach($this->software->getObjetos() as $objeto){ 
            $codigo .= $i.' - '.$objeto->getNome().''.'\\\\n';
            $i++;
        }
        $codigo .= '")';
        $codigo .= '';
        $i = 0;
        $arrayObjetoStr = array();
        foreach($this->software->getObjetos() as $objeto){
            if($i == 0){
                $arrayObjetoStr[] = '
        if comando == "'.$i.'":
            '.ucfirst($objeto->getNome()).'.main()';
            }else{
                $arrayObjetoStr[] = '
        elif comando == "'.$i.'":
            '.ucfirst($objeto->getNome()).'.main()';
            }
            
            $i++;
            
        }
        $codigo .= implode("", $arrayObjetoStr);
        $codigo .= '
        else:
            print ("Code Not Found?")

if __name__ == "__main__":
    main()  
';
        $caminho = 'main.py';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $codigo;
        
    }
}






?>