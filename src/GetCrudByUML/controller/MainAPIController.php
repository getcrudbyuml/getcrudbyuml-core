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
        if(!isset($_REQUEST['api'])){
            return;
        }
        header('Content-type: application/json');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: *");
        
        $body = file_get_contents('php://input');
        $jsonBody = json_decode($body);
        
        
        $software = new Software();
        $software->setNome($jsonBody->nome);
        foreach ($jsonBody->objetos as $objeto){
            $newObjeto = new Objeto();
            $newObjeto->setNome($objeto->nome);
            foreach($objeto->atributos as $atributo){
                $newAtributo = new Atributo();
                if(isset($atributo->indice)){
                    $newAtributo->setIndice($atributo->indice);    
                }
                $newAtributo->setNome($atributo->nome);
                $newAtributo->setTipo($atributo->tipo);
                $newObjeto->addAtributo($newAtributo);
                
            }
            $software->addObjeto($newObjeto);
            
        }
        
        $strCode = array("files" => DBGerador::main($software));
        echo json_encode($strCode);
        
        
        
    }

}
?>
