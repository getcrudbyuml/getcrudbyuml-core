<?php

namespace GetCrudByUML\util;
use ZipArchive;
/**
 * Classe utilitária para zipar arquivos de um diretório
 * 
 * @author jefferson
 *
 */
class Zipador {
	private $arrayDeArquivos;
	public function __construct(){
	    $this->arrayDeArquivos = array();
	}
	/**
	 *
	 * @return string
	 */
	public function getArrayDeArquivos() {
		return $this->arrayDeArquivos;
	}
	/**
	 *
	 * @param string $diretorio        	
	 * @param string $arquivoFinal        	
	 */
	public function zipaArquivo($diretorio, $arquivoFinal) {
		$this->browse ( $diretorio );
		if (! count ( $this->arrayDeArquivos )) {
			return;
		}
		
		$zipfile = $arquivoFinal;
		$filenames = $this->arrayDeArquivos;		
		$zip = new ZipArchive ();
		if ($zip->open ( $zipfile, ZIPARCHIVE::CREATE ) !== TRUE) {
			exit ( "Não pode abrir: <$zipfile>\n" );
		}
		
		foreach ( $filenames as $filename ) {
			$zip->addFile ( $filename, $filename );
			
		}
		
		$numeroDeArquivos = $zip->numFiles;
		$zip->close ();
		return $numeroDeArquivos;
		
	}
	/**
	 * O objetivo deste método é receber o nome de um dretorio e retornar um array com todos
	 * os arquivos de dentro, inclusive os arquivos em subpastas.
	 *
	 * @param string $directory
	 *        	
	 */
	public function browse($dir) {
		if (! file_exists ( $dir )) {
			return;
		}
		if ($handle = opendir ( $dir )) {
			while ( false !== ($file = readdir ( $handle )) ) {
				if ($file != "." && $file != ".." && is_file ( $dir . '/' . $file )) {
					$this->arrayDeArquivos [] = $dir . '/' . $file;
				} else if ($file != "." && $file != ".." && is_dir ( $dir . '/' . $file )) {
					$this->browse ( $dir . '/' . $file );
				}
			}
			closedir ( $handle );
		}
	}
}