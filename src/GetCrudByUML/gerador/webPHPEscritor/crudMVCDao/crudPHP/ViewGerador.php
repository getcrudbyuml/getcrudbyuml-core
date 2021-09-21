<?php



namespace GetCrudByUML\gerador\webPHPEscritor\crudMVCDao\crudPHP;

use GetCrudByUML\model\Atributo;
use GetCrudByUML\model\Objeto;
use GetCrudByUML\model\Software;

class ViewGerador{
    private $software;
    private $listaDeArquivos;
    
    public static function main(Software $software){
        $gerador = new ViewGerador($software);
        return $gerador->gerarCodigo();
        
    }
    
    public function __construct(Software $software){
        $this->software = $software;
        
    }
    /**
     * Selecione uma linguagem
     * @param int $linguagem
     */
    public function gerarCodigo(){
        foreach($this->software->getObjetos() as $objeto){
            $this->geraViews($objeto);
        }
        
        return $this->listaDeArquivos;
        
    }

    private function showInsertForm(Objeto $objeto) : string {
        $codigo = '';
        
        
        $atributosComuns = array();
        $existeCampoFile = false;
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }else if($atributo->isObjeto())
            {
                $atributosObjetos[] = $atributo;
            }
            if($atributo->getTipo() == Atributo::TIPO_IMAGE){
                $existeCampoFile = true;
            }
        }
        $objetos1N = array();
        foreach ($this->software->getObjetos() as $objeto2){
            foreach($objeto2->getAtributos() as $atributo){
                if($atributo->isArray1N()){
                    if($atributo->getTipoDeArray() == $objeto->getNome()){
                        $objetos1N[] = $objeto2;
                    }
                    
                }
            }
        }
        
        $arrayParametros = array();
        foreach($atributosObjetos as $atributoObjeto){
            $arrayParametros[] = '$lista'.ucfirst($atributoObjeto->getNome());   
        }
        foreach($objetos1N as $obj){
            $arrayParametros[] = '$lista'.ucfirst($obj->getNome());
        }
        $codigo = '
    public function showInsertForm(';
        $codigo .= implode(', ', $arrayParametros);
        $codigo .= ') {
		echo \'
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary m-3" data-toggle="modal" data-target="#modalAdd'.$objeto->getNome().'">
  Adicionar
</button>

<!-- Modal -->
<div class="modal fade" id="modalAdd'.$objeto->getNome().'" tabindex="-1" role="dialog" aria-labelledby="labelAdd'.$objeto->getNome().'" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="labelAdd'.$objeto->getNome().'">Adicionar '.$objeto->getNomeTextual().'</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">';
        
        
        $codigo .= '
          <form id="insert_form_'.$objeto->getNomeSnakeCase().'" class="user" method="post"';
        if($existeCampoFile){
            $codigo .= ' enctype="multipart/form-data" ';
        }

          $codigo .= '>
            <input type="hidden" name="'.'enviar_'.$objeto->getNomeSnakeCase().'" value="1">                

';
        foreach ($atributosComuns as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
            $codigo .= '

                                        <div class="form-group">
                                            <label for="' . $atributo->getNomeSnakeCase(). '">' . $atributo->getNomeTextual(). '</label>
                                            '.$atributo->getFormHtml().'
                                        </div>';
        }
        
        foreach($atributosObjetos as $atributo){
            
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
                                        <div class="form-group">
                                          <label for="' . $atributo->getNomeSnakeCase(). '">' . $atributo->getNomeTextual(). '</label>
                						  <select class="form-control" id="' . $atributo->getNomeSnakeCase() . '" name="' . $atributo->getNomeSnakeCase(). '">
                                            <option value="">Selecione o '.$atributo->getNomeTextual().'</option>\';
                                                
        foreach( $lista'.ucfirst($atributo->getNome()).' as $element){
            echo \'<option value="\'.$element->get'.$strCampoPrimary.'().\'">\'.$element.\'</option>\';
        }
            
        echo \'
                                          </select>
                						</div>';
            
        }
        foreach($objetos1N as $obj){
            $strCampoPrimary = '';
            foreach ($obj->getAtributos() as $att){
                if($att->isPrimary()){
                    $strCampoPrimary = ucfirst($att->getNome());
                    break;
                }
            }
            $codigo .= '
                                        <div class="form-group">
                                          <label for="' . $obj->getNomeSnakeCase(). '">' . $obj->getNomeTextual(). '</label>
                						  <select class="form-control" id="' . $obj->getNomeSnakeCase() . '" name="' . $obj->getNomeSnakeCase(). '">
                                            <option value="">Selecione o '.$obj->getNomeTextual().'</option>\';
                                                
        foreach( $lista'.ucfirst($obj->getNome()).' as $element){
            echo \'<option value="\'.$element->get'.$strCampoPrimary.'().\'">\'.$element.\'</option>\';
        }
                
        echo \'
                                          </select>
                						</div>';
        }
        
        $codigo .= '

						              </form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <button form="insert_form_'.$objeto->getNomeSnakeCase().'" type="submit" class="btn btn-primary">Cadastrar</button>
      </div>
    </div>
  </div>
</div>
            
            
			
\';
	}



';
        return $codigo;
    }
    private function showList(Objeto $objeto){
        $codigo = '';
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
            else if($atributo->isObjeto())
            {
                $atributosObjetos[] = $atributo;
            }
        }
        
        $codigo .= '                                            
                                            
    public function showList($lista){
           echo \'
                                            
                                            
                                            

          <div class="card">
                <div class="card-header">
                  Lista '.$objeto->getNomeTextual().'
                </div>
                <div class="card-body">
                                            
                                            
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%"
				cellspacing="0">
				<thead>
					<tr>';
        $i = 0;
        foreach($atributosComuns as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
						<th>'.$atributo->getNomeTextual().'</th>';
            
        }
        $i = 0;
        foreach($atributosObjetos as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
						<th>'.$atributo->getNomeTextual().'</th>';
            
        }
        $codigo .= '
                        <th>Actions</th>';
        $codigo .= '
					</tr>
				</thead>
				<tfoot>
					<tr>';
        $i = 0;
        foreach($atributosComuns as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
                        <th>'.$atributo->getNomeTextual().'</th>';
        }
        $i = 0;
        foreach($atributosObjetos as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
						<th>'.$atributo->getNomeTextual().'</th>';
            
        }
        $codigo .= '
                        <th>Actions</th>';
        $codigo .= '
					</tr>
				</tfoot>
				<tbody>';
        $codigo .= '\';';
        
        $codigo .= '
            
            foreach($lista as $element){
                echo \'<tr>\';';
        $i = 0;
        foreach($atributosComuns as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
                echo \'<td>\'.$element->get'.ucfirst ($atributo->getNome()).'().\'</td>\';';
        }
        $i = 0;
        foreach($atributosObjetos as $atributo){
            $i++;
            if($i >= 5){
                break;
            }
            $codigo .= '
                echo \'<td>\'.$element->get'.ucfirst ($atributo->getNome()).'().\'</td>\';';
        }
        $codigo .= '
                echo \'<td>
                        <a href="?page='.$objeto->getNomeSnakeCase().'&select=\'.$element->get'.ucfirst ($objeto->getAtributos()[0]->getNome()).'().\'" class="btn btn-info text-white">Select</a>
                        <a href="?page='.$objeto->getNomeSnakeCase().'&edit=\'.$element->get'.ucfirst ($objeto->getAtributos()[0]->getNome()).'().\'" class="btn btn-success text-white">Edit</a>
                        <a href="?page='.$objeto->getNomeSnakeCase().'&delete=\'.$element->get'.ucfirst ($objeto->getAtributos()[0]->getNome()).'().\'" class="btn btn-danger text-white">Delete</a>
                      </td>\';';
        
        $codigo .= '
                echo \'</tr>\';
            }
            
        ';
        
        $codigo .= 'echo \'';
        $codigo .= '
				</tbody>
			</table>
		</div>
            
            
            
            
  </div>
</div>
            
\';
    }
            ';
        return $codigo;
    }
    private function mostrarSelecionado(Objeto $objeto) : string {
        $codigo = '';
        $nomeDoObjeto = strtolower($objeto->getNome());
        
        
        $atributosComuns = array();
        $atributosObjetos = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
            else if($atributo->isObjeto())
            {
                $atributosObjetos[] = $atributo;
            }
        }
        $codigo = '


            
        public function showSelected('.$objeto->getNome().' $'.$nomeDoObjeto.'){
            echo \'
            
	<div class="card o-hidden border-0 shadow-lg">
        <div class="card">
            <div class="card-header">
                  '.$objeto->getNomeTextual().' selecionado
            </div>
            <div class="card-body">';
        
        foreach($atributosComuns as $atributo){
            $codigo .= '
                '.ucfirst($atributo->getNomeTextual()).': \'.$'.$nomeDoObjeto.'->get'.ucfirst ($atributo->getNome()).'().\'<br>';
        }
        
        foreach($atributosObjetos as $atributo){
            $codigo .= '
                '.ucfirst($atributo->getNomeTextual()).': \'.$'.$nomeDoObjeto.'->get'.ucfirst ($atributo->getNome()).'().\'<br>';
        }
        
        $codigo .= '
            
            </div>
        </div>
    </div>
            
            
\';
    }
';
        return $codigo;
    }
    private function showEditForm(Objeto $objeto) : string {
        $codigo = '';
        
        
        $atributosComuns = array();
        $atributosObjetos = array();
        $listaParametros = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }else if($atributo->isObjeto())
            {
                $atributosObjetos[] = $atributo;
                $listaParametros[] = '$lista'.ucfirst($atributo->getNome());
            }
        }
        $listaParametros[] = $objeto->getNome().' $selecionado';
        
        $codigo .= '

            
	public function showEditForm(';
       $codigo .= implode(', ', $listaParametros);
	   $codigo .= ') {
		echo \'
	    
	    

<div class="card o-hidden border-0 shadow-lg mb-4">
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit ' . $objeto->getNomeTextual() . '</h6>
        </div>
        <div class="card-body">
            <form class="user" method="post" id="edit_form_' . $objeto->getNomeSnakeCase() . '">';
	   
	   
	   foreach ($atributosComuns as $atributo) {
	       
	       if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
	           continue;
	       }
	       $codigo .= '
                                        <div class="form-group">
                                            <label for="'.$atributo->getNomeSnakeCase().'">'.$atributo->getNomeTextual().'</label>
                                            '.$atributo->getFormHTMLEditar().'
                						</div>';
	   }
	   foreach($atributosObjetos as $atributo){
	       $codigo .= '
                                        <div class="form-group">
                                          <label for="' . $atributo->getNomeSnakeCase(). '">' . $atributo->getNomeTextual(). '</label>
                						  <select class="form-control" id="' . $atributo->getNomeSnakeCase() . '" name="' . $atributo->getNomeSnakeCase(). '">
                                            <option value="">Selecione o '.$atributo->getNomeTextual().'</option>\';
                                                
        foreach( $lista'.ucfirst($atributo->getNome()).' as $element){
            echo \'<option value="\'.$element->getId().\'">\'.$element.\'</option>\';
        }
            
        echo \'
                                          </select>
                						</div>';
	       
	   }
	   $codigo .= '
                <input type="hidden" value="1" name="edit_' . $objeto->getNomeSnakeCase() . '">
                </form>

        </div>
        <div class="modal-footer">
            <button form="edit_form_' . $objeto->getNomeSnakeCase() . '" type="submit" class="btn btn-primary">Cadastrar</button>
        </div>
    </div>
</div>

	    

										
						              ';
        
        
        $codigo .= '\';
	}

';
        return $codigo;
    }
    private function confirmDelete(Objeto $objeto) : string {
        $codigo = '';
        
        
        $atributosComuns = array();

        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->tipoListado()){
                $atributosComuns[] = $atributo;
            }
        }
        
        $codigo  = '

                                            
    public function confirmDelete('.$objeto->getNome().' $'.lcfirst($objeto->getNome()).') {
		echo \'
        
        
        
				<div class="card o-hidden border-0 shadow-lg">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
        
							<div class="col-lg-12">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4"> Delete ' . $objeto->getNomeTextual() . '</h1>
									</div>
						              <form class="user" method="post">';
        
        foreach ($atributosComuns as $atributo) {
            if ($atributo->getIndice() == Atributo::INDICE_PRIMARY) {
                continue;
            }
            
        }
        
        
        
        $codigo .= '                    Are you sure you want to delete this object?

                                        <input type="submit" class="btn btn-primary btn-user btn-block" value="Delete" name="delete_' . $objeto->getNomeSnakeCase() . '">
                                        <hr>
                                            
						              </form>
                                            
								</div>
							</div>
						</div>
					</div>
                                            
                                            
                                            
                                            
	</div>\';
	}
                      

';
        return $codigo;
    }

    private function showAtributoArray(Objeto $objeto) : string{
        $codigo = '';
        $atributosArray = array();
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isArray()){
                $atributosArray[] = $atributo;
            }
        }
        foreach($atributosArray as $atributoArray){
            $objetoNN = null;
            foreach($this->software->getObjetos() as $obj){
                if($obj->getNome() == $atributoArray->getTipoDeArray()){
                    $objetoNN = $obj;
                    break;
                }
            }
            if($objetoNN == null){
                continue;
            }
            $atributoPrimary = null;
            foreach ($objetoNN->getAtributos() as $atributo2) {
                if($atributo2->tipoListado())
                {
                    $atributosComuns2[] = $atributo2;
                }
                if($atributo2->isPrimary()){
                    $atributoPrimary = $atributo2;
                }
            }
            if($atributoPrimary == null){
                continue;
            }
            $codigo .= '
    public function show'.ucfirst($atributoArray->getNome()).'('.ucfirst($objeto->getNome()).' $'.strtolower($objeto->getNome()).'){
        echo \'
        
    	<div class="card o-hidden border-0 shadow-lg">
              <div class="card">
                <div class="card-header">
                  '.explode(" ", $atributoArray->getTipo())[2].' do '.$objeto->getNome().'
                </div>
                <div class="card-body">
                      
                      
		<div class="table-responsive">
			<table class="table table-bordered" id="dataTable" width="100%"
				cellspacing="0">
				<thead>
					<tr>';
            $i = 0;
            foreach($atributosComuns2 as $atributo3){
                $i++;
                if($i >= 4){
                    break;
                }
                $codigo .= '
						<th>'.$atributo3->getNome().'</th>';
            }
            $codigo .= '<th>Actions</th>';
            $codigo .= '
					</tr>
				</thead>
				<tfoot>
					<tr>';
            $i = 0;
            foreach($atributosComuns2 as $atributo3){
                $i++;
                if($i >= 4){
                    break;
                }
                $codigo .= '
                        <th>'.$atributo3->getNome().'</th>';
            }
            $codigo .= '<th>Actions</th>';
            $codigo .= '
					</tr>
				</tfoot>
				<tbody>';
            $codigo .= '\';';
            
            $codigo .= '
                
            foreach($'.strtolower($objeto->getNome()).'->get'.ucfirst($atributoArray->getNome()).'() as $element){
                echo \'<tr>\';';
            $i = 0;
            foreach($atributosComuns2 as $atributo3){
                $i++;
                if($i >= 4){
                    break;
                }
                $codigo .= '
                echo \'<td>\'.$element->get'.ucfirst ($atributo3->getNome()).'().\'</td>\';';
            }
            $codigo .= 'echo \'<td>
                        <a href="?page='.$atributoArray->getTipoDeArraySnakeCase().'&select=\'.$element->get'.ucfirst ($atributoPrimary->getNome()).'().\'" class="btn btn-info">Selecionar</a>
                        <a href="?page='.strtolower($objeto->getNome()).'&select=\'.$'.strtolower($objeto->getNome()).'->get'.ucfirst ($atributoPrimary->getNome()).'().\'&remover_'.$atributoArray->getTipoDeArraySnakeCase().'=\'.$element->get'.ucfirst($atributoPrimary->getNome()).'().\'" class="btn btn-danger">Remover</a>
                      </td>\';';
            
            $codigo .= '
                echo \'<tr>\';
            }
                
        ';
            
            $codigo .= 'echo \'';
            $codigo .= '
				</tbody>
			</table>
		</div>
                
                
                
                
      </div>
  </div>
</div>
                
                
                
        \';
                
    }
                
';
            
        }
        
        return $codigo;
    }

    
    private function addAtributoArrayNN(Objeto $objeto):string{
        $atributosArray = array();
        $codigo = '';
        foreach ($objeto->getAtributos() as $atributo) {
            if($atributo->isArrayNN()){
                $atributosArray[] = $atributo;
            }
        }
        foreach($atributosArray as $atributoArray){
            $objetoNN = null;
            foreach($this->software->getObjetos() as $obj){
                if($obj->getNome() == $atributoArray->getTipoDeArray()){
                    $objetoNN = $obj;
                    break;
                }
            }
            if($objetoNN == null){
                continue;
            }
            
            $codigo .= '
                
                
    public function add'.ucfirst($atributoArray->getTipoDeArray()).'($lista){
        
        
        echo \'
        
        
        
    <div class="card o-hidden border-0 shadow-lg">
	   <div class="card-body p-0">
		  <div class="row">
        
							<div class="col-lg-12">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4"> Adicione '.$atributoArray->getTipoDeArray().' ao '.$objeto->getNome().'</h1>
									</div>
						              <form class="user" method="post">';
            
            $codigo .= '
                                        <div class="form-group">
                						  <select type="text" class="form-control" id="add_'.$atributoArray->getTipoDeArraySnakeCase().'" name="add_'.$atributoArray->getTipoDeArraySnakeCase().'" >
                                                <option>Adicione '.$atributoArray->getTipoDeArray().'</option>\';
';
            $codigo .= '
            foreach($lista as $element){';
            $atributosLabel = array();
            foreach($objetoNN->getAtributos() as $atributo2){
                if($atributo2->getIndice() == Atributo::INDICE_PRIMARY){
                    $atributoChave = $atributo2;
                }else if($atributo2->tipoListado()){
                    $atributosLabel[] = $atributo2;
                }
            }
            $codigo .= '
                echo \'
                                                <option value="\'.$element->get'.ucfirst($atributoChave->getNome()).'().\'">';
            foreach($atributosLabel as $atributo2){
                $codigo .= '\'.$element->get'.ucfirst($atributo2->getNome()).'().\' - ';
                
            }
            $codigo .= '</option>\';
                
            }
                
';
            $codigo .= '
            echo \'
                
                                          </select>
                						</div>';
            
            $codigo .= '
                                        <input type="submit" class="btn btn-primary btn-user btn-block" value="Cadastrar" name="enviar_'.strtolower(explode(' ', $atributoArray->getTipo())[2]).'">
                                        <hr>
                                            
						              </form>
                                            
								</div>
							</div>
						</div>
					</div>
                                            
                                            
	   </div>\';
                                            
                                            
                                            
                                            
    }
                                            
                                            
';
            
        }
        return $codigo;
    }
    
    private function geraViews(Objeto $objeto)
    {
        $codigo = '<?php
            
/**
 * Classe de visao para ' . $objeto->getNome() . '
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 *
 */

namespace '.$this->software->getNome().'\\\\view;
use '.$this->software->getNome().'\\\\model\\\\'.ucfirst($objeto->getNome()).';


class ' . $objeto->getNome() . 'View {';
        $codigo .= '';
        $codigo .= $this->showInsertForm($objeto);
        $codigo .= $this->showList($objeto);
        $codigo .= $this->showEditForm($objeto);
        $codigo .= $this->mostrarSelecionado($objeto);
        $codigo .= $this->confirmDelete($objeto);
        $codigo .= $this->showAtributoArray($objeto);
        $codigo .= $this->addAtributoArrayNN($objeto);

        $codigo .= '
}';

        
        $caminho = ucfirst($objeto->getNome()).'View.php';
        $this->listaDeArquivos[$caminho] = $codigo;
    }
   
    
}


?>