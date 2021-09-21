<?php


namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\apiRestPHP;
use GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\DAOGerador;
use GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\ModelGerador;
use GetCrudByUML\gerador\sqlGerador\DBGerador;
use GetCrudByUML\model\Software;
use PDO;


class EscritorDeSoftware
{

    

    private $software;
    
    private $diretorio;
    
    public function __construct(Software $software, $diretorio)
    {
        $this->diretorio = $diretorio;
        $this->software = $software;
    }
    
    public static function main(Software $software, $diretorio)
    {
        $escritor = new EscritorDeSoftware($software, $diretorio);
        $escritor->gerarCodigoPHP();
    }
    
    private function criarArquivos($arquivos, $diretorio){
        
        if(!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        foreach ($arquivos as $path => $codigo) {
            $file = fopen($diretorio.'/'.$path, "w+");
            fwrite($file, stripslashes($codigo));
            fclose($file);
        }
    }
    public function gerarCodigoPHP()
    {
        if(count($this->software->getObjetos()) == 0){
            echo "Não existem Objetos. Adicione pelo menos um objeto.";
            return;
        }
        foreach($this->software->getObjetos() as $objeto){
            if(count($objeto->getAtributos()) == 0){
                echo "Existe pelo menos um objeto sem atributos. Adicione atributos.";
                return;
            }
        }
        
        
        $this->diretorio .= '/apiPHP';
        $diretorio = $this->diretorio.'/'.$this->software->getNomeSimples();
        
        $this->criarArquivos(ModelGerador::main($this->software), $diretorio.'/'.'model');
        $this->criarArquivos(DAOGerador::main($this->software), $diretorio.'/dao');
        $this->criarArquivos(ControllerRestGerador::main($this->software), $diretorio.'/controller');
        $this->criarArquivos(IndexAPIGerador::main($this->software), $diretorio.'/..');
        
        $this->criarArquivos(DBGerador::main($this->software), $diretorio.'/../..');
        
        $this->criarArquivos(IniAPIRest::main($this->software), $diretorio.'/../..');
        
        $dbGerador = new DBGerador($this->software);
        $codigo = $dbGerador->geraBancoSqlite();
        $bdNome = $this->diretorio . '/../' . $this->software->getNomeSnakeCase() . '.db';
        if (file_exists($bdNome)) {
            unlink($bdNome);
        }
        $pdo = new PDO('sqlite:' . $bdNome);
        $pdo->exec($codigo);
        
        
        

    }
}