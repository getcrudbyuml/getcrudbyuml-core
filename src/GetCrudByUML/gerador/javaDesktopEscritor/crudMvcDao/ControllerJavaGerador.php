<?php
namespace GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;

use GetCrudByUML\model\Software as Software;
use GetCrudByUML\model\Objeto as Objeto;

class ControllerJavaGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software){
        $gerador = new ControllerJavaGerador($software);
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
    
    private function geraControllers(Objeto $objeto)
    {
        $codigo = '';
        $codigo = '
package com.'.strtolower($this->software->getNome()).'.controller;
import java.util.Scanner;
import java.util.ArrayList;
import com.escola.model.'.ucfirst($objeto->getNome()).';
import com.escola.dao.'.ucfirst($objeto->getNome()).'DAO;

/**
 * Classe de visao para ' . ucfirst($objeto->getNome()) . '
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 *
 */
public class ' . ucfirst($objeto->getNome()) . 'Controller {
    private '.ucfirst($objeto->getNome()).'DAO dao;
	private static Scanner scanner;
    public '.ucfirst($objeto->getNome()).'Controller(){
        this.dao = new '.ucfirst($objeto->getNome()).'DAO();
    }
    public void main(){        
        int comando;
        while(true){
			System.out.println("Digite um comando: ");
			System.out.println("0 - Voltar");
            System.out.println("1 - Listar");
            System.out.println("2 - Inserir");
            System.out.println("3 - Deletar");
			scanner = new Scanner(System.in);
			comando = scanner.nextInt();
            switch (comando) {
			case 0:
				return;
            case 1: 
                ArrayList<'.ucfirst($objeto->getNome()).'> lista = this.dao.fetch();
                for ('.ucfirst($objeto->getNome()).' '.lcfirst($objeto->getNome()).': lista) 
                {
					System.out.println('.lcfirst($objeto->getNome()).'.toString());
				}
                break;
            case 2:
                System.out.println("Não Implementado. ");    
                break;
            case 3:
                System.out.println("Não Implementado. ");
                break;
			default:
                System.out.println("Comando não encontrado.");
				break;
			}
        }
	   
        

    }
    

}';
        $caminho = ucfirst($objeto->getNome()).'Controller.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        
    }
    
}


?>