<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\apiRestPHP;

use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ControllerRestGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software){
        $gerador = new ControllerRestGerador($software);
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
		$this->dao = new ' . ucfirst($objeto->getNome()) . 'DAO();

	}

';
        return $codigo;
    }
 
    public function delete(Objeto $objeto):string{
        
        $atributoPrimary = $objeto->getAtributos()[0];
        foreach ($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
        }
        $codigo = '

    public function delete()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'DELETE\') {
            return;
        }
        
        if(!isset($_REQUEST[\'api\'])){
            return;
        }
        $url = explode("/", $_REQUEST[\'api\']);
        if (count($url) == 0 || $url[0] == "") {
            return;
        }
        if ($url[0] != \''.$objeto->getNomeSnakeCase().'\') {
            echo \'error\';
            return;
        }

        if(!isset($url[1])){
            echo \'error\';
            return;
        }
        if($url[1] == \'\'){
            echo \'error\';
            return;
        }
        
        $'.lcfirst($atributoPrimary->getNome()).' = $url[1];



        $selected = new '.ucfirst($objeto->getNome()).'();
        $selected->set'.ucfirst($objeto->getNome()).'($'.lcfirst($atributoPrimary->getNome()).');
        $selected = $this->dao->fillBy'.ucfirst($atributoPrimary->getNome()).'($selected);
        if ($selected == null) {
            return;
        }
        if($this->dao->delete($selected))
        {
            echo "{}";
            return;
        }
        
        echo "Erro.";
        
    }

';
        return $codigo;
        
    }

    public function get(Objeto $objeto):string{
        
        $atributoPrimary = $objeto->getAtributos()[0];
        foreach ($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
        }
        $atributosComuns = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
        }
        $codigo = '';
        $codigo .= '

    public function get()
    {

        if ($_SERVER[\'REQUEST_METHOD\'] != \'GET\') {
            return;
        }

        if(!isset($_REQUEST[\'api\'])){
            return;
        }
        $url = explode("/", $_REQUEST[\'api\']);
        if (count($url) == 0 || $url[0] == "") {
            return;
        }
        if(!isset($url[1])){
            return;
        }
        if ($url[1] != \''.$objeto->getNomeSnakeCase().'\') {
            return;
        }

        if(isset($url[2]) && $url[2] != \'\'){

            $'.lcfirst($atributoPrimary->getNome()).' = $url[2];
            $selected = new '.ucfirst($objeto->getNome()).'();
            $selected->set'.ucfirst($atributoPrimary->getNome()).'($'.lcfirst($atributoPrimary->getNome()).');
            $list = $this->dao->fetchBy'.ucfirst($atributoPrimary->getNome()).'($selected);
            if (count($list) == 0) {
                echo "{}";
                return;
            }
        }else{
            $list = $this->dao->fetch();
        }
        
        $listagem = array();
        foreach ( $list as $linha ) {
			$listagem [] = array (';
        $i = 0;
        foreach ($atributosComuns as $atributo) {
            $i ++;
            $nomeDoAtributoMA = ucfirst($atributo->getNome());
            $codigo .= '
					\'' . $atributo->getNome() . '\' => $linha->get' . $nomeDoAtributoMA . ' ()';
            if ($i != count($objeto->getAtributos())) {
                $codigo .= ', ';
            }
        }
        
        $codigo .= '
            
            
			);
		}
		echo json_encode ( $listagem );
    
		
		
		
		
	}';
        return $codigo;
        
    }
    public function geraMain() : string {
        $codigo = '
            
    public function main($iniApiFile)
    {
            
        //$config = parse_ini_file ( $iniApiFile );
        //$user = $config [\'user\'];
        //$password = $config [\'password\'];
        /*    Descomente se quiser autenticação. 
        if(!isset($_SERVER[\'PHP_AUTH_USER\'])){
            header("WWW-Authenticate: Basic realm=\\\\"Private Area\\\\" ");
            header("HTTP/1.0 401 Unauthorized");
            echo \'{"erro":[{"status":"error","message":"Authentication failed"}]}\';
            return;
        }
        if($_SERVER[\'PHP_AUTH_USER\'] == $user && ($_SERVER[\'PHP_AUTH_PW\'] == $password)){
*/
            header(\'Content-type: application/json\');
            
            $this->get();
            $this->post();
            $this->put();
            $this->delete();
/*
        }else{
            header("WWW-Authenticate: Basic realm=\\\\"Private Area\\\\" ");
            header("HTTP/1.0 401 Unauthorized");
            echo \'{"erro":[{"status":"error","message":"Authentication failed"}]}\';
        }
*/
            
    }';
        return $codigo;
    }
    public function put(Objeto $objeto):string{
        $atributoPrimary = $objeto->getAtributos()[0];
        foreach ($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
        }
        $codigo = '


    public function put()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'PUT\') {
            return;
        }

        if(!isset($_REQUEST[\'api\'])){
            return;
        }
        $url = explode("/", $_REQUEST[\'api\']);
        if (count($url) == 0 || $url[0] == "") {
            return;
        }
        if (!isset($url[1])) {
            return;
        }

        if ($url[1] != \''.$objeto->getNomeSnakeCase().'\') {
            echo \'error\';
            return;
        }

        if(!isset($url[2])){
            echo \'error\';
            return;
        }

        if($url[2] == \'\'){
            echo \'error\';
            return;
        }
        
        $'.lcfirst($atributoPrimary->getNome()).' = $url[2];



        $selected = new '.ucfirst($objeto->getNome()).'();
        $selected->set'.ucfirst($atributoPrimary->getNome()).'($'.lcfirst($atributoPrimary->getNome()).');
        $selected = $this->dao->fillBy'.ucfirst($atributoPrimary->getNome()).'($selected);

        if ($selected == null) {
            return;
        }

        $body = file_get_contents(\'php://input\');
        $jsonBody = json_decode($body, true);
        
        ';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->tipoListado() && !$atributo->isPrimary()){
                $codigo .= '
        if (isset($jsonBody[\''.$atributo->getNomeSnakeCase().'\'])) {
            $selected->set'.ucfirst($atributo->getNome()).'($jsonBody[\''.$atributo->getNomeSnakeCase().'\']);
        }
                    
';
            }
        }
  
        $codigo .= '
        if ($this->dao->update($selected)) 
                {
			echo \'Sucesso\';
		} else {
			echo \'Falha\';
		}
    }

';
        return $codigo;
    }
    public function post(Objeto $objeto):string
    {  
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
            else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
        }
        $codigo = '


    public function post()
    {
        if ($_SERVER[\'REQUEST_METHOD\'] != \'POST\') {
            return;
        }
        
        if(!isset($_REQUEST[\'api\'])){
            return;
        }
        $url = explode("/", $_REQUEST[\'api\']);
        if (count($url) == 0 || $url[0] == "") {
            return;
        }

        if(!isset($url[1])){
            return;
        }
        if ($url[1] != \''.$objeto->getNomeSnakeCase().'\') {
            return;
        }

        $body = file_get_contents(\'php://input\');
        $jsonBody = json_decode($body, true);

        if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $listIsset = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            
            $listIsset[] = 'isset ( $jsonBody [\'' . $atributo->getNome() . '\'] )';

        }
        $codigo .= implode(" && ", $listIsset);
        $i = 0;
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($numDeComunsSemPK > 0 && $i == 0){
                                $codigo .= ' && ';
                            }else if($i > 0){
                                $codigo .= ' && ';
                            }
                            $i++;
                            $codigo .= ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        $codigo .= ')) {
			echo "Incompleto";
			return;
		}

        $adicionado = new '.ucfirst($objeto->getNome()).'();';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->tipoListado() && !$atributo->isPrimary()){
                
                $codigo .= '
        if(isset($jsonBody[\''.$atributo->getNomeSnakeCase().'\'])){
            $adicionado->set'.ucfirst($atributo->getNome()).'($jsonBody[\''.$atributo->getNomeSnakeCase().'\']);
        }
        
';
            }
        }
  
        $codigo .= '
        if ($this->dao->insert($adicionado)) 
                {
			echo \' Sucesso\';
		} else {
			echo \'Falha \';
		}
    }       

';
        return $codigo;
    }
    private function geraControllers(Objeto $objeto)
    {
        $codigo = '';        
        $codigo = '<?php
            
/**
 * Classe feita para manipulação do objeto ' . $objeto->getNome() . 'ApiRestController
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */

namespace '.$this->software->getNome().'\\\\controller;
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($objeto->getNome()).'DAO;
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($objeto->getNome()).';

class ' . ucfirst($objeto->getNome()) . 'ApiRestController {


    protected $dao;';
        $codigo .= $this->construct($objeto);
        
        $codigo .= $this->geraMain();
        $codigo .= $this->get($objeto);
        $codigo .= $this->delete($objeto);
        $codigo .= $this->put($objeto);
        $codigo .= $this->post($objeto);

        
        $codigo .= '
}
?>';
        $caminho = ucfirst($objeto->getNome()).'ApiRestController.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    
}


?>