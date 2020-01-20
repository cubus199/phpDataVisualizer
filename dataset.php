<?php
class dataset{
	private static $maxId = 0; //naechste Identifikationsnummer
	public $id;		//Identifikation
	public $x_name;	//String Datenname
	public $values;	//Array Werte
	public $colors;	//Array Farben
	public $symbols;//Array Symbole
	/*
	 * Speichern eines Datenpunktes
	 */
	function __construct($name, $values, $colors = null, $symbols = null){
		$this->id = self::$maxId;
		self::$maxId++;
		$this->x_name = $name;
		$this->values = $values;
		$this->colors = $colors;
		$this->symbols = $symbols;
	}
}