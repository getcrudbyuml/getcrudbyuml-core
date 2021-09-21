<?php 


namespace  GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ViewJavaGerador{
    private $software;
    private $listaDeArquivos;
    private $diretorio;
    
    public static function main(Software $software){
        $gerador = new ViewJavaGerador($software);
        return $gerador->gerarCodigo();
    }
    public function __construct(Software $software){
        $this->software = $software;
    }
    
    public function gerarCodigo(){
        foreach ($this->software->getObjetos() as $objeto){
            $this->geraViewsJava($objeto, $this->software);
        }
        return $this->listaDeArquivos;
        
    }
    
    public function geraViewsJava(Objeto $objeto, Software $software)
    {
        $codigo = '';
        $codigo = '
package com.'.strtolower($this->software->getNome()).'.view;

import java.awt.BorderLayout;
import javax.swing.JFrame;
import javax.swing.JPanel;
import javax.swing.JTextField;
import javax.swing.JLabel;
import javax.swing.JButton;
import javax.swing.border.EmptyBorder;

/**
 * Classe de visao para ' . ucfirst($objeto->getNome()) . '
 * @author Jefferson Uchôa Ponte <jefponte@gmail.com>
 *
 */

public class ' . ucfirst($objeto->getNome()) . 'View extends JFrame {

    /**
	 * 
	 */
	private static final long serialVersionUID = 1L;
	
    private JPanel contentPane;
    private JButton btnSubmit;
    public JButton getBtnSubmit(){
        return this.btnSubmit;
    }
    public ' . ucfirst($objeto->getNome()) . 'View() {
        setTitle("' . ucfirst($objeto->getNome()) . '");
    	setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
    	setBounds(100, 100, 450, 300);
    	contentPane = new JPanel();
    	contentPane.setBorder(new EmptyBorder(5, 5, 5, 5));
    	contentPane.setLayout(new BorderLayout(0, 0));
    	setContentPane(contentPane);
        this.getContentPane().setLayout(null);

    	';
        $i = 0;
        foreach($objeto->getAtributos() as $atributo){
            
            if($atributo->tipoListado() && !$atributo->isPrimary()){
                $codigo .= '
        JTextField textField'.ucfirst($atributo->getNome()).' = new JTextField();
    	textField'.ucfirst($atributo->getNome()).'.setBounds(200, '.(12+$i*28).', 86, 20);
    	this.getContentPane().add(textField'.ucfirst($atributo->getNome()).');
    	textField'.ucfirst($atributo->getNome()).'.setColumns(100);
        
    	JLabel label'.ucfirst($atributo->getNome()).' = new JLabel("'.ucfirst($atributo->getNomeTextual()).'");
        label'.ucfirst($atributo->getNome()).'.setBounds(65, '.(12+$i*28).', 150, 14);
        this.getContentPane().add(label'.ucfirst($atributo->getNome()).');

';
                $i++;
            }
            
        }
        $codigo .= '
		         
        btnSubmit = new JButton("submit");
        btnSubmit.setBounds(200, '.(12+$i*28).', 86, 20);
        this.getContentPane().add(btnSubmit);

';
        
        $codigo .= '


    }


}';
        
        $caminho = ucfirst($objeto->getNome()).'View.java';
        
        $this->listaDeArquivos[$caminho] = $codigo;
        return $codigo;
    }
}





?>