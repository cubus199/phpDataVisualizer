<?php
require_once 'drawingAgentIF.php';

// constants for symbols; can also be retrieved by constant('name')
define('CIRCLE', 0);
define('SQUARE', 1);
define('TRIANGLE', 2);
define('CROSS', 3);
define('DIAMOND', 4);

class graphGenerator{

	public drawingAgentIF $dA;

	public function __construct(drawingAgentIF $dA){
		$this->dA = $dA;
	}

    private function drawSymbol(float $x, float $y, int $type, color $color, float $size){
		$radius = $size / 2;
		switch($type){
			default:
            case CIRCLE:
                $this->dA->drawArc($x, $y, $radius, 0, 0, $color);
				break;
			case SQUARE:
				$x1 = $x - $radius;
				$y1 = $y - $radius;
				$x2 = $x + $radius;
				$y2 = $y + $radius;
				$this->dA->drawRectangle($x1, $y1, $x2, $y2, $color);
				break;
			case TRIANGLE:
				$a = $size / 4 * sqrt(3);
				$triangle = array(
					$x, $y - $a,
					$x + $radius, $y + $a,
					$x - $radius, $y + $a
				);
				$this->dA->drawPolygon($triangle, $color);
                break;
            case CROSS:
                $c = $size / 4;
                $b = $c * sqrt(0.5);
                $a = $radius - $b;
                $cross = array(	
                    $x, $y - $b,
                    $x + $a, $y - $radius,
                    $x + $radius, $y - $a,
                    $x + $b, $y,
                    $x + $radius, $y + $a,
                    $x + $a, $y + $radius,
                    $x, $y + $b,
                    $x - $a, $y + $radius,
                    $x - $radius, $y + $a,
                    $x - $b, $y,
                    $x - $radius, $y - $a,
                    $x - $a, $y - $radius
                );
                $this->dA->drawPolygon($cross, $color);
                break;
			case DIAMOND:
				$diamond = array(
					$x, $y - $radius,
					$x + $radius, $y,
					$x, $y + $radius,
					$x - $radius, $y
				);
				$this->dA->drawPolygon($diamond, $color);
				break;
		}
	}
}
?>