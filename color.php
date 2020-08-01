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
    function __construct($r, $g = -1, $b = -1, $a = 0){		
		if($g != -1){
			$this->r = $r;
			$this->g = $g;
			$this->b = $b;
			$this->alpha = $a;
		}else{//converting hex into rgb
			list($this->r, $this->g, $this->b) = sscanf($r, '#%02x%02x%02x');
			$this->alpha = 0;
		}
    }
	
    /**
     * export color hex format
     * hex format doesnot contain the alpha value
     */
    function colorHex(){
        return sprintf("#%02x%02x%02x", $this->r, $this->g, $this->b);
    }
}
?>