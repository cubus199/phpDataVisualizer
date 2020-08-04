<?php
require_once 'drawingAgentIF.php';
require_once 'functionProvider.php';
require_once 'color.php';
require_once 'font.php';
require_once 'env.php';


const IM_DASH_SPACING = 4;
const IM_DASH_SIZE = 8;

class imDrawingAgent implements drawingAgentIF{
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
		//$x = min($x1 ,$x2);
		//$y = min($y1, $y2);
		//$width = abs(max($x1 ,$x2) - $x);
		//$height = abs(max($y1, $y2) - $y);
		//$this->writeSVG('<rect x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'" style="'.($filled ? 'fill' : 'fill: none; stroke-width:'.$width.'; stroke').': '.$color->colorHexAlpha().'; " />');
	}

	public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0): void{
		/*$this->registerFont($font);
		$transform = '';
		if($angle != 0){
			$transform = 'transform="rotate('.$angle.' '.$x.','.$y.')"';
		}

		switch($xAlign){
			default:
			case LEFT:
				$ta = 'start';
				break;
			case CENTER:
				$ta = 'middle';
				break;
			case RIGHT:
				$ta = 'end';
		}
		
		$textHeight = functionProvider::calcTextDim($font, $size, $text)['y'];

		switch($yAlign){
			default:
			case BOTTOM:
				$db = 'ideographic';
				break;
			case CENTER:
				$db = 'central';
				break;
			case TOP:
				$db = 'hanging';
		}
		$this->writeSVG('<text x="'.$x.'" y="'.$y.'" text-anchor="'.$ta.'" dominant-baseline="'.$db.'" style="fill:'.$color->colorHexAlpha().'; font-family: '.$font->name.'; font-size: '.$size.'pt" '.$transform.'>'.$text.'</text>');
		*/
	}
	
	public function drawArc(float $x, float $y, float $radius, float $start, float $end, color $color, bool $filled = true, float $width = 2): void{
		//$threeOclock = array($x + $radius, $y);
		//$startingPoint = functionProvider::rotatePoint($threeOclock, array($x, $y), $start);
		//$endingPoint = functionProvider::rotatePoint($threeOclock, array($x, $y), $end);
		//$this->writeSVG('<path d="M '.($filled ? $x.' '.$y.' L': '').implode(' ', $startingPoint).' A '.$radius.' '.$radius.' 0 0 1 '.implode(' ', $endingPoint).' '.($filled ? 'Z' : '').'" style="'.($filled? 'fill' : 'fill: none; stroke-width:'.$width.'; stroke').': '.$color->colorHexAlpha().'" />');
	}
	
	public function drawPolyLine(array $points, float $width, color $color, bool $dashed = false): void{
		//$this->writeSVG('<polyline points="'.implode(' ', $points).'" style="stroke-linecap: round; fill: none; stroke:'.$color->colorHexAlpha().'; stroke-width:'.$width.'; '.($dashed?'stroke-dasharray: '.($width*2).','.($width*2).';':'').'" />');
	}

	public function drawPolygon(array $points, color $color, bool $filled = true, float $width = 2): void{
		//$this->writeSVG('<polygon points="'.implode(' ', $points).'" style="'.($filled ? 'fill' : 'fill: none; stroke-width:'.$width.'; stroke').':'.$color->colorHexAlpha().'" />');
	}

}
?>