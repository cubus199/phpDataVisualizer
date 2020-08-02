<?php
require_once 'drawingAgentIF.php';
require_once 'functionProvider.php';

class svgDrawingAgent implements drawingAgentIF{
	string $svg;
	float $width;
	float $height;
	color $color;

	public function __construct(float $width, float $height, color $color){
		$this->width = $width;
		$this->height = $height;
		$this->color = $color;

		$this->resetSVG();
		writeSVG('<svg viewBox="0 0 '.$this->width.' '.$this->height.'" width="100%" style="box-sizing: border-box; background: '.$this->color->colorHexAlpha().'">');
	}

	public function finish(){
		return $this->getSVG().'</svg>';
	}

	public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width, color $color, bool $dashed = false){
		$this->writeSVG('<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" style="stroke:'.$color->colorHexAlpha().'; stroke-width: '.$width.'; '.($dashed?'stroke-dasharray: 4,4;':'').'" />');
	}

	public function drawRectangle(float $x1, float $y1, float $x2, float $y2, color $color, bool $filled = true){
		$x = min($x1 ,$x2);
		$y = min($y1, $y2);
		$width = abs(max($x1 ,$x2) - $x);
		$height = abs(max($y1, $y2) - $y);
		$this->writeSVG('<rect x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'" style="'.($filled ? 'fill' : 'stroke').': '.$color->colorHexAlpha().'; " />');
	}

	public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0){
		$transform = '';
		if($angle != 0){
			$transform = 'transform="rotate('.$angle.' '.$x.','..$y')"';
		}

		switch($xAlign){
			case LEFT:
				$ta = 'start';
				break;
			case CENTER:
				$ta = 'middle';
				break;
			case RIGHT:
				$ta = 'end';
		}
		
		switch($yAlign){
			case BOTTOM:
				$cy = 0;
				break;
			case CENTER:
				$cy = '0.5em';
				break;
			case TOP:
				$cy = '1em';
		}
		$this->writeSVG('<text x="'.$x.'" y="'.$y.'" cy ="'.$cy.'" text-anchor="'.$ta.'" style="fill:'.$color->colorAlphaHex().'; font-family: '.$font->name.'; font-size: '.$size.'pt" '.$transform.'>'.$text.'</text>');
	}
	
	public function drawArc(float $x, float $y, float $radius, float $start, float $end, color $color, bool $filled = true){
		$threeOclock = array($x + $radius, $y);
		$startingPoint = functionProvider->rotatePoint($threeOclock, array($x, $y), $start);
		$endingPoint = functionProvider->rotatePoint($threeOclock, array($x, $y), $end);
		$this->writeSVG('<path d="M '.($filled ? $x.' '.$y.' L': '').implode(' ', $startingPoint).' A '.$radius.' '.$radius.' 0 0 1 '.implode(' ', $endingPoint).' '.($filled ? 'Z' : '').'" style="'.($filled? 'fill' : 'stroke').': '.$color->colorHexAlpha().'" />');
	}
	
	public function drawPolyLine(array $points, float $width, color $color){
		$this->writeSVG('<polyline points="'.implode(' ', $points).'" style="stroke:'.$color->colorHexAlpha().'; stroke-width:'.$width.'" />');
	}

	public function drawPolygon(array $points, color $color, bool $filled = true){
		$this->writeSVG('<polygon points="'.implode(' ', $points).'" style="'.($filled ? 'fill)' : 'stroke').':'.$color->colorHexAlpha().'" />');
	}

	private function getSVG(){
		return $this->svg;
	}

	private function resetSVG(){
		$this->svg = '';
	}

	private function writeSVG(string $new){
		$this->svg .= $new;
	}
}
?>