<?php 

namespace GetCrudByUML\model;

/**
 * Classe feita para manipulação do objeto Atributo
 * feita automaticamente com programa gerador de software inventado por
 * @author Jefferson Uchôa Ponte <j.pontee@gmail.com>
 */


class Atributo {
	private $id;
	private $nome;
	private $tipo;
	private $indice;
	private $lenght;

	public function setLenght($lenght){
	    $this->lenght = $lenght;
	}
	public function getLenght(){
	    return $this->lenght;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getId() {
		return $this->id;
	}
	public function setNome($nome) {
		$this->nome = lcfirst($nome);
	}
	
	public function getNome() {
		return $this->nome;
	}
	
	public function getNomeSnakeCase()
	{
	    $nome	= preg_replace('/([a-z])([A-Z])/',"$1_$2",$this->nome);
	    $nome	= strtolower($nome);
	    return $nome;
	}
	public function getNomeTextual()
	{
	    $nome	= ucfirst(preg_replace('/([a-z])([A-Z])/',"$1 $2",$this->nome));
	    return $nome;
	}
	/**
	 * Raw: user login count
	 * Kebab Case: user-login-count
	 * @return string
	 */
	public function getNomeKebabCase(){
	    $nome	= preg_replace('/([a-z])([A-Z])/',"$1-$2",$this->nome);
	    $nome	= strtolower($nome);
	    return $nome;
	}
	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}
	public function getTipo() {
		return $this->tipo;
	}
	public function getTipoSnakeCase()
	{
	    $nome	= preg_replace('/([a-z])([A-Z])/',"$1_$2",$this->tipo);
	    $nome	= strtolower($nome);
	    return $nome;
	}
	public function setIndice($indice) {
		$this->indice = $indice;
	}
	public function getIndice() {
		return $this->indice;
	}
	public function tipoListado(){
	    if($this->tipo == self::TIPO_IMAGE || 
	        $this->tipo == self::TIPO_INT 
	        || 
	        $this->tipo == self::TIPO_STRING || $this->tipo == self::TIPO_FLOAT || $this->tipo == self::TIPO_DATE || $this->tipo == self::TIPO_DATE_TIME || $this->tipo == self::TIPO_BOOLEAN){
	        return true;
	    }
	    return false;
	}
	public function isArray(){
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array ')
	    {
	        return true;
	    }
	}
	public function isArrayNN(){
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array ')
	    {
	        if(explode(' ', $this->getTipo())[1]  == 'n:n'){
	            return true;
	        }
	        
	    }
	    return false;
	}
	public function isArray1N(){
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array ')
	    {
	        if(explode(' ', $this->getTipo())[1]  == '1:n'){
	            return true;
	        }
	        
	    }
	    return false;
	}
	public function getArrayTipoSnakeCase(){
	    if($this->isArray()){
	        $arr = explode(' ', $this->getTipo());
	        if(isset($arr[2])){
	            $nome	= preg_replace('/([a-z])([A-Z])/',"$1_$2",$arr[2]);
	            $nome	= strtolower($nome);
	            return $nome;
	        }
	    }
	    return null;
	}
	public function isPrimary(){
	    if($this->indice == self::INDICE_PRIMARY){
	        return true;
	    }
	    return false;
	}
	public function isObjeto(){
	    if($this->isArray()){
	        return false;
	    }
	    if($this->tipoListado()){
	        return false;
	    }
	    return true;
	}
	public function getTipoDeArray(){
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array '){
	        return explode(' ', $this->getTipo())[2];
	    }
	}
	public function getTipoDeArraySnakeCase(){
	    $strTipo = '';
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array '){
	        $strTipo = explode(' ', $this->getTipo())[2];
	    }
	    $strTipo = preg_replace('/([a-z])([A-Z])/',"$1_$2",$strTipo);
	    $strTipo = strtolower($strTipo);
	    return $strTipo;
	}
	public function getTipoJava(){
	    $tipo = "String";
	    if($this->tipoListado()){
	        if($this->getTipo() == self::TIPO_INT){
	            $tipo = 'int';
	        }else if($this->getTipo() == self::TIPO_STRING){
	            $tipo = 'String';
	        }else if($this->getTipo() == self::TIPO_FLOAT){
	            $tipo = 'Float';
	        }
	    }
	    if(substr(trim($this->getTipo()), 0, 6) == 'Array '){
	        $tipo = "ArrayList<".explode(' ', $this->getTipo())[2].'>';
	    }
	    return $tipo;
	}
	public function getTipoSqlite(){
	    $tipo = $this->getTipo();
	    if($this->tipoListado()){
	        if($this->getTipo() == self::TIPO_INT){
	            $tipo = 'INTEGER';
	        }else if($this->getTipo() == self::TIPO_STRING){
	            $tipo = 'TEXT';
	        }else if($this->getTipo() == self::TIPO_FLOAT){
	            $tipo = 'NUMERIC';
	        }else if($this->getTipo() == self::TIPO_DATE){
	            $tipo = 'TEXT';
	        }else if($this->getTipo() == self::TIPO_DATE_TIME){
	            $tipo = 'TEXT';
	        }
	        else if($this->getTipo() == self::TIPO_IMAGE){
	            $tipo = 'TEXT';
	        }
	        else if($this->getTipo() == self::TIPO_BOOLEAN){
	            $tipo = 'INTEGER';
	        }else{
	            $tipo = 'INTEGER';
	        }
	    }
	    return $tipo;
	}
	public function getTipoPostgres(){
	    $tipo = 'integer';
	    if($this->getTipo() == self::TIPO_STRING){
	        $tipo = 'character varying(400)';
	    }else if($this->getTipo() == self::TIPO_INT){
	        $tipo = 'integer';
	    }else if($this->getTipo() == self::TIPO_FLOAT){
	        $tipo = 'numeric(8,2)';
	    }else if($this->getTipo() == self::TIPO_BOOLEAN){
	        $tipo = 'BOOLEAN';
	    }
	    else if($this->getTipo() == self::TIPO_DATE_TIME){
	        $tipo = 'timestamp without time zone';
	    }else if($this->getTipo() == self::TIPO_DATE){
	        $tipo = 'date';
	    }else if($this->getTipo() == self::TIPO_IMAGE){
	        $tipo = 'character varying(200)';
	    }
	    return $tipo;
	}
	public function getTipoMysql(){
	    $tipo = 'INT';
	    if($this->getTipo() == self::TIPO_STRING){
	        $tipo = 'VARCHAR(400)';
	    }else if($this->getTipo() == self::TIPO_INT){
	        $tipo = 'INT';
	    }else if($this->getTipo() == self::TIPO_FLOAT){
	        $tipo = 'REAL';
	    }else if($this->getTipo() == self::TIPO_BOOLEAN){
	        $tipo = ' TINYINT NULL';
	    }
	    else if($this->getTipo() == self::TIPO_DATE_TIME){
	        $tipo = ' DATETIME';
	    }else if($this->getTipo() == self::TIPO_DATE){
	        $tipo = ' DATE ';
	    }else if($this->getTipo() == self::TIPO_IMAGE){
	        $tipo = 'VARCHAR(400)';
	    }
	    return $tipo;
	}
	public function getTipoFormHTML(){
	    $tipo = 'text';
	    
	    if($this->getTipo() == self::TIPO_INT){
	        $tipo = 'number';
	    }else if($this->getTipo() == self::TIPO_FLOAT){
	        $tipo = 'number';
	    }else if($this->getTipo() == self::TIPO_BOOLEAN){
	        $tipo = 'text';
	    }
	    else if($this->getTipo() == self::TIPO_DATE_TIME){
	        $tipo = 'datetime-local';
	    }else if($this->getTipo() == self::TIPO_DATE){
	        $tipo = 'date';
	    }else if($this->getTipo() == self::TIPO_IMAGE){
	        $tipo = 'file';
	    }
	    return $tipo;
	}
	public function getFormHTML(){
	    
	    $form = '<input type="'.$this->getTipoFormHTML().'" class="form-control" ';
	    if($this->tipo == self::TIPO_FLOAT){
	        $form .= 'step="0.01"';
	    }
	    $form .= ' name="' . $this->getNomeSnakeCase(). '" id="' . $this->getNomeSnakeCase(). '" placeholder="' . $this->getNomeTextual(). '">';
	    
	    if($this->getTipo() == self::TIPO_BOOLEAN){
	        $form = '
                    <select class="form-control" id="' . $this->getNomeSnakeCase(). '" name="' . $this->getNomeSnakeCase(). '">
                        <option value="">Selecione Um Valor</option>
                          <option value="1">Sim</option>
                          <option value="0">Não</option>
                    </select>';
	    }else if($this->getTipo() == self::TIPO_IMAGE){
	        
	        $form = '<input type="'.$this->getTipoFormHTML().'" class="form-control"  name="' . $this->getNomeSnakeCase(). '" id="' . $this->getNomeSnakeCase(). '"  accept="image/png, image/jpeg">';
	        
	    }
	    
	    return $form;
	}
	public function getFormHTMLEditar(){
	    
	    $form = '<input type="'.$this->getTipoFormHTML().'" class="form-control" value="\'.$selecionado->get'.ucfirst($this->getNome()).'().\'"  name="' . $this->getNomeSnakeCase(). '" id="' . $this->getNomeSnakeCase(). '" placeholder="' . $this->getNomeTextual(). '">';
	    if($this->getTipo() == self::TIPO_BOOLEAN){
	        $form = '
                    <select class="form-control" id="' . $this->getNomeSnakeCase(). '" name="' . $this->getNomeSnakeCase(). '" required>
                        <option value="">Selecione Um Valor</option>
                          <option value="1">Sim</option>
                          <option value="0">Não</option>
                    </select>';
	    }
	    
	    return $form;
	}
	public function getTipoParametroPDO(){
	    $tipo = 'PARAM_STR';
	    if($this->getTipo() == self::TIPO_STRING){
	        $tipo = 'PARAM_STR';
	    }else if($this->getTipo() == self::TIPO_INT){
	        $tipo = 'PARAM_INT';
	    }else if($this->getTipo() == self::TIPO_FLOAT){
	        $tipo = 'PARAM_STR';
	    }else if($this->getTipo() == self::TIPO_BOOLEAN){
	        $tipo = 'PARAM_BOOL';
	    }
	    else if($this->getTipo() == self::TIPO_DATE_TIME){
	        $tipo = 'PARAM_STR';
	    }else if($this->getTipo() == self::TIPO_DATE){
	        $tipo = 'PARAM_STR';
	    }else if($this->isObjeto()){
	        $tipo = 'PARAM_INT';
	    }
	    return $tipo;
	}
	const INDICE_PRIMARY = "PRIMARY";
	const TIPO_INT = "Int";
	const TIPO_STRING = "string";
	const TIPO_DATE = "date";
	const TIPO_DATE_TIME = "date_time";
	const TIPO_BOOLEAN = "boolean";
	const TIPO_IMAGE = "Image";
	const TIPO_FLOAT = "float";
	const TIPO_ARRAY_NN = "Array n:n";
	const TIPO_ARRAY_1N = "Array 1:n";
	
	
}
?>