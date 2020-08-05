<?php
class Dataset{
	private static int $maxId = 0; //next ID
	public int $id;			//ID
	public $x_name;	//Dataset name or x value
	public array $values;	//array contains all values
	public array $colors;	//array contains all colors
	public array $symbols;	//array contains all symbols

	/*
	 * create a Dataset
	 */
	function __construct($name, array $values, array $colors = array(), array $symbols = array()){
		$this->id = self::$maxId;
		self::$maxId++;
		$this->x_name = $name;
		$this->values = $values;
		$this->colors = $colors;
		$this->symbols = $symbols;
	}
}