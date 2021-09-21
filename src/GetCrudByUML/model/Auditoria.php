<?php

namespace GetCrudByUML\model;

/**
 * Classe feita para manipulação do objeto Auditoria
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */
class Auditoria {
	private $id;
	private $pagina;
	private $ipVisitante;
	private $infoSessao;
	private $data;
    public function __construct(){

    }
	public function setId($id) {
		$this->id = $id;
	}
		    
	public function getId() {
		return $this->id;
	}
	public function setPagina($pagina) {
		$this->pagina = $pagina;
	}
		    
	public function getPagina() {
		return $this->pagina;
	}
	public function setIpVisitante($ipVisitante) {
		$this->ipVisitante = $ipVisitante;
	}
		    
	public function getIpVisitante() {
		return $this->ipVisitante;
	}
	public function setInfoSessao($infoSessao) {
		$this->infoSessao = $infoSessao;
	}
		    
	public function getInfoSessao() {
		return $this->infoSessao;
	}
	public function setData($data) {
		$this->data = $data;
	}
		    
	public function getData() {
		return $this->data;
	}
	public function __toString(){
	    return $this->id.' - '.$this->pagina.' - '.$this->ipVisitante.' - '.$this->infoSessao.' - '.$this->data;
	}
                

}
?>