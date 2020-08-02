<?php
class functionProvider{

    public static function rotatePoint(array $point, array $origin, float $angle){
		float $s = sin($angle * M_PI/180);
		float $c = cos($angle * M_PI/180);

		// translate point back to origin:
		$point[0] -= $origin[0];
		$point[1] -= $origin[1];

		// rotate point
		float $xnew = $point[0] * $c - $point[1] * $s;
		float $ynew = (float) ($point[0] * $s) + (float) ($point[1] * $c);

		// translate point back:
		$x = (float) $xnew + (float) $origin[0];
        $y = (float) $ynew + (float) $origin[1];
        
		return array($x, $y);
    }
    
    
}
?>