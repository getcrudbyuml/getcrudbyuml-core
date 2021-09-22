<?php


namespace GetCrudByUML\controller;

use GetCrudByUML\model\Software;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Atributo;
use GetCrudByUML\gerador\sqlGerador\DBGerador;

/**
 * 
 * @author jefponte
 *
 * Vai receber um software e retornar um código 
 *
 *
 */
class MainAPIController{
    public static function main(){
        header('Content-type: application/json');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: *");
        
        if(!isset($_REQUEST['api'])){
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
        $obj = new MainAPIController();
        $obj->route($_REQUEST['api']);
    }
    public function route($strPath)
    {   
        if($strPath == 'api/software' || $strPath == 'api/software/'){
            $this->software();
        }
    }

    public function software(){
                
        $body = file_get_contents('php://input');
        $jsonBody = json_decode($body);
        $software = new Software();
        
        if(!isset($jsonBody->name) && !isset($jsonBody->nome)){
            echo 'Name is missing';
        }
        
        if(isset($jsonBody->nome)){
            $software->setNome($jsonBody->nome);
        }
        if(isset($jsonBody->name)){
            $software->setNome($jsonBody->name);
        }
        
        if(!isset($jsonBody->objects) && !isset($jsonBody->objetos)){
            echo 'Objects is missing';
        }
        if(isset($jsonBody->objects)){
            $objetos = $jsonBody->objects;
        }
        if(isset($jsonBody->objetos)){
            $objetos = $jsonBody->objetos;
        }
        
        foreach ($objetos as $objeto){
            
            $newObjeto = $this->jsonToObjeto($objeto);
            $software->addObjeto($newObjeto);
            
        }
        
        $strCode = array("files" => DBGerador::main($software));
        echo json_encode($strCode);
    }

    public function jsonToObjeto($jsonVar) : Objeto {
        $newObjeto = new Objeto();
        if(!isset($jsonVar->atributos) && !isset($jsonVar->attributes)){
            return $newObjeto;
        }
        $atributos = array(); 
        if(isset($jsonVar->atributos)){
            $atributos = $jsonVar->atributos;
        }
        if(isset($jsonVar->attributes)){
            $atributos = $jsonVar->attributes;
        }
        foreach($atributos as $atributo){
            $newAtributo = new Atributo();
            $indice = "";
            if(isset($atributo->indice)){
                $indice = $atributo->indice;
            } 
            if(isset($atributo->index)){
                $indice = $atributo->index;
            }
            
            $name = "";
            if(isset($atributo->name)){
                $name = $atributo->name;
            }
            if(isset($atributo->nome)){
                $name = $atributo->nome;
            }
            $type = "";
            if(isset($atributo->type)){
                $type = $atributo->type;
            }
            if(isset($atributo->tipo)){
                $type = $atributo->tipo;
            }
            
            $newAtributo->setIndice($indice);
            $newAtributo->setNome($name);
            $newAtributo->setTipo($type);            
            $newObjeto->addAtributo($newAtributo);

        }
        return $newObjeto;
    }

}
?>
