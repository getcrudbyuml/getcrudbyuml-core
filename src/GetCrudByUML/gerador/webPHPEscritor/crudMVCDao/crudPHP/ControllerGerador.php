<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ControllerGerador{
    private $software;
    private $listaDeArquivos;
    
    public static function main(Software $software){
        $gerador = new ControllerGerador($software);
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
		$this->view = new ' . ucfirst($objeto->getNome()). 'View();
	}

';
        return $codigo;
    }
    public function addAjax(Objeto $objeto){
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMa = ucfirst($objeto->getNome());
        
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
        
        $codigo .= '
            
	public function addAjax() {
            
        if(!isset($_POST[\'enviar_' . $objeto->getNomeSnakeCase() . '\'])){';
            $codigo .= '
            return;    
        }
        
		    
		
		if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $issetList = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $issetList[] = 'isset ( $_FILES [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }else{
                $issetList[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }
            
            
        }
        foreach($objetos1N as $obj){
            $issetList[] = 'isset ( $_POST [\'' . $obj->getNomeSnakeCase() . '\'] )';
        }
        $codigo .= implode(' && ', $issetList);
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
			echo \':incompleto\';
			return;
		}';
        
        $codigo .= '
            
		$' . $nomeDoObjeto . ' = new ' . $nomeDoObjetoMa . ' ();';
        foreach ($atributosComuns as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $codigo .= '
        if($_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'name\'] != null){
    		if(!file_exists(\'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\')) {
    		    mkdir(\'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\', 0777, true);
    		}
    		        
    		if(!move_uploaded_file($_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'tmp_name\'], \'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\'. $_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'name\']))
    		{
    		    echo \':falha\';
    		    return;
    		}
            $' . $nomeDoObjeto . '->set' . ucfirst($atributo->getNome()) . ' ( "uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/".$_FILES [\'' . $atributo->getNomeSnakeCase() . '\'][\'name\'] );
        }';
                
            }
            else
            {
                $codigo .= '
		$' . $nomeDoObjeto . '->set' . ucfirst($atributo->getNome()) . ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
            }
            
        }
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                            $codigo .= '
		$' . $nomeDoObjeto . '->get' .ucfirst($atributoObjeto->getNome()) . '()->set'.ucfirst ($atributo2->getNome()).' ( $_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        $listaParametros = array();
        $listaParametros[] = '$'.lcfirst($objeto->getNome());
        foreach($objetos1N as $obj){
            $codigo .= '
        $'.lcfirst($obj->getNome()).' = new '.ucfirst ($obj->getNome()).'();';
            foreach($obj->getAtributos() as $att){
                if($att->isPrimary()){
                    $codigo .= '
        $'.lcfirst($obj->getNome()).'->set'.ucfirst($att->getNome()).'($_POST[\''.$obj->getNomeSnakeCase().'\']);
';
                }
            }


            $listaParametros[] = '$'.lcfirst ($obj->getNome());
        }
        $codigo .= '
            
		if ($this->dao->insert ( ' . implode(', ', $listaParametros). ' ))
        {
			$id = $this->dao->getConnection()->lastInsertId();
            echo \':sucesso:\'.$id;
            
		} else {
			 echo \':falha\';
		}
	}
            
            
';
        
        return $codigo;
    }
    public function add(Objeto $objeto){
        $codigo = '';
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
        
        $codigo .= '

	public function add() {
            
        if(!isset($_POST[\'enviar_' . $objeto->getNomeSnakeCase() . '\'])){';
        $listaParametros = array();
        foreach($atributosObjetos as $atributoObjeto){
            $codigo .= '
            $'.lcfirst($atributoObjeto->getTipo()).'Dao = new '.ucfirst ($atributoObjeto->getTipo()).'DAO($this->dao->getConnection());
            $list'.ucfirst ($atributoObjeto->getTipo()).' = $'.lcfirst($atributoObjeto->getTipo()).'Dao->fetch();
';
            $listaParametros[] = '$list'.ucfirst ($atributoObjeto->getTipo());
            
            
        }
        foreach($objetos1N as $obj){
            $codigo .= '
            $'.lcfirst($obj->getNome()).'Dao = new '.ucfirst ($obj->getNome()).'DAO($this->dao->getConnection());
            $list'.ucfirst ($obj->getNome()).' = $'.lcfirst($obj->getNome()).'Dao->fetch();            
';
            $listaParametros[] = '$list'.ucfirst ($obj->getNome());
        }
        $codigo .= '
            $this->view->showInsertForm(';
        
        $codigo .= implode(', ', $listaParametros);
        $codigo .= ');';
        $codigo .= '
		    return;
		}
		if (! ( ';
        $i = 0;
        $numDeComunsSemPK = 0;
        $issetLista = array();
        foreach ($atributosComuns as $atributo) {
            $i ++;
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $numDeComunsSemPK++;
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $issetLista[] = 'isset ( $_FILES [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }else{
                $issetLista[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }
            
            
        }
        foreach($objetos1N as $obj){
            $issetLista[] = 'isset ( $_POST [\'' . $obj->getNomeSnakeCase() . '\'] )';
        }
        $codigo .= implode(' && ', $issetLista);
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
			echo \'
                <div class="alert alert-danger" role="alert">
                    Failed to register. Some field must be missing. 
                </div>

                \';
			return;
		}';
        
        
        $codigo .= '
		$' . lcfirst($objeto->getNome()) . ' = new ' . ucfirst($objeto->getNome()) . ' ();';
        
        
        foreach ($atributosComuns as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $codigo .= '

        if($_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'name\'] != null){

            if(!file_exists(\'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\')) {
    		    mkdir(\'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\', 0777, true);
    		}
    
    		if(!move_uploaded_file($_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'tmp_name\'], \'uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/\'. $_FILES[\'' . $atributo->getNomeSnakeCase() . '\'][\'name\']))
    		{
    		    echo \'
                    <div class="alert alert-danger" role="alert">
                        Failed to send file.
                    </div>
    		        
                    \';
    		    return;
    		}
            $' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . ' ( "uploads/'.$objeto->getNomeSnakeCase().'/'.$atributo->getNomeSnakeCase().'/".$_FILES [\'' . $atributo->getNomeSnakeCase() . '\'][\'name\'] );
        }';
                
            }else{
                $codigo .= '
		$' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
            }
            
        }
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->isPrimary()){
                            $codigo .= '
		$' . lcfirst($objeto->getNome()) . '->get' .ucfirst($atributoObjeto->getNome()) . '()->set'.ucfirst ($atributo2->getNome()).' ( $_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        $listaParametros = array();
        $listaParametros[] = '$'.lcfirst($objeto->getNome());
        foreach($objetos1N as $obj){
            $codigo .= '
        $'.lcfirst($obj->getNome()).' = new '.ucfirst ($obj->getNome()).'();';
            foreach($obj->getAtributos() as $att){
                if($att->isPrimary()){
                    $codigo .= '
        $'.lcfirst($obj->getNome()).'->set'.ucfirst($att->getNome()).'($_POST[\''.$obj->getNomeSnakeCase().'\']);
';
                }
            }
            
            
            $listaParametros[] = '$'.lcfirst ($obj->getNome());
        }
        $codigo .= '
            
		if ($this->dao->insert ('.implode(', ', $listaParametros).' ))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso ao inserir '.$objeto->getNomeTextual().'
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha ao tentar Inserir '.$objeto->getNomeTextual().'
</div>

\';
		}
        echo \'<META HTTP-EQUIV="REFRESH" CONTENT="3; URL=index.php?page=' . $objeto->getNomeSnakeCase() . '">\';
	}


';
        
        return $codigo;
    }
    public function delete(Objeto $objeto) : string
    {
        $codigo = '';
        $atributoPrimaryKey = null;
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimaryKey = $atributo;
            }
        }
        if($atributoPrimaryKey == null){
            $codigo .= '
        //Object without PrimaryKey has no implementation of the delete method.
        public function delete(){}

';
            return $codigo;
        }
        
        $codigo .= '
    public function delete(){
	    if(!isset($_GET[\'delete\'])){
	        return;
	    }
        $selected = new '.ucfirst($objeto->getNome()).'();
	    $selected->set'.ucfirst ($atributoPrimaryKey->getNome()).'($_GET[\'delete\']);
        if(!isset($_POST[\'delete_' . $objeto->getNomeSnakeCase() . '\'])){
            $this->view->confirmDelete($selected);
            return;
        }
        if($this->dao->delete($selected))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso ao excluir '.$objeto->getNomeTextual().'
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha ao tentar excluir '.$objeto->getNomeTextual().'
</div>

\';
		}
    	echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?page=' . $objeto->getNomeSnakeCase() . '">\';
    }

';
        return $codigo;
        
    }
    public function fetch() : string {
        $codigo = '

	public function fetch() 
    {
		$list = $this->dao->fetch();
		$this->view->showList($list);
	}
';
        return $codigo;
    }
    public function geraMain():string{
        $codigo = '

    public function main(){
        
        if (isset($_GET[\'select\'])){
            echo \'<div class="row">\';
                $this->select();
            echo \'</div>\';
            return;
        }
        echo \'
		<div class="row">\';
        echo \'<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">\';
        
        if(isset($_GET[\'edit\'])){
            $this->edit();
        }else if(isset($_GET[\'delete\'])){
            $this->delete();
	    }else{
            $this->add();
        }
        $this->fetch();
        
        echo \'</div>\';
        echo \'</div>\';
            
    }
    public function mainAjax(){

        $this->addAjax();
        
            
    }

';
        return $codigo;
    }
    public function edit(Objeto $objeto) : string {
        $atributosComuns = array();
        $atributosObjetos = array();
        $atributoPrimary = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }else if($atributo->isObjeto()){
                $atributosObjetos[] = $atributo;
                
            }
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
        }
        if($atributoPrimary == null){
            return "";
        }
        
        $codigo = '';
        $codigo .= '
            
    public function edit(){
	    if(!isset($_GET[\'edit\'])){
	        return;
	    }
        $selected = new '.ucfirst($objeto->getNome()).'();
	    $selected->set'.ucfirst ($atributoPrimary->getNome()).'($_GET[\'edit\']);
	    $this->dao->fillBy'.ucfirst ($atributoPrimary->getNome()).'($selected);
	        
        if(!isset($_POST[\'edit_' . $objeto->getNomeSnakeCase() . '\'])){';
        $listaParametros = array();
        foreach($atributosObjetos as $atributoObjeto){
            $codigo .= '
            $'.strtolower($atributoObjeto->getTipo()).'Dao = new '.ucfirst ($atributoObjeto->getTipo()).'DAO($this->dao->getConnection());
            $list'.ucfirst ($atributoObjeto->getTipo()).' = $'.strtolower($atributoObjeto->getTipo()).'Dao->fetch();
';
            $listaParametros[] = '$list'.ucfirst ($atributoObjeto->getTipo());
            
            
        }
        $listaParametros[] = '$selected';
        $codigo .= '
            $this->view->showEditForm(';
        
        $codigo .= implode(', ', $listaParametros);
        
        $codigo .= ');
            return;
        }
            
		if (! ( ';
        $campos = array();
        foreach ($atributosComuns as $atributo) {    
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }else{
                $campos[] = 'isset ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] )';
            }
        }
        
        foreach($atributosObjetos as $atributoObjeto){
            foreach($this->software->getObjetos() as $objeto3){
                if($atributoObjeto->getTipo() == $objeto3->getNome())
                {
                    foreach($objeto3->getAtributos() as $atributo2){
                        if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){

                            $campos[] = ' isset($_POST [\'' . $atributoObjeto->getNomeSnakeCase() . '\'])';
                            break;
                        }
                    }
                    break;
                }
            }
        }
        $codigo .= implode(" && ", $campos);
        $codigo .= ')) {
			echo "Incompleto";
			return;
		}
';
        foreach ($atributosComuns as $atributo) 
        {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            $codigo .= '
		$selected->set' . ucfirst($atributo->getNome()). ' ( $_POST [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        
        $codigo .= '
            
		if ($this->dao->update ($selected ))
        {
			echo \'

<div class="alert alert-success" role="alert">
  Sucesso 
</div>

\';
		} else {
			echo \'

<div class="alert alert-danger" role="alert">
  Falha 
</div>

\';
		}
        echo \'<META HTTP-EQUIV="REFRESH" CONTENT="3; URL=index.php?page=' . $objeto->getNomeSnakeCase() . '">\';
            
    }
        ';
        return $codigo;
    }
    public function select(Objeto $objeto):string{
        $atributoPrimary = null;
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
                break;
            }
        }
        if($atributoPrimary == null){
            return "";
        }
        $codigo = '';
        $codigo .= '
            
    public function select(){
	    if(!isset($_GET[\'select\'])){
	        return;
	    }
        $selected = new '.ucfirst($objeto->getNome()).'();
	    $selected->set'.ucfirst ($atributoPrimary->getNome()).'($_GET[\'select\']);
	        
        $this->dao->fillBy'.ucfirst ($atributoPrimary->getNome()).'($selected);

        echo \'<div class="col-xl-7 col-lg-7 col-md-12 col-sm-12">\';
	    $this->view->showSelected($selected);
        echo \'</div>\';
            
';

        foreach($objeto->getAtributos() as $atributo){
            if(!$atributo->isArray1N()){
                continue;
            }
            $codigo .= '
        $this->dao->fetch'.ucfirst($atributo->getNome()).'($selected);
        echo \'<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">\';
        $this->view->show'.ucfirst($atributo->getNome()).'($selected);
        echo \'</div>\';
            

';
            
        }
        foreach($objeto->getAtributos() as $atributo){
            if(!$atributo->isArrayNN()){
                continue;
            }
            
            $codigo .= '
        $this->dao->fetch'.ucfirst($atributo->getNome()).'($selected);
        $'.lcfirst($atributo->getTipoDeArray()).'Dao = new '.ucfirst($atributo->getTipoDeArray()).'DAO($this->dao->getConnection());
        $list = $'.lcfirst($atributo->getTipoDeArray()).'Dao->fetch();
            
        echo \'<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">\';
        $this->view->show'.ucfirst($atributo->getNome()).'($selected);
        echo \'</div>\';
            
            
        if(!isset($_POST[\'add_'.$atributo->getTipoDeArraySnakeCase().'\']) && !isset($_GET[\'remover_'.$atributo->getTipoDeArraySnakeCase().'\'])){
            echo \'<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">\';
            $this->view->add'.ucfirst($atributo->getTipoDeArray()).'($list);
            echo \'</div>\';
        }else if(isset($_POST[\'add_'.$atributo->getTipoDeArraySnakeCase().'\']))
        {
            $'.lcfirst($atributo->getTipoDeArray()).' = new '.ucfirst($atributo->getTipoDeArray()).'();
            $'.lcfirst($atributo->getTipoDeArray()).'->setId($_POST[\'add_'.$atributo->getTipoDeArraySnakeCase().'\']);
            if($this->dao->insert'.ucfirst($atributo->getTipoDeArray()).'($selected, $'.lcfirst($atributo->getTipoDeArray()).'))
            {
			echo \'
                
<div class="alert alert-success" role="alert">
  Sucesso
</div>
                
\';
		} else {
			echo \'
                
<div class="alert alert-danger" role="alert">
  Falha
</div>
                
\';
		    }
            echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?page='.$objeto->getNomeSnakeCase().'&select=\'.$selected->get'.ucfirst ($atributoPrimary->getNome()).'().\'">\';
            return;
        }else  if(isset($_GET[\'remover_'.$atributo->getTipoDeArraySnakeCase().'\'])){
            
            $'.lcfirst($atributo->getTipoDeArray()).' = new '.ucfirst($atributo->getTipoDeArray()).'();
            $'.lcfirst($atributo->getTipoDeArray()).'->setId($_GET[\'remover_'.$atributo->getTipoDeArraySnakeCase().'\']);
            if($this->dao->remover'.ucfirst($atributo->getTipoDeArray()).'($selected, $'.lcfirst($atributo->getTipoDeArray()).'))
            {
		      echo \'
                
<div class="alert alert-success" role="alert">
  Sucesso
</div>
                
\';
		} else {
			echo \'
                
<div class="alert alert-danger" role="alert">
  Falha
</div>
                
\';
		      }
            echo \'<META HTTP-EQUIV="REFRESH" CONTENT="2; URL=index.php?page='.$objeto->getNomeSnakeCase().'&select=\'.$selected->get'.ucfirst ($atributoPrimary->getNome()).'().\'">\';
            return;
        }
                
                
        ';
            
            
        }
        $codigo .= '
            
    }';
        return $codigo;
    }

    
    public function geraControllers(Objeto $objeto)
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
        $codigo = '<?php
            
/**
 * Classe feita para manipulação do objeto ' . $objeto->getNome() . 'Controller
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */

namespace '.$this->software->getNome().'\\\\controller;
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($objeto->getNome()).'DAO;';
        
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isObjeto()) {
                $codigo .= '
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($atributo->getTipo()).'DAO;';
                
            }else if($atributo->isArrayNN()){
                $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($atributo->getTipoDeArray()).';
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($atributo->getTipoDeArray()).'DAO;';
            }
        }
        foreach($objetos1N as $obj){
            $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($obj->getNome()).';
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($obj->getNome()).'DAO;';
        }
        $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($objeto->getNome()).';
use '.$this->software->getNome().'\\\\view\\\\'.ucfirst($objeto->getNome()).'View;


class ' . ucfirst($objeto->getNome()) . 'Controller {

	protected  $view;
    protected $dao;';
        $codigo .= $this->construct($objeto);
        $codigo .= $this->delete($objeto);
        $codigo .= $this->fetch();
        $codigo .= $this->add($objeto);
        $codigo .= $this->addAjax($objeto);
        $codigo .= $this->edit($objeto);
        $codigo .= $this->geraMain();
        $codigo .= $this->select($objeto);

    
        
        
        
        $codigo .= '
}
?>';
        $caminho = ucfirst($objeto->getNome()).'Controller.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    
}


?>