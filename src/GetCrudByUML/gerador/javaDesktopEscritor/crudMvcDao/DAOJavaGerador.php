<?php 


namespace GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Software;
use GetCrudByUML\gerador\sqlGerador\SQLGerador;

class DAOJavaGerador{
    
    
    private $software;
    
    private $listaDeArquivos;
    
    private $diretorio;
    
    public static function main(Software $software)
    {
        $gerador = new DAOJavaGerador($software);
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
   
    private function geraDAOGeral(){
        $codigo = '';
        $codigo .= '
package com.'.strtolower($this->software->getNome()).'.dao;
    
    
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;
    
/**
 * Faz conexão com banco de dados e gerencia persistências.
 * @author Jefferson Uchôa Ponte
 *
 */
public class DAO {
    
    
	/**
	 * Sistema gerenciador de banco de dados.
	 */
	private String sgdb;
    
	/**
	 * Conexão com banco.
	 */
	private Connection connection;
    
    
	/**
	 * Constroi objeto DAO com conexão com banco de dados.
	 */
	public DAO() {
		connect();
	}
    
	/**
	 * Constroi objeto DAO com conexão com banco de dados.
	 */
	public DAO(Connection connection) {
		this.connection = connection;
	}
    
    
	/**
	 * Faz uma conexão com banco de dados de acordo com as informações do arquivo de configuração.
	 */
	public void connect() {
		this.connection = null;
		try {
			Properties config = new Properties();
			FileInputStream file;
			file = new FileInputStream(ARQUIVO_CONFIGURACAO);
			config.load(file);
			String sgdb, host, port, dbName, user, password;
    
			sgdb = config.getProperty("sgdb");
			host = config.getProperty("host");
			port = config.getProperty("port");
			dbName = config.getProperty("db_name");
			user = config.getProperty("user");
			password = config.getProperty("password");
    
			file.close();
			if (sgdb.equals("postgres")) {
				Class.forName(DRIVER_POSTGRES);
				this.connection = DriverManager.getConnection(JDBC_BANCO_POSTGRES+ "//" + host + "/" + dbName, user, password);
    
			} else if (sgdb.equals("sqlite")) {
				Class.forName(DRIVER_SQLITE);
				this.connection = DriverManager.getConnection(JDBC_BANCO_SQLITE+dbName);
			} else if (sgdb.equals("mysql")) {
				Class.forName(DRIVER_MYSQL);
				this.connection = DriverManager.getConnection(JDBC_BANCO_MYSQL + "//" + host +":"+ port + "/" + dbName, user, password);
			}
    
		} catch (ClassNotFoundException e1) {
			e1.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
    
	/**
	 * Retorna a conexão com banco de dados.
	 * @return
	 */
	public Connection getConnection() {
		return connection;
	}
	/**
	 * Atribui a conexão com banco de dados.
	 * @param connection
	 */
	public void setConnection(Connection connection) {
		this.connection = connection;
	}
    
    
    
	/**
	 * @return the sgdb
	 */
	public String getSgdb() {
		return sgdb;
	}
    
	/**
	 * @param sgdb
	 */
	public void setSgdb(String sgdb) {
		this.sgdb = sgdb;
	}
    
	/**
	 * Arquivo de configuração do banco de dados.
	 */
	public static final String ARQUIVO_CONFIGURACAO = "../'. strtolower($this->software->getNome()) . '_db.ini";
	    
	/**
	 * Drive jdbc para Sqlite.
	 */
	public static final String DRIVER_SQLITE = "org.sqlite.JDBC";
	/**
	 * Banco de dados squlite
	 */
	    
	public static final String JDBC_BANCO_SQLITE = "jdbc:sqlite:";
	    
	/**
	 * JDBC para postgres.
	 */
	public static final String JDBC_BANCO_POSTGRES = "jdbc:postgresql:";
	/**
	 * Driver JDBC postgres
	 */
	public static final String DRIVER_POSTGRES = "org.postgresql.Driver";
	/**
	 * JDBC Mysql
	 */
	public static final String JDBC_BANCO_MYSQL = "jdbc:mysql:";
	/**
	 * Driver JDBC Mysql
	 */
	public static final String DRIVER_MYSQL = "com.mysql.jdbc.Driver";
	    
}';
        
        $caminho = 'DAO.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $this->listaDeArquivos;
    }
    private function delete(Objeto $objeto){
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


	public boolean delete(' . ucfirst($objeto->getNome()). ' ' . strtolower($objeto->getNome()). '){
		String sql = "DELETE FROM ' . $objeto->getNomeSnakeCase(). ' WHERE ' . $atributoPrimary->getNomeSnakeCase() . ' = ?";
		try{
        	PreparedStatement stmt = this.getConnection().prepareStatement(sql);
        	stmt.setInt(1, '.lcfirst($objeto->getNome()).'.get' . ucfirst($atributoPrimary->getNome()). '());
        	stmt.execute();
        	stmt.close();
        	return true;
    	} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
        	    
	}

';
        return $codigo;
    }
    private function geraDAOs(Objeto $objeto)
    {
        $codigo = '';
        
        $nomeDoObjeto = strtolower($objeto->getNome());
        $nomeDoObjetoMA = strtoupper(substr($objeto->getNome(), 0, 1)) . substr($objeto->getNome(), 1, 100);
        $atributosComuns = array();
        $atributosNN = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if(substr($atributo->getTipo(),0,6) == 'Array '){
                if(explode(' ', $atributo->getTipo())[1]  == 'n:n'){
                    $atributosNN[] = $atributo;
                }
            }else if($atributo->getTipo() == Atributo::TIPO_INT || $atributo->getTipo() == Atributo::TIPO_STRING || $atributo->getTipo() == Atributo::TIPO_FLOAT)
            {
                $atributosComuns[] = $atributo;
            }else{
                $atributosObjetos[] = $atributo;
            }
        }
        
        
        
        $codigo = '
package com.'.strtolower($this->software->getNome()).'.dao;
    
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
    
import com.'.strtolower($this->software->getNome()).'.model.*;
    
/**
 * Classe feita para manipulação do objeto '.ucfirst($objeto->getNome()).'
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte
 *
 *
 */
public class ' . ucfirst($objeto->getNome()) . 'DAO extends DAO{';
        
        
        $codigo .= $this->update($objeto);
        $codigo .= $this->fetch($objeto);
        $codigo .= $this->insert($objeto);
        $codigo .= $this->delete($objeto);
        $codigo .= '
            

        	    
        	    
';
        
        foreach ($atributosComuns as $atributo) {
            
            $nomeDoAtributoMA = strtoupper(substr($atributo->getNome(), 0, 1)) . substr($atributo->getNome(), 1, 100);
            
            
            $codigo .= '
                
    public ArrayList<'.ucfirst($objeto->getNome()).'> fetchBy'.ucfirst($atributo->getNome()).'(' . ucfirst($objeto->getNome()). ' ' . lcfirst($objeto->getNome()) . ') {
        ArrayList<'.ucfirst($objeto->getNome()).'>lista = new ArrayList<'.ucfirst($objeto->getNome()).'>();';
            
            $id = $atributo->getNome();
            $codigo .= '
	    String sql = "';
            $sqlGerador = new SQLGerador($this->software);
            $codigo .= $sqlGerador->getSQLSelect($objeto);
            $codigo .= '"';
            if($atributo->getTipo() == Atributo::TIPO_STRING)
            {
                $codigo .=  '
                +" WHERE '.strtolower($objeto->getNome()).'.'.$id.' like \'%?%\'';
                
            }else if($atributo->getTipo() == Atributo::TIPO_INT || $atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                +" WHERE '.strtolower($objeto->getNome()).'.'.$id.' = ?';
            }
            $codigo .= ' LIMIT 1000";
    		PreparedStatement ps;
    		try {
    			ps = this.getConnection().prepareStatement(sql);';
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
                ps.setInt(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                ps.setFloat(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }
            else if($atributo->getTipo() == Atributo::TIPO_STRING){
                $codigo .= '
                ps.setString(1, '.$nomeDoObjeto.'.get'.$nomeDoAtributoMA.'());';
                
            }
            
            
            $codigo .= '
    			ResultSet resultSet = ps.executeQuery();
    			while(resultSet.next()){
                    ' . $nomeDoObjeto . ' = new ' . $nomeDoObjetoMA . '();';
            
            foreach ($atributosComuns as $atributo2) {
                
                $nomeDoAtributoMA = strtoupper(substr($atributo2->getNome(), 0, 1)) . substr($atributo2->getNome(), 1, 100);
                if($atributo2->getTipo() == Atributo::TIPO_INT){
                    $codigo .= '
                    '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getInt("'.$atributo2->getNome().'"));';
                }else if($atributo2->getTipo() == Atributo::TIPO_FLOAT)
                {
                    $codigo .= '
                    '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getFloat("'.$atributo2->getNome().'"));';
                }
                else if($atributo2->getTipo() == Atributo::TIPO_STRING)
                {
                    $codigo .= '
	               '.$nomeDoObjeto.'.set'.$nomeDoAtributoMA.'( resultSet.getString("'.$atributo2->getNome().'"));';
                }
                
                
            }
            foreach($atributosObjetos as $atributoObjeto){
                
                foreach($this->software->getObjetos() as $objeto2){
                    if($objeto2->getNome() == $atributoObjeto->getTipo()){
                        foreach($objeto2->getAtributos() as $atributo3){
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . $atributo3->getNomeSnakeCase().'_'.$atributoObjeto->getTipoSnakeCase().'_'.$atributoObjeto->getNomeSnakeCase() . '"));';
                                
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . $atributo3->getNomeSnakeCase().'_'.$atributoObjeto->getTipoSnakeCase().'_'.$atributoObjeto->getNomeSnakeCase() . '"));';
                                
                            }
                            else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                    ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . $atributo3->getNomeSnakeCase().'_'.$atributoObjeto->getTipoSnakeCase().'_'.$atributoObjeto->getNomeSnakeCase() . '"));';
                                
                            }
                            
                            
                        }
                        break;
                    }
                }
                
            }
            $codigo .= '
                
    				lista.add(' . $nomeDoObjeto . ');
    			}
                return lista;
    		} catch (SQLException e) {
    			// TODO Auto-generated catch block
    			e.printStackTrace();
                return null;
    		}
    				    
	}';
            
            
        }
        
        foreach($atributosNN as $atributo){
            $codigo .= '
    public '.ucfirst($objeto->getNome()).' fetch'.ucfirst($atributo->getNome()).'('.ucfirst($objeto->getNome()).' '.strtolower($objeto->getNome()).')
    {
        int id = '.strtolower($objeto->getNome()).'.getId();
        String sql = "SELECT ';
            $listaCampos = array();
            foreach($this->software->getObjetos() as $obj){
                if($obj->getNome() == $atributo->getTipoDeArray())
                {
                    $i = 0;
                    foreach($obj->getAtributos() as $atr){
                        
                        $i++;
                        $listaCampos[] = $atributo->getTipoDeArraySnakeCase().'.'.$atr->getNomeSnakeCase().' as '. $atr->getNomeSnakeCase().'_'.$atributo->getTipoDeArraySnakeCase();
                    }
                }
            }
            $codigo .= implode(', ', $listaCampos);
            $codigo .= ' FROM '.$objeto->getNomeSnakeCase().'_'.$atributo->getTipoDeArraySnakeCase().' INNER JOIN '.$atributo->getTipoDeArraySnakeCase().' ON  '.$objeto->getNomeSnakeCase().'_'.$atributo->getTipoDeArraySnakeCase().'.id_'.$atributo->getTipoDeArraySnakeCase().' = '.$atributo->getTipoDeArraySnakeCase().'.id WHERE '.$objeto->getNomeSnakeCase().'_'.$atributo->getTipoDeArraySnakeCase().'.id_'.$objeto->getNomeSnakeCase().' = "+id;';
            
            
            $codigo .= '
        PreparedStatement ps;
        try {
            ps = this.getConnection().prepareStatement(sql);
			ResultSet resultSet = ps.executeQuery();
    		while(resultSet.next()){
                '.ucfirst(explode(' ', $atributo->getTipo())[2]).' '.strtolower(explode(' ', $atributo->getTipo())[2]).' = new '.ucfirst(explode(' ', $atributo->getTipo())[2]).'();';
            foreach($this->software->getObjetos() as $obj){
                if(strtolower($obj->getNome()) == strtolower(explode(' ', $atributo->getTipo())[2]))
                {
                    foreach($obj->getAtributos() as $atr){
                        
                        
                        if($atr->tipoListado())
                        {
                            
                            
                            if($atr->getTipo() == Atributo::TIPO_INT)
                            {
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.ucfirst($atr->getNome()).'( resultSet.getInt("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                            }
                            else if($atr->getTipo() == Atributo::TIPO_FLOAT)
                            {
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.ucfirst($atr->getNome()).'( resultSet.getFloat("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                            }
                            else if($atr->getTipo() == Atributo::TIPO_STRING)
                            {
                                
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.ucfirst($atr->getNome()).'( resultSet.getString("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                                
                            }else{
                                $codigo .= '
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'.set'.ucfirst($atr->getNome()).'( resultSet.getString("'. $atr->getNome().'_'.strtolower($atributo->getTipoDeArray()).'"));';
                                
                            }
                            
                        }else if(substr($atr->getTipo(), 0, 6) == 'Array '){
                            
                            $codigo .= '
                '.ucfirst(explode(' ', $atributo->getTipo())[2]).'DAO '.strtolower(explode(' ', $atributo->getTipo())[2]).'Dao = new '.ucfirst(explode(' ', $atributo->getTipo())[2]).'DAO($this->getConnection());
                '.strtolower(explode(' ', $atributo->getTipo())[2]).'Dao.buscar'.ucfirst($atr->getNome()).'($'.strtolower(explode(' ', $atributo->getTipo())[2]).');';
                            //$objetoDao->buscar
                        }
                        
                    }
                    $codigo .= '';
                    break;
                }
            }
            
            $codigo .= '
                '.strtolower($objeto->getNome()).'.add'.ucfirst(explode(' ', $atributo->getTipo())[2]).'('.strtolower(explode(' ', $atributo->getTipo())[2]).');
                    
                    
                    
            }
            return '.strtolower($objeto->getNome()).';
		} catch (SQLException e) {
			e.printStackTrace();
            return null;
		}
    }
';
            
            
            
            
            
            
            
            $codigo .= '
                
	public boolean inserir'.ucfirst(explode(" ", $atributo->getTipo())[2]).'('. $nomeDoObjetoMA . ' ' . $nomeDoObjeto . ', '.ucfirst(explode(" ", $atributo->getTipo())[2]).' '.strtolower(explode(" ", $atributo->getTipo())[2]).'){
        int id'.ucfirst($objeto->getNome()).' =  ' . $nomeDoObjeto.'.getId();
        int id'.ucfirst(explode(' ', $atributo->getTipo())[2]).' = '.strtolower(explode(" ", $atributo->getTipo())[2]).'.getId();
		String sql = "INSERT INTO '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).'(';
            $codigo .= ' id'.strtolower($objeto->getNome()).', id'.strtolower(explode(' ', $atributo->getTipo())[2]).')';
            $codigo .= ' VALUES (?, ?)";';
            $codigo .= '
                
		try {
                
			PreparedStatement ps = this.getConnection().prepareStatement(sql);
            ps.setInt(1, id'.ucfirst($objeto->getNome()).');
            ps.setInt(2, id'.ucfirst(explode(' ', $atributo->getTipo())[2]) . ');
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
	}
                
                
                
	public boolean remover'.ucfirst(explode(" ", $atributo->getTipo())[2]).'('. $nomeDoObjetoMA . ' ' . $nomeDoObjeto . ', '.ucfirst(explode(" ", $atributo->getTipo())[2]).' '.strtolower(explode(" ", $atributo->getTipo())[2]).'){
        int id'.ucfirst($objeto->getNome()).' =  ' . $nomeDoObjeto.'.getId();
        int id'.ucfirst(explode(' ', $atributo->getTipo())[2]).' = '.strtolower(explode(" ", $atributo->getTipo())[2]).'.getId();
		String sql = "DELETE FROM  '.strtolower($objeto->getNome()).'_'.strtolower(explode(' ', $atributo->getTipo())[2]).' WHERE ';
            $codigo .= ' id'.strtolower($objeto->getNome()).' = ?';
            $codigo .= ' AND id'.strtolower(explode(' ', $atributo->getTipo())[2]).' = ? ";';
            
            $codigo .= '
                
		try {
                
			PreparedStatement ps = this.getConnection().prepareStatement(sql);
            ps.setInt(1, id'.ucfirst($objeto->getNome()).');
            ps.setInt(2, id'.ucfirst(explode(' ', $atributo->getTipo())[2]) . ');
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			e.printStackTrace();
			return false;
		}
	}
                
                
';
        }
        
        $codigo .= '
            
            
}';
        
        
        $caminho = ucfirst($objeto->getNome()).'DAO.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $codigo;
    }
    private function update(Objeto $objeto){
        $codigo = '';
        $codigo = '';
        $atributosComuns = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->tipoListado()) {
                $atributosComuns[] = $atributo;
            }
        }
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
        
        $codigo .= '
            
            
    public boolean update('.ucfirst($objeto->getNome()).' '.lcfirst($objeto->getNome()).')
    {
		PreparedStatement ps;';
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->getIndice() == Atributo::INDICE_PRIMARY){
                $codigo .= '
        int id = '.lcfirst($objeto->getNome()).'.get'.ucfirst ($atributo->getNome()).'();';
                
            }else if($atributo->tipoListado()){
                $codigo .= '
        '.$atributo->getTipoJava().' '.lcfirst($atributo->getNome()).' = '.lcfirst($objeto->getNome()).'.get'.ucfirst($atributo->getNome()).'();';
            }
        }
        $codigo .= '
            
        String sql = "UPDATE '.$objeto->getNomeSnakeCase().'"
                +"SET"
                ';
        $listaAtributo = array();
        foreach ($atributosComuns as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            if(substr($atributo->getTipo(), 0, 6) == 'Array '){
                continue;
            }
            $listaAtributo[] = $atributo;
        }
        $i = 0;
        foreach ($listaAtributo as $atributo) {
            $i ++;
            $codigo .= '+"'.$atributo->getNomeSnakeCase().' = ?';
            if ($i != count($listaAtributo)) {
                $codigo .= ',"
                ';
            }else{
                $codigo .= '"';
            }
        }
        $codigo .= '
                +"WHERE '.$objeto->getNomeSnakeCase().'.id ="+id+";";';
        
        $codigo .= '
		try {
			ps = this.getConnection().prepareStatement(sql);';
        $i = 1;
        foreach ($listaAtributo as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
            ps.setInt('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
            ps.setFloat('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_STRING){
                $codigo .= '
            ps.setString('.$i.', ' . $atributo->getNome() . ');';
                
            }else if($atributo->getTipo() == Atributo::TIPO_DATE || $atributo->getTipo() == Atributo::TIPO_DATE_TIME){
                $codigo .= '
            ps.setString('.$i.', ' . $atributo->getNome() . ');';
                
            }
            
            $i++;
        }
        $codigo .= '
            
			ps.executeUpdate();
            return true;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
            return false;
		}
            
            
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

	public ArrayList<'.ucfirst($objeto->getNome()).'> fetch() {
		ArrayList<'.ucfirst($objeto->getNome()).'>lista = new ArrayList<'.ucfirst($objeto->getNome()).'>();
		String sql = "';
        $sqlGerador = new SQLGerador($this->software);
        $sql = $sqlGerador->getSQLSelect($objeto);
        $codigo .= $sql;
        $codigo .= ' LIMIT 1000";
            
		PreparedStatement ps;
		try {
			ps = this.getConnection().prepareStatement(sql);
			ResultSet resultSet = ps.executeQuery();
			while(resultSet.next()){
				' . ucfirst($objeto->getNome()) . ' ' . lcfirst($objeto->getNome()). ' = new ' . ucfirst($objeto->getNome()). '();';
        foreach ($atributosComuns as $atributo) {
            
            if($atributo->getTipo() == Atributo::TIPO_INT){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome()) . '( resultSet.getInt("' . $atributo->getNomeSnakeCase() . '"));';
            }else if($atributo->getTipo() == Atributo::TIPO_FLOAT){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome())  . '( resultSet.getFloat("' . $atributo->getNomeSnakeCase() . '"));';
            }else if($atributo->getTipo() == Atributo::TIPO_STRING || $atributo->getTipo() == Atributo::TIPO_DATE_TIME || $atributo->getTipo() == Atributo::TIPO_DATE){
                $codigo .= '
                ' . lcfirst($objeto->getNome()) . '.set' . ucfirst($atributo->getNome())  . '( resultSet.getString("' . $atributo->getNomeSnakeCase() . '"));';
            }
            
        }
        foreach($atributosObjetos as $atributoObjeto){
            
            foreach($this->software->getObjetos() as $objeto2){
                if($objeto2->getNome() == $atributoObjeto->getTipo()){
                    foreach($objeto2->getAtributos() as $atributo3){
                        if($atributo3->getIndice() == Atributo::INDICE_PRIMARY){
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }
                            
                            
                        }
                        else
                        {
                            
                            if($atributo3->getTipo() == Atributo::TIPO_INT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getInt("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                                
                            }else if($atributo3->getTipo() == Atributo::TIPO_FLOAT){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getFloat("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }else if($atributo3->getTipo() == Atributo::TIPO_STRING){
                                $codigo .= '
                ' . $nomeDoObjeto . '.get' . ucfirst($atributoObjeto->getNome()) . '().set'.ucfirst($atributo3->getNome()).'( resultSet.getString("' . strtolower($atributo3->getNome()).'_'.strtolower($atributoObjeto->getTipo()).'_'.strtolower($atributoObjeto->getNome()) . '" ));';
                            }
                            
                        }
                        
                    }
                    break;
                }
            }
            
        }
        $codigo .= '
            
        
				lista.add(' . $nomeDoObjeto . ');
			}
            return lista;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
            return null;
		}
    				    
	}

';
        
        
        
        return $codigo;
    }
    
    private function insert(Objeto $objeto)
    {
        $codigo = '
	public boolean insert(' . ucfirst($objeto->getNome()). ' ' . lcfirst($objeto->getNome()). '){
	    
        String sql = "INSERT into '.$objeto->getNomeSnakeCase().'(';
        $listaAtributos = array();
        $listaAtributosVar = array();
        foreach ($objeto->getAtributos() as $atributo)
        {
            if($atributo->isPrimary()){
                continue;
            }
            if($atributo->tipoListado()){
                $listaAtributos[] = $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = '?';
                
            }else if($atributo->isObjeto()){
                $listaAtributos[] = 'id_' . $atributo->getNomeSnakeCase();
                $listaAtributosVar[] = '?';
                
            }else{
                continue;
            }
        }
        $codigo .= implode(", ", $listaAtributos);
        $codigo .= ') VALUES (';
        $codigo .= implode(", ", $listaAtributosVar);
        $codigo .= ');";';
       
        $codigo .= '
            
		try {
            
			PreparedStatement ps = this.getConnection().prepareStatement(sql);';
        
        $i = 0;
        foreach ($objeto->getAtributos() as $atributo) {
            
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }else{
                $i++;
            }
            if($atributo->tipoListado()){
                $codigo .= '
            ps.set'.ucfirst($atributo->getTipoJava()).'('.$i.', '.lcfirst($objeto->getNome()).'.get'.ucfirst($atributo->getNome()).'());';
        
            }else if($atributo->isObjeto()){
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
            ps.setInt('.$i.', '.lcfirst($objeto->getNome()).'.get'.ucfirst($atributo->getNome()).'().get'.$strCampoPrimary.'());';
                
                
            }
            
        }
        
        $codigo .= '
            
			ps.executeUpdate();
			return true;
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			return false;
		}
    }
            
    
';
        
        return $codigo;
        
    }
    
    
}





?>