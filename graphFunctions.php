<?php
class graphFunctions{	
	private $font_dir;
	public $graphData;
	public $graph;
	public $xLabels;
	public $yLabels;
	public $widestXLabel;
	public $widestYLabel;
	public $highestXLabel;
	public $highestYLabel;
	
	function __construct($graphData, $font_dir, $graph = array()){
		$this->graphData = $graphData;
		$this->font_dir = $font_dir;
		$this->graph = $graph;
	}
	/*
	 * nonNumiericType {0: numeric(false), 1: x nonNumeric, 2: y nonNumeric}
	 */
	public function calcGraphSize($stacked = false, $axisShift = false, $axesSwapped = false, $nonNumericType = 0){
		$font = $this->graphData->config['generalFont'];
		$limits = $this->graphData->getLimits($stacked);
		$this->calcLabels($this->graphData->config['xLabelCount'], $this->graphData->config['yLabelCount'], $stacked, $axisShift, $axesSwapped,$nonNumericType);
		//$this->widestXLabel = max(array_column($this->xLabels, 'x'));
		$this->widestYLabel = max(array_column($this->yLabels, 'x'));
		$this->highestXLabel = max(array_column($this->xLabels, 'y'));
		$this->highestYLabel = max(array_column($this->yLabels, 'y'));
		$x1 = $this->graphData->config['leftPadding'] + $this->widestYLabel + $this->graphData->config['axisThickness']*3;
		$y1 = $this->graphData->config['topPadding'] + ($this->calcWordDim($font, $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['y']*$this->graphData->config['graphTitleSpacingFactor']);
		$x2 = $this->graphData->config["containerWidth"] - $this->graphData->config['rightPadding'] - ($this->xLabels[count($this->xLabels)-1]['x']-$this->xLabels[count($this->xLabels)-1]['startX']);
		$y2 = $this->graphData->config["containerHeight"] - $this->graphData->config['bottomPadding'] - $this->highestXLabel - $this->graphData->config['axisThickness']*3;
		$this->graph = array('x1' => $x1,//Graphgrenze
							 'y1' => $y1,
							 'x2' => $x2,
							 'y2' => $y2);
		$this->calcScaling($stacked);
	}
	public function calcCenteredGraph(){
		$font = $this->graphData->config['generalFont'];
		$topPadding = $this->graphData->config['topPadding'] + ($this->calcWordDim($font, $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['y']*$this->graphData->config['graphTitleSpacingFactor']);
		$height = $this->graphData->config["containerHeight"] - $this->graphData->config['bottomPadding'] - $topPadding;
		$width = $this->graphData->config['containerWidth'] - $this-> graphData->config['leftPadding'] - $this-> graphData->config['rightPadding'];
		$x = $this-> graphData->config['leftPadding'] + 0.5 * $width;
		$y = $topPadding + 0.5 * $height;
		$this->graph = array('x'=>$x, 'y'=>$y, 'radius'=> min($height, $width));
	}
	/*
	 * Berechnen der Skalierungsfaktoren, sowie der Nullstellen
	 */
	private function calcScaling($stacked = false){
		if(isset($this->graph['x1'])){
			$labelsX = $this->graphData->getXLabels(2);
			$labelsY = $this->graphData->getYLabels(2, $stacked);
			$width = $this->graph['x2'] - $this->graph['x1'];
			$height = $this->graph['y2'] - $this->graph['y1'];
			/*
			 * Falls eine Division durch 0 vorliegt, wird 0 eingesetzt
			 */
			$this->graph['scaleNumericX'] = (is_numeric($labelsX[0])?(((abs($labelsX[0]) + $labelsX[1]) == 0)?0:($width)/(abs($labelsX[0]) + $labelsX[1])):0);
			$this->graph['scaleNumericFlippedX'] = (is_numeric($labelsX[0])?(((abs($labelsX[0]) + $labelsX[1]) == 0)?0:($width)/(abs($labelsY[0]) + $labelsY[1])):0);
			$this->graph['scaleNumericY'] = (is_numeric($labelsY[0])?(((abs($labelsY[0]) + $labelsY[1]) == 0)?0:($height)/(abs($labelsY[0]) + $labelsY[1])):0);
			$countValues = count($this->graphData->datasets);
			$this->graph['scaleNonNumericX'] = (($countValues == 0)?0:($width)/$countValues);
			$this->graph['scaleNonNumericY'] = (($countValues == 0)?0:($height)/$countValues);
			$this->graph['scaleNonNumericLineX'] = (($countValues-1 <= 0)?0:($width)/($countValues-1));
			$this->graph['scaleNonNumericLineY'] = (($countValues-1 <= 0)?0:($height)/($countValues-1));
			$this->graph['y0'] = $this->graph['y2'] - $this->graph['scaleNumericY'] * abs($labelsY[0]);
			$this->graph['yNonNumeric0'] = $this->graph['y2'] - $this->graph['scaleNonNumericY'] * abs($labelsY[0]);
			$this->graph['x0'] = $this->graph['x1'] + $this->graph['scaleNumericX'] * abs($labelsX[0]);
			$this->graph['xFlipped0'] = $this->graph['x1'] + $this->graph['scaleNumericFlippedX'] * abs($labelsY[0]);
		}
	}
	/*
	 * Labels bestimmen
	 * nonNumiericType {0: numeric(false), 1: x nonNumeric, 2: y nonNumeric}
	 */
	public function calcLabels($maxX, $maxY, $stacked = false, $axisShift = false, $axesSwapped = false, $nonNumericType = 0){
		$labelAngle = $this->graphData->config['colLabelRotation'];
		$nonNumericLabels = array();
		if($nonNumericType != 0){			
			foreach($this->graphData->datasets as $dataset){
				$nonNumericLabels []= array_merge(array('content' => $dataset->x_name, 'display' => $dataset->x_name), $this->calcWordDim($this->graphData->config['generalFont'], $this->graphData->config['generalFontSize'], $dataset->x_name, $nonNumericType==2?0:$labelAngle));
			}			
		}
		if($nonNumericType != 1){
			foreach(($axesSwapped?$this->graphData->getYLabels($maxX, $stacked, $axisShift):$this->graphData->getXLabels($maxX, $axisShift)) as $label){
				$disp = $this->fixDecimals($label, $this->graphData->config['xPrecision']).(isset($this->graphData->config['xUnit'])?$this->graphData->config['xUnit']:'');
				$this->xLabels []= array_merge(array('content' => $label, 'display' => $disp), $this->calcWordDim($this->graphData->config['generalFont'], $this->graphData->config['generalFontSize'], $disp, $labelAngle));
			}
		}else{
			$this->xLabels = $nonNumericLabels;
		}		
		if($nonNumericType != 2){
			foreach($this->graphData->getYLabels($maxY, $stacked,$axisShift) as $label){
				$disp = $this->fixDecimals($label, $this->graphData->config['yPrecision']).(isset($this->graphData->config['yUnit'])?$this->graphData->config['yUnit']:'');
				$this->yLabels []= array_merge(array('content' => $label, 'display' => $disp), $this->calcWordDim($this->graphData->config['generalFont'], $this->graphData->config['generalFontSize'], $disp,0));
			}
		}else{
			$this->yLabels = $nonNumericLabels;
		}
	}	
	 
	/*
	 * Berechnen der Wortlänge und -höhe
	 */
	protected function calcWordDim($font, $size, $text, $angle = 0){
		$font_path = $this->font_dir.$font.'.ttf';
		$box = imagettfbbox($size, $angle, $font_path, $text);
		$width = sqrt(pow($box[4]-$box[6],2)+pow($box[5]-$box[7],2));
		$height = sqrt(pow($box[6]-$box[0],2)+pow($box[7]-$box[1],2));
		
		$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
		$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
		$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
		$max_y = max( array($box[1], $box[3], $box[5], $box[7]) ); 
		$dims = array(
				//'x'=> cos(deg2rad($angle)) * $width + cos(deg2rad(90 - $angle)) * $height,
				//'y'=> cos(deg2rad($angle)) * $height + cos(deg2rad(90 - $angle)) * $width,
				'x' => $max_x - $min_x,
				'y' => $max_y - $min_y,
				'startX'=> (cos(deg2rad(90 - $angle)) * $height * 0.5+ cos(deg2rad($angle)) * $width *0.5),
				'startY'=>abs($box[7]-$box[1])
		);
		return $dims;
	}
	
	/*
	 * Nachkommastellen anzeigen
	 */
	function fixDecimals($value, $numPlaces){
		if(is_numeric($value)){
			return number_format($value, $numPlaces, ',', '.');
		}
		return $value;
	}
}
?>