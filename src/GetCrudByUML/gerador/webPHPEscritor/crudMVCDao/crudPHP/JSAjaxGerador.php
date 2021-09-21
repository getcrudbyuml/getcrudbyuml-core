<?php

namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;
use GetCrudByUML\model\Atributo;

class JSAjaxGerador
{

    private $software;

    private $listaDeArquivos;

    private $diretorio;

    public static function main(Software $software)
    {
        $gerador = new JSAjaxGerador($software);
        return $gerador->geraCodigo();
    }

    public function __construct(Software $software)
    {
        $this->software = $software;
        
    }

    /**
     * Selecione uma linguagem
     *
     * @param int $linguagem
     */
    public function geraCodigo()
    {
        foreach($this->software->getObjetos() as $objeto){
            $this->geraModel($objeto);
        }
        return $this->listaDeArquivos;
        
        
    }
   
    private function geraModel(Objeto $objeto)
    {
        $possuiCampoArquivo = false;
        foreach($objeto->getAtributos() as $atributo){
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $possuiCampoArquivo = true;
                break;
            }
        }
        $codigo = '

$(document).ready(function(e) {
	$("#insert_form_'.$objeto->getNomeSnakeCase().'").on(\'submit\', function(e) {
		e.preventDefault();
        $(\'#modalAdd'.$objeto->getNome().'\').modal(\'hide\');
        ';
        if($possuiCampoArquivo){
            $codigo .= '
        var dados = new FormData(this);
        ';
        }else{
            $codigo .= '
		var dados = jQuery( this ).serialize();
        ';
        }
        
        $codigo .= '
		jQuery.ajax({
            type: "POST",
            url: "index.php?ajax=' .$objeto->getNomeSnakeCase() . '",
            data: dados,
            success: function( data )
            {
            

            	if(data.split(":")[1] == \'sucesso\'){
            		
            		$("#botao-modal-resposta").click(function(){
            			window.location.href=\'?page=' .$objeto->getNomeSnakeCase() . '\';
            		});
            		$("#textoModalResposta").text("' .$objeto->getNomeTextual() . ' enviado com sucesso! ");                	
            		$("#modalResposta").modal("show");
            		
            	}
            	else
            	{
            		
                	$("#textoModalResposta").text("Falha ao inserir ' .$objeto->getNomeTextual() . ', fale com o suporte. ");                	
            		$("#modalResposta").modal("show");
            	}
';
        
        if($possuiCampoArquivo){
            $codigo .= '
            },
            cache: false,
            contentType: false,
            processData: false,
            xhr: function() { // Custom XMLHttpRequest
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) { // Avalia se tem suporte a propriedade upload
                    myXhr.upload.addEventListener(\'progress\', function() {
                    /* faz alguma coisa durante o progresso do upload */
                    }, false);
                }
                return myXhr;';
        }
        
        
        $codigo .= '
            }
        });
		
		
	});
	
	
});
   
';
        
        
        $caminho = $objeto->getNomeSnakeCase().'.js';
        $this->listaDeArquivos[$caminho] = $codigo;
        
    }

}

?>