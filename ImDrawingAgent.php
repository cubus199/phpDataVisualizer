<?php
require_once 'DrawingAgentIF.php';
require_once 'FunctionProvider.php';
require_once 'Color.php';
require_once 'Font.php';
require_once 'env.php';


const IM_DASH_SPACING = 4;
const IM_DASH_SIZE = 8;
const FONTSIZE_FACTOR = 1.28;

class ImDrawingAgent implements drawingAgentIF{
	private $im;
	private float $width;
	private float $height;
	private color $backgroundColor;
	private int $format;

	public function __construct(float $width, float $height, color $backgroundColor, int $format = 0){
		if (!extension_loaded('imagick')){
			die('imagick not installed');
		}
		$this->width = $width;
		$this->height = $height;
		$this->backgroundColor = $backgroundColor;
		$this->format = $format;

		switch($this->format){
			case RAW_OUTPUT:
			case PNG_BASE64_OUTPUT:
				$formatString = 'png';
				break;
			case JPG_BASE64_OUTPUT:
				$formatString = 'jpeg';
				break;
		}

		$this->im = new \Imagick();
		$this->im->newImage($width, $height, $backgroundColor->colorHexAlpha()); 
		$this->im->setImageFormat($formatString);
	}

	public function getContentType(){
		switch($this->format){
			case RAW_OUTPUT:
				return 'image/png';
				break;
			case PNG_BASE64_OUTPUT:
				return 'image/png;base64';
				break;
			case JPG_BASE64_OUTPUT:
				return 'image/jpeg;base64';
				break;
		}
	}

	public function finish(): string{
		switch($this->format){
			case RAW_OUTPUT:
				return $this->im;
				break;
			case PNG_BASE64_OUTPUT:
			case JPG_BASE64_OUTPUT:
				$imgBuff = $this->im->getimageblob();
				$this->im->clear();
				return base64_encode($imgBuff);
				break;
		}
		$this->getSVG().$this->createCSS().'</svg>';
	}

	public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width, color $color, bool $dashed = false): void{
		$draw = new \ImagickDraw();
		$draw->setStrokeColor($color->colorHexAlpha());
		$draw->setFillOpacity(0);
		$draw->setStrokeWidth($width);
		$draw->setStrokeLineCap(\Imagick::LINECAP_ROUND);
		if($dashed){
			$draw->setStrokeDashArray(array($width*2,$width*2));
		}
		$draw->line($x1, $y1, $x2, $y2);
		$this->im->drawImage($draw);
	}

	public function drawRectangle(float $x1, float $y1, float $x2, float $y2, color $color, bool $filled = true, float $width = 2): void{
		$draw = new \ImagickDraw();
		$draw->setStrokeColor($color->colorHexAlpha());
		$draw->setFillColor($color->colorHexAlpha());
		if(!$filled){
			$draw->setFillOpacity(0);
		}else{
			$draw->setStrokeOpacity(0);
		}
		$draw->setStrokeWidth($width);
		$draw->setStrokeLineCap(\Imagick::LINECAP_ROUND);

		$a1 = min($x1 ,$x2);
		$b1 = min($y1, $y2);
		$a2 = max($x1 ,$x2);
		$b2 = max($y1, $y2);

		$draw->rectangle($a1, $b1, $a2, $b2);
		$this->im->drawImage($draw);
	}

	public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0): void{
		/*$this->registerFont($font);
		$transform = '';
		if($angle != 0){
			$transform = 'transform="rotate('.$angle.' '.$x.','.$y.')"';
		}*/

		$gr = $this->calcGravity($x, $y, $xAlign, $yAlign);

		$draw = new \ImagickDraw();
		$draw->setGravity($gr[2]);
		$draw->setFontSize($size*FONTSIZE_FACTOR);
		$draw->setFont(REMOTE_FONT_DIR.'/'.$font->path);
		$draw->setFillColor($color->colorHexAlpha());
		$draw->setStrokeOpacity(0);

		//if($angle != 0) $draw->rotate($angle);
		$this->im->annotateImage($draw, $gr[0], $gr[1], $angle, $text);
		
		$this->im->drawImage($draw);
	}
	
	public function drawArc(float $x, float $y, float $radius, float $start, float $end, color $color, bool $filled = true, float $width = 2): void{
		$draw = new \ImagickDraw();
		$draw->setStrokeColor($color->colorHexAlpha());
		$draw->setFillColor($color->colorHexAlpha());

		if(!$filled){
			$draw->setFillOpacity(0);
		}else{
			$draw->setStrokeOpacity(0);
		}

		$draw->setStrokeWidth($width);
		$draw->setStrokeLineCap(\Imagick::LINECAP_ROUND);

		$threeOclock = array($x + $radius, $y);
		$startingPoint = functionProvider::rotatePoint($threeOclock, array($x, $y), $start);
		$endingPoint = functionProvider::rotatePoint($threeOclock, array($x, $y), $end);
		$draw->pathStart();
		if($filled){
			$draw->pathMoveToAbsolute($x, $y);
			$draw->pathLineToAbsolute($startingPoint[0], $startingPoint[1]);
		}else{
			$draw->pathMoveToAbsolute($startingPoint[0], $startingPoint[1]);
		}
		$draw->pathEllipticArcAbsolute ($radius, $radius, 0, 0, true, $endingPoint[0], $endingPoint[1]);
		$draw->pathClose();
		$draw->pathFinish();
		$this->im->drawImage($draw);
	}
	
	public function drawPolyLine(array $points, float $width, color $color, bool $dashed = false): void{
		$draw = new \ImagickDraw();
		$draw->setStrokeColor($color->colorHexAlpha());
		$draw->setFillOpacity(0);
		$draw->setStrokeWidth($width);
		$draw->setStrokeLineCap(\Imagick::LINECAP_ROUND);
		if($dashed){
			$draw->setStrokeDashArray(array($width*2,$width*2));
		}

		$coordinates = array();

		for($i = 0; $i < count($points) - 1; $i += 2){
			array_push($coordinates, array('x'=>$points[$i], 'y'=>$points[$i+1]));
		}

		$draw->polyline($coordinates);
		$this->im->drawImage($draw);
	}

	public function drawPolygon(array $points, color $color, bool $filled = true, float $width = 2): void{
		$draw = new \ImagickDraw();
		$draw->setStrokeColor($color->colorHexAlpha());
		$draw->setFillColor($color->colorHexAlpha());

		if(!$filled){
			$draw->setFillOpacity(0);
		}else{
			$draw->setStrokeOpacity(0);
		}

		$draw->setStrokeWidth($width);
		$draw->setStrokeLineCap(\Imagick::LINECAP_ROUND);

		$coordinates = array();

		for($i = 0; $i < count($points) - 1; $i += 2){
			array_push($coordinates, array('x'=>$points[$i], 'y'=>$points[$i+1]));
		}

		$draw->polygon($coordinates);
		$this->im->drawImage($draw);
	}

	private function calcGravity(float $x, float $y, int $xAlign, int $yAlign){
		switch($xAlign){
			default:
			case LEFT:
				$xa = 'WEST';
				break;
			case CENTER:
				$xa = '';
				$x -= $this->width /2;
				break;
			case RIGHT:
				$xa = 'EAST';
		}

		switch($yAlign){
			default:
			case BOTTOM:
				$ya = 'SOUTH';
				break;
			case CENTER:
				$ya = '';
				$y -= $this->height / 2;
				break;
			case TOP:
				$ya = 'NORTH';
		}

		$gr = $ya.$xa;
		if($gr == '') $gr = 'CENTER';

		return array($x, $y, constant('\Imagick::GRAVITY_'.$gr));
	}
}
?>