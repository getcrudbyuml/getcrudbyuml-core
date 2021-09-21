<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class DAOCustomGerador
{

    private $software;

    private $listaDeArquivos;

    private $diretorio;

    public static function main(Software $software)
    {
        $gerador = new DAOCustomGerador($software);
        return $gerador->geraCodigo();
    }

    public function __construct(Software $software)
    {
        $this->software = $software;
    }

    private function geraCodigo()
    {
        
        foreach($this->software->getObjetos() as $objeto){
            $this->geraDAOs($objeto);
        }
        
        return $this->listaDeArquivos;
        
    }
   
    private function geraDAOs(Objeto $objeto)
    {
        $codigo = '';
        $codigo .= '<?php
                
/**
 * Customize sua classe
 *
 */


namespace '.$this->software->getNome().'\\\\custom\\\\dao;
use '.$this->software->getNome().'\\\\dao\\\\'.ucfirst($objeto->getNome()).'DAO;


class  ' . ucfirst($objeto->getNome()) . 'CustomDAO extends ' . ucfirst($objeto->getNome()) . 'DAO {
    

';

        $codigo .= '
}';

        $caminho = ucfirst($objeto->getNome()).'CustomDAO.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
}

?>