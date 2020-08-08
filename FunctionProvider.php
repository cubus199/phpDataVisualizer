<?php
require_once 'env.php';

class FunctionProvider{

    public static function rotatePoint(array $point, array $origin, float $angle): array{
        if($angle >= 360) $angle %= 360; //normalize angles
        if($angle == 0) return $point; //skip calculation if angle is 0

		$s = sin($angle * M_PI/180);
		$c = cos($angle * M_PI/180);

		// translate point back to origin:
		$point[0] -= $origin[0];
		$point[1] -= $origin[1];

		// rotate point
		$xnew = $point[0] * $c - $point[1] * $s;
		$ynew = (float) ($point[0] * $s) + (float) ($point[1] * $c);

		// translate point back:
		$x = (float) $xnew + (float) $origin[0];
        $y = (float) $ynew + (float) $origin[1];
        
		return array($x, $y);
    }
    
    public static function calcTextDim(font $font, float $size, string $text, float $angle = 0): array{
		$font_path = FONT_DIR.DIRECTORY_SEPARATOR.$font->path; // prepare path
        
        $box = imagettfbbox($size, $angle, $font_path, $text); // analyze measurements
        
        // measure in relation to axes
		$width = sqrt(pow($box[4]-$box[6],2)+pow($box[5]-$box[7],2));
		$height = sqrt(pow($box[6]-$box[0],2)+pow($box[7]-$box[1],2));
        
        // find most extreme values
		$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
		$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
		$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
        $max_y = max( array($box[1], $box[3], $box[5], $box[7]) ); 
        
        //prepare data for return
		$dims = array(
            'x' => $max_x - $min_x,
            'y' => $max_y - $min_y,
            'startX'=> (cos(deg2rad(90 - $angle)) * $height * 0.5+ cos(deg2rad($angle)) * $width *0.5),
            'startY'=>abs($box[7]-$box[1])
        );
        
		return $dims;
	}
}
?>