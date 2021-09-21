<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao;

use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;
use GetCrudByUML\gerador\sqlGerador\SQLGerador;

class DAOGerador
{

    private $software;

    private $listaDeArquivos;



    public static function main(Software $software)
    {
        $gerador = new DAOGerador($software);
        return $gerador->geraCodigo();
    }

    public function __construct(Software $software)
    {
        $this->software = $software;
        
    }

    private function geraCodigo()
    {
        $this->geraDAOGeral();
        foreach($this->software->getObjetos() as $objeto){
            $this->geraDAOs($objeto);
        }
        return $this->listaDeArquivos;

        
    }
    private function geraDAOGeral()
    {
        
        
        $codigo = '<?php
                
                
namespace '.$this->software->getNome().'\\\\dao;
use PDO;

class DAO {
 
    protected $iniFile;
	protected $connection;
	private $sgdb;
	    
	public function getSgdb(){
		return $this->sgdb;
	}
	public function __construct(PDO $connection = null, $iniFile = DB_INI) {
	    $this->iniFile = $iniFile;
		if ($connection  != null) {
			$this->connection = $connection;
		} else {
			$this->connect();
		}
	}
	    
	public function connect() {
	    $config = parse_ini_file ( $this->iniFile );

		$sgdb = $config [\'sgdb\'];
		$dbName = $config [\'db_name\'];
		$host = $config [\'host\'];
		$port = $config [\'port\'];
		$user = $config [\'user\'];
		$password = $config [\'password\'];
	    $this->sgdb = $sgdb;

		if ($sgdb == "postgres") {
			$this->connection = new PDO ( \'pgsql:host=\' . $host. \' port=\'.$port.\' dbname=\' . $dbName . \' user=\' . $user . \' password=\' . $password);
		} else if ($sgdb == "mssql") {
			$this->connection = new PDO ( \'dblib:host=\' . $host . \';dbname=\' . $dbName, $user, $password);
		}else if($sgdb == "mysql"){
			$this->connection = new PDO( \'mysql:host=\' . $host . \';dbname=\' .  $dbName, $user, $password);
		}else if($sgdb == "sqlite"){
			$this->connection = new PDO(\'sqlite:\'.$dbName);
		}
		
	}
	public function setConnection($connection) {
		$this->connection = $connection;
	}
	public function getConnection() {
		return $this->connection;
	}
	public function closeConnection() {
		$this->connection = null;
	}
}
	    
?>';
        $caminho = 'DAO.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    private function geraDAOs(Objeto $objeto)
    {

        
        $codigo = '';
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
        $codigo .= '<?php
            
/**
 * Classe feita para manipulação do objeto ' . ucfirst($objeto->getNome()) . '
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte
 */
     
namespace '.$this->software->getNome().'\\\\dao;
use PDO;
use PDOException;';
        
        $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($objeto->getNome()).';';
        foreach($objetos1N as $obj){
            $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($obj->getNome()).';';
        }
        
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isArray()) {
                $codigo .= '
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($atributo->getTipoDeArray()).';

';
                
            }
        }
        $codigo .= '

class ' . ucfirst($objeto->getNome()) . 'DAO extends DAO {
    
    
';
        $codigo .= $this->update($objeto);
        $codigo .= $this->insert($objeto);
        $codigo .= $this->insertWithPK($objeto);
        $codigo .= $this->delete($objeto);
        $codigo .= $this->fetch($objeto);
        $codigo .= $this->fetchBy($objeto);
        $codigo .= $this->fillBy($objeto);
        $codigo .= $this->fetchAtributoNN($objeto);
        $codigo .= $this->fetchAtributo1N($objeto);
        $codigo .= $this->insertAtributoNN($objeto);
        $codigo .= $this->removerAtributoNN($objeto);
        $codigo .= $this->fetchByNN($objeto);
        $codigo .= $this->belongAtributo1N($objeto);
        $codigo .= '
}';
        
        $caminho = ucfirst($objeto->getNome()).'DAO.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
    
    private function update(Objeto $objeto){
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
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
        
        
        $codigo = '
            
            
    public function update(' . ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome()) . ')
    {';
       
        
        $codigo .= '
        $' . lcfirst($atributoPrimary->getNome()) . ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributoPrimary->getNome()) . '();';
        $codigo .= '
            
            
        $sql = "UPDATE ' . $objeto->getNomeSnakeCase() . '
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
        $codigo .= '";';
        
        foreach ($listaAtributo as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
            $codigo .= '
			$' . lcfirst($atributo->getNome()) . ' = $' . $nomeDoObjeto . '->get' . ucfirst($atributo->getNome()) . '();';
        }
        $codigo .= '
            
        try {
            
            $stmt = $this->getConnection()->prepare($sql);';
        foreach ($atributosComuns as $atributo) {
            if (substr($atributo->getTipo(), 0, 6) == 'Array ') {
                continue;
            }
            $codigo .= '
			$stmt->bindParam(":' . $atributo->getNome() . '", $' . $atributo->getNome() . ', PDO::'.$atributo->getTipoParametroPDO().');';
        }
        
        $codigo .= '
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
            
    }
            
            
';
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
        $parametros = array();
        $varPrimary = array();
        $parametros[] = ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome());
        foreach($objetos1N as $objeto2){
            $parametros[] = ucfirst($objeto2->getNome()).' $'.lcfirst($objeto2->getNome());
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $varPrimary[] = '
        $'.lcfirst($attr->getNome()).ucfirst($objeto2->getNome()).' = $'.lcfirst($objeto2->getNome()).'->get'.ucfirst($attr->getNome()).'();';
                }
            }
        }
        
                
        $codigo .= '
    public function insert('.implode(', ', $parametros).'){';
        
        $codigo .= '
        $sql = "INSERT INTO ' . $objeto->getNomeSnakeCase() . '(';
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
        $codigo .= ');";';
        
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isPrimary()){
                continue;
            }
            if($atributo->tipoListado()){
                $codigo .= '
		$' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '();';
            }else if($atributo->isObjeto())
            {
                $strCampoPrimary = '';
                foreach($this->software->getObjetos() as $objetoDoAtributo){
                    if($objetoDoAtributo->getNome() == $atributo->getTipo()){
                        foreach($objetoDoAtributo->getAtributos() as $att){
                            if($att->isPrimary()){
                                $strCampoPrimary = ucfirst($att->getNome());
                                break;
                            }
                        }
                        break;
                    }
                }
                
                
                $codigo .= '
		$' . lcfirst($atributo->getNome()). ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '()->get'.$strCampoPrimary.'();';
                
            }
            
        }
        
        $codigo .= implode('', $varPrimary);
        
        
        $codigo .= '
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);';
        foreach ($objeto->getAtributos() as $atributo)
        {
            if($atributo->isPrimary()){
                continue;
            }
            if($atributo->tipoListado() || $atributo->isObjeto())
            {
                $codigo .= '
			$stmt->bindParam(":' . $atributo->getNome() . '", $' . $atributo->getNome() . ', PDO::'.$atributo->getTipoParametroPDO().');';
                
            }
            
        }
        foreach($objetos1N as $objeto2){
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $codigo .= '
			$stmt->bindParam(":' . $attr->getNome().ucfirst($objeto2->getNome()) . '", $' . $attr->getNome().ucfirst($objeto2->getNome()) . ', PDO::'.$attr->getTipoParametroPDO().');';
                }
            }
        }
        
        
        $codigo .= '
			return $stmt->execute();
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}';
        
        
        
        
        $codigo .= '
            
    }';
        
        return $codigo;
        
    }
    /*

    private function insertWithPK(Objeto $objeto)
    {
        $codigo = '
    public function insertWithPK(' . ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome()) . '){';
        
        $codigo .= '
        $sql = "INSERT INTO ' . $objeto->getNomeSnakeCase() . '(';
        $listaAtributos = array();
        $listaAtributosVar = array();
        foreach ($objeto->getAtributos() as $atributo) 
        {
            if($atributo->tipoListado()){
                $listaAtributos[] = $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = ':' .lcfirst($atributo->getNome());
                
            }else if($atributo->isObjeto()){
                $listaAtributos[] = 'id_' . $atributo->getTipoSnakeCase() . '_' . $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = ':' .lcfirst($atributo->getNome());
                
            }else{
                continue;
            }
        }

        $codigo .= implode(", ", $listaAtributos);
        $codigo .= ') VALUES (';        
        $codigo .= implode(", ", $listaAtributosVar);
        $codigo .= ');";';
        
        
        foreach ($objeto->getAtributos() as $atributo) {
            
            if($atributo->tipoListado()){
                $codigo .= '
		$' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($objeto->getNOme()) . '->get' . ucfirst($atributo->getNome()) . '();';
            }else if($atributo->isObjeto())
            {
                $codigo .= '
		$' . lcfirst($atributo->getNome()). ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '()->getId();';
                
            }
            
        }
        $codigo .= '
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);';
        foreach ($objeto->getAtributos() as $atributo) 
        {
            if($atributo->tipoListado() || $atributo->isObjeto())
            {
                $codigo .= '
			$stmt->bindParam(":' . $atributo->getNome() . '", $' . $atributo->getNome() . ', PDO::'.$atributo->getTipoParametroPDO().');';
            
            }
        }
            

        $codigo .= '
			return $stmt->execute();
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}';
        

        
        
        $codigo .= '

    }';
        
        return $codigo;
        
    }
    */
    private function insertWithPK(Objeto $objeto)
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
        $parametros = array();
        $varPrimary = array();
        $parametros[] = ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome());
        foreach($objetos1N as $objeto2){
            $parametros[] = ucfirst($objeto2->getNome()).' $'.lcfirst($objeto2->getNome());
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $varPrimary[] = '
        $'.lcfirst($attr->getNome()).ucfirst($objeto2->getNome()).' = $'.lcfirst($objeto2->getNome()).'->get'.ucfirst($attr->getNome()).'();';
                }
            }
        }
        
        
        $codigo .= '
    public function insertWithPK('.implode(', ', $parametros).'){';
        
        $codigo .= '
        $sql = "INSERT INTO ' . $objeto->getNomeSnakeCase() . '(';
        $listaAtributos = array();
        $listaAtributosVar = array();
        foreach ($objeto->getAtributos() as $atributo)
        {

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
        $codigo .= ');";';
        
        foreach ($objeto->getAtributos() as $atributo) {

            if($atributo->tipoListado()){
                $codigo .= '
		$' . lcfirst($atributo->getNome()) . ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '();';
            }else if($atributo->isObjeto())
            {
                $strCampoPrimary = '';
                foreach($this->software->getObjetos() as $objetoDoAtributo){
                    if($objetoDoAtributo->getNome() == $atributo->getTipo()){
                        foreach($objetoDoAtributo->getAtributos() as $att){
                            if($att->isPrimary()){
                                $strCampoPrimary = ucfirst($att->getNome());
                                break;
                            }
                        }
                        break;
                    }
                }
                
                
                $codigo .= '
		$' . lcfirst($atributo->getNome()). ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '()->get'.$strCampoPrimary.'();';
                
            }
            
        }
        
        $codigo .= implode('', $varPrimary);
        
        
        $codigo .= '
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);';
        foreach ($objeto->getAtributos() as $atributo)
        {

            if($atributo->tipoListado() || $atributo->isObjeto())
            {
                $codigo .= '
			$stmt->bindParam(":' . $atributo->getNome() . '", $' . $atributo->getNome() . ', PDO::'.$atributo->getTipoParametroPDO().');';
                
            }
            
        }
        foreach($objetos1N as $objeto2){
            foreach($objeto2->getAtributos() as $attr){
                if($attr->isPrimary()){
                    $codigo .= '
			$stmt->bindParam(":' . $attr->getNome().ucfirst($objeto2->getNome()) . '", $' . $attr->getNome().ucfirst($objeto2->getNome()) . ', PDO::'.$attr->getTipoParametroPDO().');';
                }
            }
        }
        
        
        $codigo .= '
			return $stmt->execute();
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}';
        
        
        
        
        $codigo .= '
            
    }';
        
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
        $codigo = '

	public function delete(' . $objeto->getNome() . ' $' . lcfirst($objeto->getNome()). '){
		$' . $atributoPrimary->getNome() . ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($objeto->getAtributos()[0]->getNome()) . '();
		$sql = "DELETE FROM ' . $objeto->getNomeSnakeCase() . ' WHERE ' . $atributoPrimary->getNomeSnakeCase() . ' = :' . $atributoPrimary->getNomeSnakeCase() . '";
		    
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam(":' . $atributoPrimary->getNomeSnakeCase() . '", $' . $atributoPrimary->getNome() . ', PDO::PARAM_INT);
			return $stmt->execute();
			    
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}
	}

';
        return $codigo;
        
    }
    private function fetchByNN($objeto) : string {
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
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
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            } else if ($atributo->isObjeto()) {
                $atributosObjetos[] = $atributo;
            }
        }
        if(count($objetos1N) == 0){
            return "";
        }
        $atributosStr = array();
        foreach ($objetos1N as $obj){
            $atributosStr[] =  ucfirst($obj->getNome()).' $'.lcfirst($obj->getNome());
        }
        $codigo .= '
	public function fetchByNN('.implode(', ',$atributosStr).') {
		$list = array ();';
        $atributosStr = array();
        foreach ($objetos1N as $obj){
            
            foreach($obj->getAtributos() as $att){
                if($att->isPrimary()){
                    $atributosStr[] =  $objeto->getNomeSnakeCase().'.'.$att->getNomeSnakeCase().'_'.$obj->getNomeSnakeCase().' = :'.$att->getNome().$obj->getNome();
                    $codigo .= '
        $'.lcfirst($att->getNome()).ucfirst($obj->getNome()).' = $'.lcfirst($obj->getNome()).'->get'.ucfirst($att->getNome()).'();';
                    break;
                }
            }
        }
        $codigo .= '
		$sql = "';
        
        $sqlGerador = new SQLGerador($this->software);
        $codigo .= $sqlGerador->getSQLSelect($objeto);
        $codigo .= '
                    WHERE '.implode(' AND ', $atributosStr);        
        
        $codigo .= ' LIMIT 1000";
            
        try {
            $stmt = $this->connection->prepare($sql);
            
		    if(!$stmt){
                echo "<br>Mensagem de erro retornada: ".$this->connection->errorInfo()[2]."<br>";
		        return $list;
		    }';
        foreach ($objetos1N as $obj){            
            foreach($obj->getAtributos() as $att){
                if($att->isPrimary()){
                    $codigo .= '
            $stmt->bindParam(":'.lcfirst($att->getNome()).ucfirst($obj->getNome()).'", $'.lcfirst($att->getNome()).ucfirst($obj->getNome()).', PDO::'.$att->getTipoParametroPDO().');
';
                    break;
                }
            }
        }
        $codigo .= '
            $stmt->execute();
		    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    foreach ( $result as $row)
            {
		        $' . lcfirst($objeto->getNome()) . ' = new ' . ucfirst($objeto->getNome()) . '();';
        foreach ($atributosComuns as $atributo) {
            $codigo .= '
                $' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . '( $row [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        foreach ($atributosObjetos as $atributoObjeto) {
            
            foreach ($this->software->getObjetos() as $objeto2) {
                if ($objeto2->getNome() == $atributoObjeto->getTipo()) {
                    foreach ($objeto2->getAtributos() as $atributo3) {
                        if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        } else if ($atributo3->tipoListado()) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        }
                    }
                    break;
                }
            }
        }
        $codigo .= '
                $list [] = $' . $nomeDoObjeto . ';';
        $codigo .= '
            
            
		    }
		} catch(PDOException $e) {
		    echo $e->getMessage();
 		}
        return $list;
    }
        ';
        
        
        return $codigo;
    }
    private function fetch($objeto) : string {
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            } else if ($atributo->isObjeto()) {
                $atributosObjetos[] = $atributo;
            }
        }
        $codigo .= '
	public function fetch() {
		$list = array ();
		$sql = "';
        
        $sqlGerador = new SQLGerador($this->software);
        $codigo .= $sqlGerador->getSQLSelect($objeto);
        
        
        $codigo .= ' LIMIT 1000";

        try {
            $stmt = $this->connection->prepare($sql);
            
		    if(!$stmt){   
                echo "<br>Mensagem de erro retornada: ".$this->connection->errorInfo()[2]."<br>";
		        return $list;
		    }
            $stmt->execute();
		    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    foreach ( $result as $row) 
            {
		        $' . lcfirst($objeto->getNome()) . ' = new ' . ucfirst($objeto->getNome()) . '();';
        foreach ($atributosComuns as $atributo) {
            $codigo .= '
                $' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . '( $row [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        foreach ($atributosObjetos as $atributoObjeto) {
            
            foreach ($this->software->getObjetos() as $objeto2) {
                if ($objeto2->getNome() == $atributoObjeto->getTipo()) {
                    foreach ($objeto2->getAtributos() as $atributo3) {
                        if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        } else if ($atributo3->tipoListado()) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        }
                    }
                    break;
                }
            }
        }
        $codigo .= '
                $list [] = $' . $nomeDoObjeto . ';';
        $codigo .= '

	
		    }
		} catch(PDOException $e) {
		    echo $e->getMessage();
 		}
        return $list;	
    }
        ';
        
        
        return $codigo;
    }
    private function fetchBy(Objeto $objeto) : string {
        
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMA = ucfirst($objeto->getNome());
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            } else if ($atributo->isObjeto()) {
                $atributosObjetos[] = $atributo;
            }
        }
        
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isArray()){
                continue;
            }
            
            
            $codigo .= '
                
    public function fetchBy' . ucfirst($atributo->getNome()) . '(' . $nomeDoObjetoMA . ' $' . $nomeDoObjeto . ') {
        $lista = array();';
            
            if($atributo->tipoListado()){
                $codigo .= '
	    $' . $atributo->getNome() . ' = $' . lcfirst($objeto->getNome()). '->get' . ucfirst($atributo->getNome()) . '();';
            }else if($atributo->isObjeto()){
                $objetoDesseAtributo = null;
                foreach($this->software->getObjetos() as $objeto2){
                    if($atributo->getTipo() == $objeto2->getNome()){
                        $objetoDesseAtributo = $objeto2;
                    }
                }
                if($objetoDesseAtributo == null){
                    $codigo .= '
    }//Metodo Nao implementado por falta de correspondencia de objeto';
                    continue;
                }
                $atributoPrimaryDesseCara = null;
                foreach($objetoDesseAtributo->getAtributos() as $atributo4){
                    if($atributo4->isPrimary()){
                        $atributoPrimaryDesseCara  = $atributo4;
                    }
                }
                if($atributoPrimaryDesseCara == null){
                    $codigo .= '
    }//Metodo Nao implementado por falta de primary key na correspondencia de objeto';
                    continue;
                }
                
                $codigo .= '
	    $' . $atributo->getNome() . ' = $' . lcfirst($objeto->getNome()). '->get' . ucfirst($atributo->getNome()) . '()->get'.ucfirst($atributoPrimaryDesseCara->getNome()).'();';
                
            }
            
            
            $codigo .= '
                
        $sql = "';
            
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= $sqlGerador->getSQLSelect($objeto);
            
            if ($atributo->getTipo() == Atributo::TIPO_IMAGE || $atributo->getTipo() == Atributo::TIPO_STRING || $atributo->getTipo() == Atributo::TIPO_DATE || $atributo->getTipo() == Atributo::TIPO_DATE_TIME) {
                $codigo .= '
            WHERE ' . $objeto->getNomeSnakeCase() . '.' . $atributo->getNomeSnakeCase() . ' like :' . $atributo->getNome() . '";';
            } 
            else if($atributo->getTipo() == Atributo::TIPO_BOOLEAN || $atributo->getTipo() == Atributo::TIPO_FLOAT || $atributo->getTipo() == Atributo::TIPO_INT)
            {
                $codigo .= '
            WHERE ' . $objeto->getNomeSnakeCase() . '.' . $atributo->getNomeSnakeCase() . ' = :' . $atributo->getNome() . '";';
            }else if($atributo->isOBjeto()){
                $codigo .= '
            WHERE ' . $objeto->getNomeSnakeCase() . '.' . $atributoPrimaryDesseCara->getNomeSnakeCase().'_'.$atributo->getNomeSnakeCase() . ' = :' . $atributo->getNome() . '";';
            }
            
            $codigo .= '
                
        try {
                
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":'.$atributo->getNome().'", $'.$atributo->getNome().', PDO::'.$atributo->getTipoParametroPDO().');
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ( $result as $row ){
		        $' . lcfirst($objeto->getNome()) . ' = new ' . ucfirst($objeto->getNome()) . '();';
        foreach ($atributosComuns as $atributo) {
            $codigo .= '
                $' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . '( $row [\'' . $atributo->getNomeSnakeCase() . '\'] );';
        }
        foreach ($atributosObjetos as $atributoObjeto) {
            
            foreach ($this->software->getObjetos() as $objeto2) {
                if ($objeto2->getNome() == $atributoObjeto->getTipo()) {
                    foreach ($objeto2->getAtributos() as $atributo3) {
                        if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        } else if ($atributo3->tipoListado()) {
                            $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                        }
                    }
                    break;
                }
            }
        }
        $codigo .= '
                $lista [] = $' . $nomeDoObjeto . ';';
        $codigo .= '

	
		    }
    			    
        } catch(PDOException $e) {
            echo $e->getMessage();
    			    
        }
		return $lista;
    }';
        }
        return $codigo;
    }
    private function fillBy(Objeto $objeto) : string {
        $nomeDoObjeto = lcfirst($objeto->getNome());
    
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            } else if ($atributo->isObjeto()) {
                $atributosObjetos[] = $atributo;
            }
        }
        $codigo = '';
        
        foreach ($atributosComuns as $atributo) {
            
            $codigo .= '
                
    public function fillBy' . ucfirst($atributo->getNome()) . '(' . ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome()) . ') {
        
	    $' . $atributo->getNome() . ' = $' . lcfirst($objeto->getNome()) . '->get' . ucfirst($atributo->getNome()) . '();';
            $codigo .= '
	    $sql = "';
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= $sqlGerador->getSQLSelect($objeto);
            
            $codigo .= '
                WHERE ' . $objeto->getNomeSnakeCase() . '.' . $atributo->getNomeSnakeCase() . ' = :'. $atributo->getNome();
                        
            $codigo .= '
                 LIMIT 1000";
                
        try {
            $stmt = $this->connection->prepare($sql);
                
		    if(!$stmt){
                echo "<br>Mensagem de erro retornada: ".$this->connection->errorInfo()[2]."<br>";
		    }
            $stmt->bindParam(":'.$atributo->getNome().'", $'.$atributo->getNome().', PDO::'.$atributo->getTipoParametroPDO().');
            $stmt->execute();
		    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    foreach ( $result as $row )
            {';
            foreach ($atributosComuns as $atributo) {
                $codigo .= '
                $' . lcfirst($objeto->getNome()) . '->set' . ucfirst($atributo->getNome()) . '( $row [\'' . $atributo->getNomeSnakeCase() . '\'] );';
            }
            foreach ($atributosObjetos as $atributoObjeto) {
                
                foreach ($this->software->getObjetos() as $objeto2) {
                    if ($objeto2->getNome() == $atributoObjeto->getTipo()) {
                        foreach ($objeto2->getAtributos() as $atributo3) {
                            if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                                $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            } else if ($atributo3->tipoListado()) {
                                $codigo .= '
                $' . $nomeDoObjeto . '->get' . ucfirst($atributoObjeto->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributoObjeto->getTipoSnakeCase() . '_' . $atributoObjeto->getNomeSnakeCase() . '\'] );';
                            }
                        }
                        break;
                    }
                }
            }
            
            $codigo .= '
                
                
		    }
		} catch(PDOException $e) {
		    echo $e->getMessage();
 		}
		return $' . $nomeDoObjeto . ';
    }';
        }
        
        return $codigo;
    }
    
    
    private function fetchAtributo1N($objeto) : string {
        $codigo = '';
        $atributos1N = array();
        $atributoPrimary = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
            if ($atributo->isArray1N()) {
                $atributos1N[] = $atributo;
            }
        }
        foreach($atributos1N as $atributo){
            $objetoDoAtributo = null;
            foreach($this->software->getObjetos() as $obj){
                if($obj->getNome() == $atributo->getTipoDeArray()){
                    $objetoDoAtributo = $obj;
                }
            }
            
            if($objetoDoAtributo == null){
                return "";
            }
            $atributoDele = null;
            foreach($objetoDoAtributo->getAtributos() as $attr){
                if($attr->tipoListado() && $attr->isPrimary())
                {
                    $atributoDele = $attr;
                    break;
                }
            }
            if($atributoDele == null){
                return "";
            }
            
            $codigo .= '
                
    public function fetch' . ucfirst($atributo->getNome()) . '(' . ucfirst($objeto->getNome()) . ' $' . strtolower($objeto->getNome()) . '){';

            $codigo .= '
	    $' . lcfirst($atributoPrimary->getNome()) . ' = $' . lcfirst($objeto->getNome()). '->get' . ucfirst($atributoPrimary->getNome()) . '();';
            $filtro = '
            WHERE '. $atributoPrimary->getNome().'_'.$objeto->getNomeSnakeCase().' = :' . $atributoPrimary->getNome() . ';';
            
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= '
        $sql = "';
            $codigo .= $sqlGerador->getSQLSelect($objetoDoAtributo);
            $codigo .= $filtro;
            
            $codigo .= '";';
            $codigo .= '
        try {
                
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":' . $atributoPrimary->getNome() . '", $' . $atributoPrimary->getNome() . ', PDO::'.$atributoPrimary->getTipoParametroPDO().');
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ( $result as $row ){
                
                $'.lcfirst($objetoDoAtributo->getNome()).' = new '.ucfirst($objetoDoAtributo->getNome()).'();
';
            
            foreach ($objetoDoAtributo->getAtributos() as $atributo) {
                if($atributo->tipoListado()){
                    $codigo .= '
                $' . lcfirst($objetoDoAtributo->getNome()) . '->set' . ucfirst($atributo->getNome()) . '( $row [\'' . $atributo->getNomeSnakeCase() . '\'] );';
                }else if($atributo->isObjeto()){
                    foreach ($this->software->getObjetos() as $objeto2) {
                        if ($objeto2->getNome() == $atributo->getTipo()) {
                            foreach ($objeto2->getAtributos() as $atributo3) {
                                if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                                    $codigo .= '
                $'.lcfirst($objetoDoAtributo->getNome()).'->get' . ucfirst($atributo->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributo->getTipoSnakeCase() . '_' . $atributo->getNomeSnakeCase() . '\'] );';
                                } else if ($atributo3->tipoListado()) {
                                    $codigo .= '
                $'.lcfirst($objetoDoAtributo->getNome()).'->get' . ucfirst($atributo->getNome()) . '()->set' . ucfirst($atributo3->getNome()) . '( $row [\'' . $atributo3->getNomeSnakeCase() . '_' . $atributo->getTipoSnakeCase() . '_' . $atributo->getNomeSnakeCase() . '\'] );';
                                }
                            }
                            break;
                        }
                    }
                }
                
            }
            
            $codigo .= '
                $'.lcfirst($objeto->getNome()).'->add'.ucfirst($objetoDoAtributo->getNome()).'($'.lcfirst($objetoDoAtributo->getNome()).');
            }
                    
        } catch(PDOException $e) {
            echo $e->getMessage();
                    
        }
                    
';
            $codigo .= '
                
                
    }
                
                
';
            
        }
        return $codigo;
        
    }
    
    private function belongAtributo1N($objeto) : string {
        $codigo = '';
        $atributos1N = array();
        $atributoPrimary = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isPrimary()){
                $atributoPrimary = $atributo;
            }
            if ($atributo->isArray1N()) {
                $atributos1N[] = $atributo;
            }
        }
        foreach($atributos1N as $atributo){
            $objetoDoAtributo = null;
            foreach($this->software->getObjetos() as $obj){
                if($obj->getNome() == $atributo->getTipoDeArray()){
                    $objetoDoAtributo = $obj;
                }
            }
            
            if($objetoDoAtributo == null){
                return "";
            }
            $atributoDele = null;
            foreach($objetoDoAtributo->getAtributos() as $atributo3){
                if($atributo3->tipoListado() && $atributo3->isPrimary())
                {
                    $atributoDele = $atributo3;
                    break;
                }
            }
            $codigo .= '
                
    public function belog' . ucfirst($atributo->getTipoDeArray()) . '(' . ucfirst($objeto->getNome()) . ' $' . strtolower($objeto->getNome()) . ', ' . ucfirst($atributo->getTipoDeArray()) . ' $' . lcfirst($atributo->getTipoDeArray()) . '){';

            

            $codigo .= '
	    $' . $atributoPrimary->getNome() .ucfirst($objeto->getNome()). ' = $' . lcfirst($objeto->getNome()). '->get' . ucfirst($atributoPrimary->getNome()) . '();
        $'.lcfirst($atributoDele->getNome()).ucfirst($objetoDoAtributo->getNome()).' = $'.lcfirst($objetoDoAtributo->getNome()).'->get'.ucfirst($atributoDele->getNome()).'();';
            $filtro = '
            WHERE '.$objetoDoAtributo->getNomeSnakeCase() . '.'. $atributoPrimary->getNome().'_'.$objeto->getNomeSnakeCase().' = :' . $atributoPrimary->getNome() .ucfirst($objeto->getNome()).'
            AND '.$objetoDoAtributo->getNomeSnakeCase() . '.' . $atributoDele->getNomeSnakeCase().'  = :' . lcfirst($atributoDele->getNome()).ucfirst($objetoDoAtributo->getNome()). ';';
            
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= '
        $sql = "';
            $codigo .= $sqlGerador->getSQLSelect($objetoDoAtributo);
            $codigo .= $filtro;
            
            $codigo .= '";';
            $codigo .= '
        try {
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":' . lcfirst($atributoPrimary->getNome()) .ucfirst($objeto->getNome()). '", $' . lcfirst($atributoPrimary->getNome()) .ucfirst($objeto->getNome()). ', PDO::'.$atributoPrimary->getTipoParametroPDO().');
            $stmt->bindParam(":' . lcfirst($atributoDele->getNome()) .ucfirst($objetoDoAtributo->getNome()). '", $' . lcfirst($atributoDele->getNome()) .ucfirst($objetoDoAtributo->getNome()). ', PDO::'.$atributoDele->getTipoParametroPDO().');
            $stmt->execute();
            if($stmt->fetchColumn() > 0){
                return true;
            }
            return false;
         
            
        } catch(PDOException $e) {
            echo $e->getMessage();
            
        }
        return false;
';
            $codigo .= '
    }
                
                
';
            
        }
        return $codigo;
                
    }
    private function fetchAtributoNN($objeto) : string {
        $codigo = '';
        $atributosNN = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isArrayNN()) {
                $atributosNN[] = $atributo;
            }
        }
        foreach ($atributosNN as $atributo) {
            $codigo .= '
    public function fetch' . ucfirst($atributo->getNome()) . '(' . ucfirst($objeto->getNome()) . ' $' . strtolower($objeto->getNome()) . ')
    {
        $id = $' . strtolower($objeto->getNome()) . '->getId();
        $sql = "SELECT * FROM
                ' . strtolower($objeto->getNome()) . '_' . strtolower(explode(' ', $atributo->getTipo())[2]) . '
                INNER JOIN ' . strtolower(explode(' ', $atributo->getTipo())[2]) . '
                ON  ' . strtolower($objeto->getNome()) . '_' . strtolower(explode(' ', $atributo->getTipo())[2]) . '.id_' . strtolower(explode(' ', $atributo->getTipo())[2]) . ' = ' . strtolower(explode(' ', $atributo->getTipo())[2]) . '.id
                 WHERE ' . strtolower($objeto->getNome()) . '_' . strtolower(explode(' ', $atributo->getTipo())[2]) . '.id_' . $objeto->getNomeSnakeCase() . ' = $id";
        $result = $this->getConnection ()->query ( $sql );
                     
        foreach ($result as $row) {
            $' . strtolower(explode(' ', $atributo->getTipo())[2]) . ' = new ' . ucfirst(explode(' ', $atributo->getTipo())[2]) . '();';
            
            foreach ($this->software->getObjetos() as $obj) {
                if (strtolower($obj->getNome()) == strtolower(explode(' ', $atributo->getTipo())[2])) {
                    foreach ($obj->getAtributos() as $atr) {
                        
                        $nomeDoAtributoMA = ucfirst($atr->getNome());
                        
                        if ($atr->getTipo() == Atributo::TIPO_INT || $atr->getTipo() == Atributo::TIPO_STRING || $atr->getTipo() == Atributo::TIPO_FLOAT) {
                            $codigo .= '
	        $' . strtolower(explode(' ', $atributo->getTipo())[2]) . '->set' . $nomeDoAtributoMA . '( $row [\'' . strtolower($atr->getNome()) . '\'] );';
                        } else if (substr($atr->getTipo(), 0, 6) == 'Array ') {
                            //
                            $codigo .= '
            $' . strtolower(explode(' ', $atributo->getTipo())[2]) . 'Dao = new ' . ucfirst(explode(' ', $atributo->getTipo())[2]) . 'DAO($this->getConnection());
            $' . strtolower(explode(' ', $atributo->getTipo())[2]) . 'Dao->fetch' . ucfirst($atr->getNome()) . '($' . strtolower(explode(' ', $atributo->getTipo())[2]) . ');';
                            // $objetoDao->fetch
                        }
                    }
                    $codigo .= '';
                    break;
                }
            }
            
            $codigo .= '
            $' . strtolower($objeto->getNome()) . '->add' . ucfirst(explode(' ', $atributo->getTipo())[2]) . '($' . strtolower(explode(' ', $atributo->getTipo())[2]) . ');
                
        }
        return $' . strtolower($objeto->getNome()) . ';
    }';
        }
            
        return $codigo;
    }
    private function insertAtributoNN($objeto) : string {
        $codigo = '';
        $atributosNN = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isArrayNN()) {
                $atributosNN[] = $atributo;
            }
        }
        foreach ($atributosNN as $atributo) {
            $codigo .= '
                
                
	public function insert' . ucfirst($atributo->getTipoDeArray()) . '(' . ucfirst($objeto->getNome()) . ' $' . lcfirst($objeto->getNome()). ', ' . ucfirst($atributo->getTipoDeArray()) . ' $' . lcfirst($atributo->getTipoDeArray()) . ')
    {
        $id' . ucfirst($objeto->getNome()) . ' =  $' .lcfirst( $objeto->getNome()). '->getId();
        $id' . ucfirst($atributo->getTipoDeArray()) . ' = $' . lcfirst($atributo->getTipoDeArray()) . '->getId();
		$sql = "INSERT INTO ' . $objeto->getNomeSnakeCase() . '_' . $atributo->getArrayTipoSnakeCase() . '(';
            $codigo .= '
                    id_' . $objeto->getNomeSnakeCase() . ',
                    id_' . $atributo->getArrayTipoSnakeCase(). ')
				VALUES(';
            $codigo .= '
                :id' . ucfirst($objeto->getNome()) . ',
                :id' . ucfirst($atributo->getTipoDeArray());
            $codigo .= ')";';
            $codigo .= '
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);';
            
            $codigo .= '
		    $stmt->bindParam(":id' . ucfirst($objeto->getNome()) . '", $id' . ucfirst($objeto->getNome()) . ', PDO::PARAM_INT);
            $stmt->bindParam(":id' . ucfirst($atributo->getTipoDeArray()) . '", $id' . ucfirst($atributo->getTipoDeArray()) . ', PDO::PARAM_INT);
                
';
            
            $codigo .= '
			return $stmt->execute();
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}
	}';
        }
        return $codigo;
    }

    private function removerAtributoNN($objeto) : string {
        $codigo = '';
        $nomeDoObjeto = lcfirst($objeto->getNome());
        $nomeDoObjetoMA = ucfirst($objeto->getNome());
        
        $atributosNN = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isArrayNN()) {
                $atributosNN[] = $atributo;
            }
        }
        foreach ($atributosNN as $atributo) {
            
            $codigo .= '
	public function remover' . ucfirst($atributo->getTipoDeArray()) . '(' . $nomeDoObjetoMA . ' $' . $nomeDoObjeto . ', ' . ucfirst(explode(" ", $atributo->getTipo())[2]) . ' $' . strtolower(explode(" ", $atributo->getTipo())[2]) . '){
        $id' . ucfirst($objeto->getNome()) . ' =  $' . $nomeDoObjeto . '->getId();
        $id' . ucfirst(explode(' ', $atributo->getTipo())[2]) . ' = $' . strtolower(explode(" ", $atributo->getTipo())[2]) . '->getId();
		$sql = "DELETE FROM  ' . strtolower($objeto->getNome()) . '_' . strtolower(explode(' ', $atributo->getTipo())[2]) . ' WHERE ';
            $codigo .= '
                    id_' . $objeto->getNomeSnakeCase() . ' = :id' . ucfirst($objeto->getNome()) . '
                    AND
                    id_' . $atributo->getTipoDeArraySnakeCase() . ' = :id' . ucfirst($atributo->getTipoDeArray()) . '";';
            
            $codigo .= '
		try {
			$db = $this->getConnection();
			$stmt = $db->prepare($sql);';
            
            $codigo .= '
		    $stmt->bindParam(":id' . ucfirst($objeto->getNome()) . '", $id' . ucfirst($objeto->getNome()) . ', PDO::PARAM_INT);
            $stmt->bindParam(":id' . ucfirst(explode(' ', $atributo->getTipo())[2]) . '", $id' . ucfirst($atributo->getTipoDeArray()) . ', PDO::PARAM_INT);
';
            
            $codigo .= '
			return $stmt->execute();
		} catch(PDOException $e) {
			echo \'{"error":{"text":\'. $e->getMessage() .\'}}\';
		}
	}
                
                
';
        }
        return $codigo; 
    }

}

?>