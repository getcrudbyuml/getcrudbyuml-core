<?php



namespace GetCrudByUML\gerador\sqlGerador;
use GetCrudByUML\model\Software;
use GetCrudByUML\model\Atributo;




class DBGerador
{

    private $listaDeArquivos;

    private $software;


    public static function main(Software $software)
    {
        $gerador = new DBGerador($software);
        return $gerador->gerarCodigo();
    }

    public function __construct(Software $software)
    {
        $this->software = $software;
    }

    public function getListaDeArquivos()
    {
        return $this->listaDeArquivos;
    }

    public function gerarCodigo()
    {
        $this->geraBancoPG();
        $this->geraBancoMysql();
        $this->geraBancoSqlite();

        return $this->listaDeArquivos;
    }

   
    public function geraBancoPG()
    {
        $objetosNN = array();
        $objetos1N = array();
        $codigo = '';
        foreach ($this->software->getObjetos() as $objeto) {
            
            $campos = array();
            foreach ($objeto->getAtributos() as $atributo) 
            {
                if ($atributo->getIndice() == Atributo::INDICE_PRIMARY && $atributo->tipoListado()) {
                    $campos[] = $atributo->getNomeSnakeCase() . ' serial NOT NULL';
                    $campos[] = 'CONSTRAINT pk_' .$objeto->getNomeSnakeCase() .' PRIMARY KEY (' . $atributo->getNomeSnakeCase() . ')';
                } else if ($atributo->tipoListado()) {
                    $campos[] = $atributo->getNomeSnakeCase() . ' ' . $atributo->getTipoPostgres();
                } else if ($atributo->isArrayNN()) 
                {
                    $objetosNN[] = $objeto;
                } else if ($atributo->isArray1N()) {
                    $objetos1N[] = $objeto;
                } else if ($atributo->isObjeto()) {
                    $campos[] = 'id' . '_' . $atributo->getNomeSnakeCase() . ' integer NOT NULL';
                }
            }
            
            $codigo .= '
CREATE TABLE ' . $objeto->getNomeSnakeCase();
            $codigo .= " (\n        ";
            $codigo .= implode(", \n        ", $campos);
            $codigo .= "\n);\n";
        }
        foreach ($objetosNN as $objeto) {

            // explode(' ', $string);
            foreach ($objeto->getAtributos() as $atributo) {
                if ($atributo->isArrayNN()) {
                    $codigo .= '
CREATE TABLE ' . $objeto->getNomeSnakeCase() . '_' . strtolower(explode(" ", $atributo->getTipoSnakeCase())[2]);
                    $codigo .= '(
    id serial NOT NULL,
    id_' . $objeto->getNomeSnakeCase() . ' integer NOT NULL,
    id_' . $atributo->getArrayTipoSnakeCase() . ' integer NOT NULL,
    CONSTRAINT pk_' . $objeto->getNomeSnakeCase() . '_' . $atributo->getArrayTipoSnakeCase() . '_id 
    PRIMARY KEY (id),
    CONSTRAINT fk_' . $objeto->getNomeSnakeCase() . '_id FOREIGN KEY (id_' . $objeto->getNomeSnakeCase() . ') 
    REFERENCES ' . $objeto->getNomeSnakeCase() . ' (id) 
    MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT fk_' . $atributo->getArrayTipoSnakeCase() . '_id 
    FOREIGN KEY (id_' . $atributo->getArrayTipoSnakeCase() . ') REFERENCES ' . $atributo->getArrayTipoSnakeCase() . ' (id) 
    MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
);';
                }
            }
        }
        // Adicionar outras chaves estrangeiras.
        foreach ($this->software->getObjetos() as $objeto) {
            foreach ($objeto->getAtributos() as $atributo) {
                if ($atributo->isObjeto()) {
                    $objetoDoAtributo = null;
                    foreach ($this->software->getObjetos() as $objeto2) {
                        if ($atributo->getTipo() == $objeto2->getNome()) {
                            $objetoDoAtributo = $objeto2;
                            break;
                        }
                    }
                    if($objetoDoAtributo == null){
                        if($atributo->getTipo() == ""){
                            $tipo = $atributo->getNome()." Tipo: Vazio ";
                        }else{
                            $tipo = $atributo->getNome()."Tipo: ".$atributo->getTipo();
                        }
                        
                        echo "<br>Atributo do tipo ".$tipo.' não pode ser criada.<br>';
                        break;
                    }
                    
                    foreach ($objetoDoAtributo->getAtributos() as $atributo3) {
                        if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                            $atributoPrimary = $atributo3;
                            break;
                        }
                    }
                    $codigo .= '

ALTER TABLE ' . $objeto->getNomeSnakeCase() . ' 
    ADD CONSTRAINT fk_' . $objeto->getNomeSnakeCase() . '_' . $atributo->getNomeSnakeCase() . ' FOREIGN KEY (id_' . $atributo->getTipoSnakeCase() . ')
    REFERENCES ' . $atributo->getTipoSnakeCase() . ' (' . $atributoPrimary->getNomeSnakeCase() . ');
';
                }
            }
        }

        foreach ($objetos1N as $objeto) {
            $atributoPK = null;
            foreach ($objeto->getAtributos() as $atributo) {
                if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                    $atributoPK = $atributo;
                }
            }
            foreach ($objeto->getAtributos() as $atributo) {

                if ($atributo->isArray1N()) {
                    if ($atributoPK != null) {

                        $codigo .= '
ALTER TABLE ' . $atributo->getArrayTipoSnakeCase() .
                        ' ADD COLUMN  ' . $atributoPK->getNomeSnakeCase() . '_' . $objeto->getNomeSnakeCase() . '  integer ;';

                        $codigo .= '

ALTER TABLE ' . $atributo->getArrayTipoSnakeCase() . ' 
    ADD CONSTRAINT
    fk'. '_' . $objeto->getNomeSnakeCase() . '_' . $atributo->getNomeSnakeCase() . 
    ' FOREIGN KEY (' . $atributoPK->getNomeSnakeCase() . '_' . $objeto->getNomeSnakeCase() . ')
    REFERENCES ' . $objeto->getNomeSnakeCase() . ' (' . $atributoPK->getNomeSnakeCase() . ');
';
                    }
                }
            }
        }
        $path = 'database_pg.sql';
        $this->listaDeArquivos[$path] = $codigo;
        return $codigo;
    }
    

    /**
     * Ainda não está pronto. 
     * @return string
     */
    public function geraBancoMysql()
    {        $objetosNN = array();
    $objetos1N = array();
    $codigo = '';
    foreach ($this->software->getObjetos() as $objeto) {
        
        $campos = array();
        foreach ($objeto->getAtributos() as $atributo)
        {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY && $atributo->tipoListado()) {
                $campos[] = $atributo->getNomeSnakeCase() . '  INT NOT NULL AUTO_INCREMENT ';
                $campos[] = 'PRIMARY KEY (' .$atributo->getNomeSnakeCase() .')';
            } else if ($atributo->tipoListado()) {
                $campos[] = $atributo->getNomeSnakeCase() . ' ' . $atributo->getTipoMysql();
            } else if ($atributo->isArrayNN())
            {
                $objetosNN[] = $objeto;
            } else if ($atributo->isArray1N()) {
                $objetos1N[] = $objeto;
            } else if ($atributo->isObjeto()) {
                $campos[] = 'id' . '_' . $atributo->getNomeSnakeCase() . ' INT NOT NULL';
            }
        }
        
        $codigo .= '
CREATE TABLE IF NOT EXISTS ' . $objeto->getNomeSnakeCase();
        $codigo .= " (\n        ";
        $codigo .= implode(", \n        ", $campos);
        $codigo .= "\n)ENGINE = InnoDB;\n";
    }
    foreach ($objetosNN as $objeto) {
        
        // explode(' ', $string);
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isArrayNN()) {
                $codigo .= '
CREATE TABLE IF NOT EXISTS ' . $objeto->getNomeSnakeCase() . '_' . strtolower(explode(" ", $atributo->getTipoSnakeCase())[2]);
                $codigo .= '(
    id_' . $objeto->getNomeSnakeCase() . ' INT NOT NULL,
    id_' . $atributo->getArrayTipoSnakeCase() . ' INT NOT NULL,
    PRIMARY KEY (id_' . $objeto->getNomeSnakeCase() . ', id_' . $atributo->getArrayTipoSnakeCase() . '),
    CONSTRAINT fk_' . $objeto->getNomeSnakeCase() . '_id 
    FOREIGN KEY (id_' . $objeto->getNomeSnakeCase() . ')
    REFERENCES ' . $objeto->getNomeSnakeCase() . ' (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
    CONSTRAINT fk_' . $atributo->getArrayTipoSnakeCase() . '_id
    FOREIGN KEY (id_' . $atributo->getArrayTipoSnakeCase() . ')
    REFERENCES ' . $atributo->getArrayTipoSnakeCase() . ' (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)ENGINE = InnoDB;';
            }
        }
    }
    // Adicionar outras chaves estrangeiras.
    foreach ($this->software->getObjetos() as $objeto) {
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->isObjeto()) {
                $objetoDoAtributo = null;
                foreach ($this->software->getObjetos() as $objeto2) {
                    if ($atributo->getTipo() == $objeto2->getNome()) {
                        $objetoDoAtributo = $objeto2;
                        break;
                    }
                }
                if($objetoDoAtributo == null){
                    if($atributo->getTipo() == ""){
                        $tipo = $atributo->getNome()." Tipo: Vazio ";
                    }else{
                        $tipo = $atributo->getNome()."Tipo: ".$atributo->getTipo();
                    }
                    
                    echo "<br>Atributo do tipo ".$tipo.' não pode ser criada.<br>';
                    break;
                }
                
                foreach ($objetoDoAtributo->getAtributos() as $atributo3) {
                    if ($atributo3->getIndice() == Atributo::INDICE_PRIMARY) {
                        $atributoPrimary = $atributo3;
                        break;
                    }
                }
                $codigo .= '
ALTER TABLE ' . $objeto->getNomeSnakeCase() . '
    ADD CONSTRAINT fk_' . $objeto->getNomeSnakeCase() . '_' . $atributo->getNomeSnakeCase() . ' FOREIGN KEY (id_' . $atributo->getTipoSnakeCase() . ')
    REFERENCES ' . $atributo->getTipoSnakeCase() . ' (' . $atributoPrimary->getNomeSnakeCase() . ');
';
            }
        }
    }
    
    foreach ($objetos1N as $objeto) {
        $atributoPK = null;
        foreach ($objeto->getAtributos() as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                $atributoPK = $atributo;
            }
        }
        foreach ($objeto->getAtributos() as $atributo) {
            
            if ($atributo->isArray1N()) {
                if ($atributoPK != null) {
                    
                    $codigo .= '
ALTER TABLE ' . $atributo->getArrayTipoSnakeCase() .
' ADD COLUMN  ' . $atributoPK->getNomeSnakeCase() . '_' . $objeto->getNomeSnakeCase() . '  INT ;';
                    
                    $codigo .= '
                        
ALTER TABLE ' . $atributo->getArrayTipoSnakeCase() . '
    ADD CONSTRAINT
    fk'. '_' . $objeto->getNomeSnakeCase() . '_' . $atributo->getNomeSnakeCase() .
    ' FOREIGN KEY (' . $atributoPK->getNomeSnakeCase() . '_' . $objeto->getNomeSnakeCase() . ')
    REFERENCES ' . $objeto->getNomeSnakeCase() . ' (' . $atributoPK->getNomeSnakeCase() . ');
';
                }
            }
        }
    }
        $path = 'database_mysql.sql';
        $this->listaDeArquivos[$path] = $codigo;
        return $codigo;
    }
    public function geraBancoSqlite()
    {
        $objetosNN = array();

        
        
        $codigo = '';
        foreach ($this->software->getObjetos() as $objeto) {
            $codigo .= '
CREATE TABLE ' . $objeto->getNomeSnakeCase();
            $codigo .= " (\n";
            $i = 0;
            $atributosComuns = array();
            
            foreach ($objeto->getAtributos() as $atributo) {
                if ($atributo->isArrayNN()) {
                    
                    $objetosNN[] = $objeto;
                } else if ($atributo->isArray1N()) {

                } else {
                    
                    $atributosComuns[] = $atributo;
                }
            }
            foreach ($atributosComuns as $atributo) {
                $i ++;
                if ($atributo->tipoListado()) {
                    $codigo .= '    ' . $atributo->getNomeSnakeCase() . ' ' . $atributo->getTipoSqlite() . ' ';
                } else if ($atributo->isObjeto()) {
                    $codigo .= '    id_' . $atributo->getNomeSnakeCase() . ' INTEGER NOT NULL';
                }
                if ($atributo->getIndice() == Atributo::INDICE_PRIMARY && $atributo->getTipo() == Atributo::TIPO_INT) {
                    $codigo .= '    PRIMARY KEY AUTOINCREMENT';
                }
                if ($i >= count($atributosComuns)) {
                    $codigo .= "\n";
                    continue;
                }
                $codigo .= ",\n";
            }
            $codigo .= ");\n";
        }
        
        foreach ($objetosNN as $objeto) {
            
            // explode(' ', $string);
            foreach ($objeto->getAtributos() as $atributo) {
                if (substr($atributo->getTipo(), 0, 6) == 'Array ') {
                    $codigo .= '
CREATE TABLE ' . $objeto->getNomeSnakeCase() . '_' . strtolower(explode(" ", $atributo->getTipo())[2]);
                    $codigo .= '(
    id 	INTEGER PRIMARY KEY AUTOINCREMENT,
    id_' . $objeto->getNomeSnakeCase() . ' INTEGER,
    id_' . strtolower(explode(" ", $atributo->getTipo())[2]) . ' INTEGER
);';
                }
            }
        }
        foreach ($this->software->getObjetos() as $objeto) {
            $atributoPK = null;
            foreach ($objeto->getAtributos() as $atributo) {
                if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                    $atributoPK = $atributo;
                }
            }
            foreach ($objeto->getAtributos() as $atributo) {
                
                if ($atributo->isArray1N()) {
                    if ($atributoPK != null) {
                        
                        $codigo .= '
ALTER TABLE ' . $atributo->getArrayTipoSnakeCase() . ' ADD COLUMN  ' . $atributoPK->getNomeSnakeCase() . '_' . $objeto->getNomeSnakeCase() . '  INTEGER ;';
                    }
                }
            }
        }
        
        
        $path = 'database_sqlite.sql';
        $this->listaDeArquivos[$path] = $codigo;
        return $codigo;
        
    }
}

?>