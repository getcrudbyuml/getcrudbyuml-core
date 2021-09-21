<?php

namespace GetCrudByUML\gerador\javascript\clienteRest;

use GetCrudByUML\model\Software;

class IndexGerador
{

    private $software;
    
    private $listaDeArquivos;
    
    public static function main(Software $software){
        $gerador = new IndexGerador($software);
        return $gerador->gerarCodigo();
    }
    
    
    public function __construct(Software $software)
    {
        $this->software = $software;
        $this->listaDeArquivos = array();
    }
    public function gerarCodigo(){
        $this->geraIndex();
        return $this->listaDeArquivos;
        
    }
    

    
    public function geraIndex(){
        
        if (! count($this->software->getObjetos())) {
            return;
        }
        $codigo = '<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>' . $this->software->getNome() . '</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">'.$this->software->getNome().'</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Alterna navegação">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">';
        
        
        
        foreach ($this->software->getObjetos() as $objeto) {
            
            $codigo .= '<a class="nav-item nav-link" href="?page=' . $objeto->getNomeSnakeCase() . '">' . $objeto->getNomeTextual() . '</a>';
        }
        
        $codigo .= '
            
    </div>
  </div>
</nav>
	<main role="main">
            
      <section class="jumbotron text-center">
        <div class="container">
          <h1 class="jumbotron-heading">'.$this->software->getNome().'</h1>
              
        </div>
      </section>
              
        <div class="album py-5 bg-light">
            <div class="container">';
        
        
        $codigo .= '';
        
        $codigo .= '
            
            
              </div>
            
            </div>
            
     </main>
            
            
    <footer class="text-muted">
      <div class="container">
        <p class="float-right">
          <a href="#">Voltar ao topo</a>
        </p>
        <p>Este é um software desenvolvido automaticamente pelo escritor de Software.</p>
        <p>Novo no Escritor De Software? Acesse <a href="https://getcrudbyuml.com">GetCrudbyUml.com</a>.</p>
      </div>
    </footer>
            
';
        
        
        
        $codigo  .= '

<!-- Modal -->
<div class="modal fade" id="modalResposta" tabindex="-1" role="dialog" aria-labelledby="labelModalResposta" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="labelModalResposta">Resposta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <span id="textoModalResposta"></span>
      </div>
      <div class="modal-footer">
        <button type="button" id="botao-modal-resposta" class="btn btn-primary" data-dismiss="modal">Continuar</button>
      </div>
    </div>
  </div>
</div>



        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>';
        foreach ($this->software->getObjetos() as $objeto) {
            $codigo  .= '
        <script src="js/' .$objeto->getNomeSnakeCase() . '.js" ></script>';
            
        }
        $codigo .= '
	</body>
</html>';
        $caminho = 'index.html';
        $this->listaDeArquivos[$caminho] = $codigo;
        
    }
    
    
    
}

?>