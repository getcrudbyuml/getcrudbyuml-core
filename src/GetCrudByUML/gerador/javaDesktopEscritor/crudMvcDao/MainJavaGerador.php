<?php 

namespace GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;

use GetCrudByUML\model\Software;

class MainJavaGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software){
        $gerador = new MainJavaGerador($software);
        return $gerador->gerarCodigo();
    }
    public function __construct(Software $software){
        $this->software = $software;
    }
    
    public function gerarCodigo(){
        $this->geraMain();
        return $this->listaDeArquivos;
    }
    private function criarArquivos(){
        
        $caminho = $this->diretorio.'/AppDesktopJava/'.$this->software->getNomeSimples().'/src/main/java/com/'.strtolower($this->software->getNomeSimples()).'/main';
        if(!file_exists($caminho)) {
            mkdir($caminho, 0777, true);
        }
        
        foreach ($this->listaDeArquivos as $path => $codigo) {
            if (file_exists($path)) {
                unlink($path);
            }
            $file = fopen($path, "w+");
            fwrite($file, stripslashes($codigo));
            fclose($file);
        }
    }
    
    
    public function geraMain(){
        $codigo  = 'package com.'.strtolower($this->software->getNomeSimples()).'.main;
import java.util.Scanner;';
        foreach($this->software->getObjetos() as $objeto){
            
            $codigo .= '
import com.escola.controller.'.ucfirst($objeto->getNome()).'Controller;';
        }
        $codigo .= '

public class Main {
	private static Scanner scanner;
	public static void main(String[] args) {
        int comando;
		while(true) {
			System.out.println("Digite um comando: ");
			System.out.println("0 - Sair da aplicação");';
        $arrStrObjeto = array();
        $i = 0;
        foreach ($this->software->getObjetos() as $objeto){
            $i++;
            $arrStrObjeto[] = '
            case '.$i.': 
                '.ucfirst($objeto->getNome()).'Controller '.lcfirst($objeto->getNome()).'Controller = new '.ucfirst($objeto->getNome()).'Controller();
                '.lcfirst($objeto->getNome()).'Controller.main();
                break;';
            $codigo .= '
            System.out.println("'.$i.' - '.$objeto->getNomeTextual().'");';
            
        }
        $codigo .= '
			scanner = new Scanner(System.in);
			comando = scanner.nextInt();
            switch (comando) {
			case 0:
				System.out.println("Fim da aplicação.");
				System.exit(0);
				break;
			'.implode('', $arrStrObjeto).'

			default:
                System.out.println("Comando não encontrado.");
				break;
			}
		}
		
	}
            
}
            
';
        $caminho = 'Main.java';
        $this->listaDeArquivos[$caminho] = $codigo;
        return $codigo;
        
    }
}






?>