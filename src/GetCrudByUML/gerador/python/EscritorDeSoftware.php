<?php


namespace GetCrudByUML\gerador\python;

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
    
    private function criarArquivos($arquivos, $diretorio, $sobrescrever = true){
        
        if(!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        foreach ($arquivos as $path => $codigo) {
            if(file_exists($diretorio.'/'.$path)){
                if($sobrescrever == false){
                    break;
                }
            }
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
        if($_SERVER['HTTP_HOST'] == 'localhost'){
            $diretorioSrc = 'crudPython';
        }else{
            $diretorioSrc = 'crudPython';
        }
        $diretorio = $this->diretorio;
        
        $this->criarArquivos(ModelGerador::main($this->software), $diretorio.'/'.$diretorioSrc.'/');
        $this->criarArquivos(MainPyGerador::main($this->software), $diretorio.'/'.$diretorioSrc.'/');
        $this->criarArquivos(DBGerador::main($this->software), $diretorio);
        $dbGerador = new DBGerador($this->software);
        $codigo = $dbGerador->geraBancoSqlite();
        $bdNome = $this->diretorio . '/' . $this->software->getNomeSnakeCase() . '.db';
        if (file_exists($bdNome)) {
            unlink($bdNome);
        }
        $pdo = new PDO('sqlite:' . $bdNome);
        $pdo->exec($codigo);
    }
}