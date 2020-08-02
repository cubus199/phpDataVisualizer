<?php
class color{
	public $r;
	public $g;
	public $b;
	public $alpha;

	/**
	 * generating color object
	 * first param gets considered as hex representation, if only one param is given
	 */
	function __construct($r, $g = -1, $b = -1, $a = 255){		
		if($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255 && $a >= 0 && $a <= 255){
			$this->r = $r;
			$this->g = $g;
			$this->b = $b;
			$this->alpha = $a;

			return;
		}else if(strpos($r, '#') == 0){ //converting hex into rgb if it has a leading #
			$r = substr($r, 1); //remove leading #

			if(preg_match('/^[A-Fa-f\d]*$/', $r)){ // check if string is only made of numbers and characters a-f
				if(strlen($r) == 6){ // alpha is set to 100% if only 6 characters provided
					list($this->r, $this->g, $this->b) = sscanf($r, '%02x%02x%02x');
					$this->alpha = 255;
					return;
				}elseif(strlen($r) == 8){
					list($this->r, $this->g, $this->b, $this->alpha) = sscanf($r, '%02x%02x%02x%02x');
					return;
				}
			 }
		}
		throw new Exception('Unknown color-format!');
	}
  
	/**
	 * export color hex format
	 * hex format doesnot contain the alpha value
	 */
	function colorHex(){
		return sprintf("#%02x%02x%02x", $this->r, $this->g, $this->b);
	}

	/**
	 * export color hex format with the alpha-value
	 */
	function colorHexAlpha(){
		return sprintf("#%02x%02x%02x%02x", $this->r, $this->g, $this->b, $this->alpha);
	}
}
?>