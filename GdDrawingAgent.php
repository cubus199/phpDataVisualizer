<?php
require_once 'DrawingAgentIF.php';
require_once 'FunctionProvider.php';
require_once 'Color.php';
require_once 'Font.php';
require_once 'env.php';

const DASH_SPACING = 4;
const DASH_SIZE = 8;

class GdDrawingAgent implements drawingAgentIF{
	private $img;
	private float $width;
	private float $height;
	private array $colors;
	private color $backgroundColor;
	private int $outputFile;

    /**
     * create a new GD drawing agent object
     * @param float $width
     * @param float $height
     * @param color $backgroundColor
     * @param int $outputFile
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
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $width
     * @param color $color
     * @param bool $dashed
     */
	

	public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width, color $color, bool $dashed = false):void{
		imagesetthickness($this->img, $width);
		if($dashed){
			$style = array_merge(array_fill(0, $width*DASH_SIZE, $this->allocAlphaColorHex($color)), array_fill(0, $width*DASH_SPACING, IMG_COLOR_TRANSPARENT));
			imagesetstyle($this->img, $style);
			imageline($this->img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
		}else{
			imageline($this->img, $x1, $y1, $x2, $y2, $this->allocAlphaColorHex($color));
		}
	}

    /**
     * draws a rectangle which connects two points
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param color $color
     * @param bool $filled
     * @param float $width
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
     * @param float $x
     * @param float $y
     * @param string $text
     * @param font $font
     * @param float $size
     * @param color $color
     * @param int $xAlign
     * @param int $yAlign
     * @param float $angle
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
		imagefttext($this->img, $size, 360 - $angle, $x, $y, $this->allocAlphaColorHex($color), FONT_DIR.DIRECTORY_SEPARATOR.$font->path, $text);
	}

    /**
     * Draws a partial arc centered at a specified point
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param float $start
     * @param float $end
     * @param color $color
     * @param bool $filled
     * @param float $width
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
     * @param array $points
     * @param float $width
     * @param color $color
     * @param bool $dashed
     */
	public function drawPolyLine(array $points, float $width, color $color, bool $dashed = false):void{
		imagesetthickness($this->img, $width);
		if($dashed){
			$style = array_merge(array_fill(0, $width*DASH_SIZE, $this->allocAlphaColorHex($color)), array_fill(0, $width*DASH_SPACING, IMG_COLOR_TRANSPARENT));
			imagesetstyle($this->img, $style);
			imageopenpolygon ($this->img ,$points ,count($points)/2 ,IMG_COLOR_STYLED);
		}else{
			imageopenpolygon ($this->img ,$points ,count($points)/2 ,$this->allocAlphaColorHex($color));
		}
	}

    /**
     * connects the given points, filled or not filled
     * @param array $points
     * @param color $color
     * @param bool $filled
     * @param float $width
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
	 * @return int color
	 */
	private function allocAlphaColorHex(color $color): int{
		if(!isset($this->colors[$color->colorHexAlpha()])){
			$this->colors[$color->colorHexAlpha()] = imagecolorallocatealpha($this->img, $color->r, $color->g, $color->b, $color->colorGDAlpha());
		}
		return $this->colors[$color->colorHexAlpha()];
	}

	/**
	 * outputs the image
	 * direct return or encoded output possible
     * @return string|resource
	 */
	public function finish(){
		if($this->outputFile == RAW_OUTPUT){
			return $this->img;
		}else if($this->outputFile == PNG_BASE64_OUTPUT){
			ob_start();
			imagepng($this->img);
			return base64_encode(ob_get_clean());
		}else if($this->outputFile == JPEG_BASE64_OUTPUT){
			ob_start();
			imagejpeg($this->img);
			return base64_encode(ob_get_clean());
		}
	}
}
?>