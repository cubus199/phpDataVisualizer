<?php
require_once 'drawingAgentIF.php';
require_once 'functionProvider.php';
require_once 'color.php';
require_once 'font.php';
require_once 'env.php';

const RAW_OUTPUT = 1;
const PNG_BASE64_OUTPUT = 2;

class gdDrawingAgent implements drawingAgentIF{
	private $img;
	private float $width;
	private float $height;
	private array $colors;
	private color $backgroundColor;
	private int $outputFile;
	
	/**
	 * create a new GD drawing agent object
	 */
	public function __construct(float $width, float $height, color $backgroundColor, int $outputFile = RAW_OUTPUT){
		$this->width = $width;
		$this->height = $height;
		$this->backgroundColor = $backgroundColor;
		$this->img = @imagecreatetruecolor ($width, $height) //create new empty image
			or die('GD is not enabled.');
		$colors = array();
		array_push($colors, imagecolorallocatealpha($this->img, $backgroundColor->r, $backgroundColor->g, $backgroundColor->b, $backgroundColor->colorGDAlpha()));
		imagefill($this->img, 0, 0, $colors[0]);
		$this->outputFile = $outputFile;
	}

	/**
	 * draws a line which connects two points
	 */
	public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width, color $color, bool $dashed = true):void{
		imagesetthickness($this->img, $width);
		if($dashed){
			$style = array_merge(array_fill(0, $width, $this->allocAlphaColorHex($color)), array_fill(0, $width, IMG_COLOR_TRANSPARENT));
			imagesetstyle($this->img, $style);
		}
		imageline($this->img, $x1, $y1, $x2, $y2, $this->allocAlphaColorHex($color));
	}

	/**
	 * draws a rectangle which connects two points
	 */
	public function drawRectangle(float $x1, float $y1, float $x2, float $y2, color $color, bool $filled = true, float $width = 2): void{
		if($filled){
			imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->allocAlphaColorHex($color));
		}else{
			imagesetthickness($this->img, $width);
			imagerectangle($this->img, $x1, $y1, $x2, $y2, $this->allocAlphaColorHex($color));
		}
	}

	/**
	 * write text starting on one point possitioned according to the params
	 */
	public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0):void{
		$textDim = functionProvider::calcTextDim($font, $size, $text);
		switch($xAlign){
			default:
			case LEFT:
				break;
			case CENTER:
				$x -= $textDim['startX'];
				break;
			case RIGHT:
				$x -= 2*$textDim['startX'];
		}
		switch($yAlign){
			default:
			case BOTTOM:
				break;
			case CENTER:
				$y += 0.5*$textDim['startY'];
				break;
			case TOP:
				$y += $textDim['startY'];
		}		
		imagefttext($this->img, $size, $angle, $x, $y, $this->allocAlphaColorHex($color), FONT_DIR.DIRECTORY_SEPARATOR.$font->path, $text);
	}

	/**
	 * Draws a partial arc centered at a specified point
	 */
	public function drawArc(float $x, float $y, float $radius, float $start, float $end, color $color, bool $filled = true, float $width = 2): void{
		if($filled){
			imagefilledarc($this->img, $x, $y, $radius*2, $radius*2, $start, $end, $this->allocAlphaColorHex($color), IMG_ARC_PIE);
		}else{
			imagesetthickness($this->img, $width);
			imagearc($this->img, $x, $y, $radius, $radius, $start, $end, $this->allocAlphaColorHex($color));
		}
	}

	/**
	 * draws a line through the given points
	 */
	public function drawPolyLine(array $points, float $width, color $color, bool $dashed = false):void{
		if($dashed){
			$style = array_merge(array_fill(0, $width, $this->allocAlphaColorHex($color)), array_fill(0, $width, IMG_COLOR_TRANSPARENT));
			imagesetstyle($this->img, $style);
		}
		imagesetthickness($this->img, $width);
		imageopenpolygon ($this->img ,$points ,count($points)/2 ,$this->allocAlphaColorHex($color));
	}

	/**
	 * connects the given points, filled or not filled
	 */
	public function drawPolygon(array $points, color $color, bool $filled = true, float $width = 2):void{
		if($filled){
			imagefilledpolygon($this->img, $points, count($points)/2, $this->allocAlphaColorHex($color));
		}else{
			imagesetthickness($this->img, $width);
			imagepolygon($this->img ,$points ,count($points)/2 ,$this->allocAlphaColorHex($color));
		}
	}

	/*
	 * allocate new color
	 */
	function allocAlphaColorHex(color $color): int{
		if(!isset($this->colors[$color->colorHexAlpha()])){
			$this->colors[$color->colorHexAlpha()] = imagecolorallocatealpha($this->img, $color->r, $color->g, $color->b, $color->colorGDAlpha());
		}
		return $this->colors[$color->colorHexAlpha()];
	}

	/**
	 * outputs the image
	 */
	public function finish(){
		if($this->outputFile == RAW_OUTPUT){
			return $this->img;
		}else if($this->outputFile == PNG_BAS64_OUTPUT){
			ob_start();
			return base64_encode(ob_get_clean());
		}
	}
}
?>