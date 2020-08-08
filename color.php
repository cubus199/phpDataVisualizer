<?php
class color{
	public int $r;
	public int $g;
	public int $b;
	public int $alpha;

    /**
     * generating color object
     * first param gets considered as hex representation, if only one param is given
     * @param int|string $r
     * @param int $g
     * @param int $b
     * @param int $a
     * @throws Exception if color format not known
     */
	function __construct($r, int $g = -1, int $b = -1, int $a = 255){		
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
     * hex format doesn't contain the alpha value
     * @return string hex color
     */
	function colorHex(): string{
		return sprintf("#%02x%02x%02x", $this->r, $this->g, $this->b);
	}

	/**
	 * export color hex format with the alpha-value
     * @return string hex color
	 */
	function colorHexAlpha(): string{
		return sprintf("#%02x%02x%02x%02x", $this->r, $this->g, $this->b, $this->alpha);
	}

    /**
     * export GD-compatible alpha value
     * @return int alpha
     */
	function colorGDAlpha(): int{
		return floor((255 - $this->alpha)/2);
	}
}
?>