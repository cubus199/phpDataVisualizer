<?php
require_once ('graphFunctions.php');
print_r('<script src="/lib/graphs/graphSVGImage.js" async></script>');
class graphSVGImage{
	public $graphData;
	private $graphFunctions;
	private $config;
	public $svg;
	private $font_dir;
	public $type;
	private $pointCount = 1000;
	/*
	 * Erstellen eines leeren Bild und setzen der Hintergrundfarbe
	 */
	function __construct($graphData){
		$this->font_dir = __DIR__.'/fonts/';		//Font Speicherort
		$this->graphData = $graphData;		//temporaerer Datenspeicher fuer Graphendaten
		$this->config = $graphData->config; //config zwischenspeichern
		$this->graphFunctions = new graphFunctions($graphData, $this->font_dir);
		print_r('
		<style>
			@font-face {
			font-family: "'.$this->graphData->config['generalFont'].'";
			src: url("fonts/'.$this->graphData->config['generalFont'].'.ttf");
			}
		</style>
		');
	}
	/*
	 * CSS-Eigenschaften des SVG-Elements bestimmen
	 */
	function readStyle(){
		$style = array();
		$style []= 'box-sizing: border-box';
		if(isset($this->config['containerBackgroundColor'])) $style []= 'background: '.$this->config['containerBackgroundColor'];
		return implode(';', $style);
	}
	/*
	 * Zeichnen eines Balkendiagramms
	 */
	function drawVertBarGraph($stacked = false){
		$this->type = 'vertBarGraph';
		$this->drawBasics($stacked, 1);
		$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
		if($this->config['gridEnabled'] == true){
			$this->createGrid(1,2);	//Gitter zeichnen
		}
		$datasets = $this->graphData->getDatasets();
		$i = 0;
		foreach($datasets as $dataset){
			$x1 = $this->graphFunctions->graph['x1'] + $i * $this->graphFunctions->graph['scaleNonNumericX'] + 0.5*$this->graphData->config['graphComponentSpacing'];
			$x2 = $this->graphFunctions->graph['x1'] + ($i+1) * $this->graphFunctions->graph['scaleNonNumericX'] - 0.5*$this->graphData->config['graphComponentSpacing'];
			$y2 = $this->graphFunctions->graph['y0'];
			$j = 0;
			//spezielle Werte fuer Balken, falls die zusammenhaengenden Werte nebeneinander sind
			if($stacked == false){
				$x = $x1;
				$y1 = $y2;
				$y = $y2;
				$width = ($x2 - $x1)/(count($dataset->values));
			}else{
				$y_2 = $y2;
			}
			
			$first = true;
			foreach($dataset->values as $value){
				if($value != 0){
					$switch = false;
					if($stacked){	//Die verschiedenen Werte übereinander stapeln
						if($value > 0){
							$y1 = $y2;
							$y2 = $y2 - $value * $this->graphFunctions->graph['scaleNumericY'];
						}else{
							$switch = true;
							$y_1 = $y_2;
							$y_2 = $y_1 - $value * $this->graphFunctions->graph['scaleNumericY'];
						}
					}else{			//Die Ergebnisse nebeneinander zeichnen
						$x1 = $x + ($j * $width) + $this->graphData->config['graphSupComponentSpacing']*0.5;
						$x2 = $x + (($j+1) * $width) - $this->graphData->config['graphSupComponentSpacing']*0.5;
						$y2 = $y - $value * $this->graphFunctions->graph['scaleNumericY'];
						if($value < 0){
							$switch = true;
							$y_2 = $y2;
							$y_1 = $y1;
						}
					}
					if(($y1 - ($this->graphData->config['axisThickness'])*0.5) < $this->graphFunctions->graph['y2'] || !$first && !$stacked){
						$color = $this->graphData->getColor($j);
						$yTemp = ($first || !$stacked)?$y1 - ($this->graphFunctions->graph['y2']):$y1;
						$this->svg .= '<rect class="bar" x="'.$x1.'" y="'.($switch?$y_1:$y2).'" width="'.($x2 - $x1).'" height="'.($switch?($y_2 - $y_1):($y1 - $y2)).'" style="fill: '.$color.';" />';
						$first = false;
					}
				}
				$j++;
			}
			$i++;
		}
		
		
		//Beschriftung der x-Achse
		$this->xLabels($stacked, false, true);
		
		$this->drawXYAxes(false, true, false);
		$this->createCustomZeroLineX();
	}
	function drawHorBarGraph($stacked = false){
		$this->type = 'horBarGraph';
		$this->drawBasics($stacked, 2, true);
		if($this->graphData->config['gridEnabled']){
			$this->createGrid(2, 1);	//Gitter zeichnen
		}
		$this->xLabels($stacked, true);
		$this->writeTitle();			//Titel hinzufuegen
		$datasets = $this->graphData->getDatasets();//uebergebene Daten zwischenspeichern
		$bar_width_scale = $this->graphFunctions->graph['scaleNumericFlippedX'];
		$i = 0;
		foreach($datasets as $dataset){
			$x2 = $this->graphFunctions->graph['xFlipped0'];
			$y1 = $this->graphFunctions->graph['y1'] + $i * ($this->graphFunctions->graph['scaleNonNumericY']) + 0.5*$this->graphData->config['graphComponentSpacing'];
			$y2 = $this->graphFunctions->graph['y1'] + ($i+1) * ($this->graphFunctions->graph['scaleNonNumericY']) - 0.5*$this->graphData->config['graphComponentSpacing'];
			$wordLength = $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],$dataset->x_name);
			
			$xPos = $this->graphFunctions->graph['x1'] - $this->config['axisThickness'] * 4;
			$yPos = $this->graphFunctions->graph['y1'] + (($i+0.5) * $this->graphFunctions->graph['scaleNonNumericY']) +  0.5 * $wordLength['y'];
			$this->yLabel($xPos, $yPos,$dataset->x_name);
			
			$first = true;
			$j = 0;
			if(!$stacked){
				$x = $x2;
				$x1 = $x2;
				$y = $y1;
				$width = ($y2- $y1)/(count($dataset->values));
			}else{
				$x_2 = $x2;
			}
			foreach($dataset->values as $value){
				if($value != 0){
					$switch = false;
					if($stacked){
						if($value > 0){
							$x1 = $x2;
							$x2 = $x1 + $value * ($bar_width_scale);
						}else{
							$x_1 = $x_2;
							$x_2 = $x_1 + $value * ($bar_width_scale);
							$switch = true;
						}
					}else{
						$y1 = $y + ($j * $width) + $this->graphData->config['graphSupComponentSpacing']*0.5;
						$y2 = $y + (($j + 1) * $width) - $this->graphData->config['graphSupComponentSpacing']*0.5;
						$x2 = $x + $value * $bar_width_scale;
						if($value < 0){
							$x_1 = $x1;
							$x_2 = $x2;
							$switch = true;
						}
					}
					if(($this->graphFunctions->graph['x1'] + $this->graphData->config['axisThickness']-1)*0.5 < $x2 || !$first && !$stacked){
						$xTemp = ($first || !$stacked)?$x1 + ($this->graphData->config['axisThickness'])*0.5:$x1;
						$color = $this->graphData->getColor($j);
						$this->svg .= '<rect class="bar" x="'.($switch?$x_2:$xTemp).'" y="'.$y1.'" width="'.($switch?($x_1-$x_2):($x2 - $xTemp)).'" height="'.($y2 - $y1).'" style="fill: '.$color.';" />';
						$first = false;
					}
				}
				$j++;
			}
			$i++;
		}
		
		$this->drawXYAxes(false, false, true);
		$this->createCustomZeroLineX(true);
	}
	function drawScatterGraph($stacked = false, $skipBasics = false,$nonNumericXAxis = false){
		$this->type = 'scatterGraph';
		if(!$skipBasics){
			$this->drawBasics($stacked, $nonNumericXAxis ? 1 : 0);
			$this->drawXYAxes(false, true, !$nonNumericXAxis);
			$this->createCustomZeroLineX();
			if(!$nonNumericXAxis){
				$this->createCustomZeroLineY();
			}
			$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
			$this->xLabels();			
		}
		
		if($this->config['gridEnabled'] == true){
			$this->createGrid(($nonNumericXAxis?3:2),2);	//Gitter zeichnen
		}
		
		$datasets = $this->graphData->getDatasets(!$nonNumericXAxis);//uebergebene Daten zwischenspeichern
		$limits = $this->graphData->getLimits($stacked);
		$j = 0;
		foreach($datasets as $dataset){
			$symbols = $this->graphData->row_symbols;
			if($dataset->symbols != null){
				$symbols = $dataset->symbols;
			}
			$x = ($nonNumericXAxis?$this->graphFunctions->graph['x1']:$this->graphFunctions->graph['x0']) +  ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']*$j:$this->graphFunctions->graph['scaleNumericX'] *$dataset->x_name);
			$i = 0;
			foreach($dataset->values as $value){
				$y = $this->graphFunctions->graph['y0'] - $this->graphFunctions->graph['scaleNumericY'] * $value;
				if(!isset($symbols[$i])){
					$symbols[$i] = 'circle';
				}
				$color = $this->graphData->getColor($i);
				switch($symbols[$i]){
					case 'square':
						$this->svg .= '<rect class="scatterPoint" pointtype="square" xval="'.$dataset->x_name.'" yval="'.$value.'" x="'.($x - ($this->config['symbolSize'] / 2)).'" y="'.($y - ($this->config['symbolSize'] / 2)).'" width="'.$this->config['symbolSize'].'" height="'.$this->config['symbolSize'].'" fill="'.$color.'" />';
						break;
					case 'circle':
					default:
						$this->svg .= '<circle class="scatterPoint" pointtype="circle" xval="'.$dataset->x_name.'" yval="'.$value.'" cx="'.$x.'" cy="'.$y.'" r="'.($this->config['symbolSize'] / 2).'" fill="'.$color.'" />';
						break;
					case 'cross':
						$c = 0.25 * $this->config['symbolSize'];
						$b = $c * sqrt(0.5);
						$d = $this->config['symbolSize'] / 2;
						$a = $d - $b;
						$cross = array();	
						$cross[0] = $x.','.($y - $b);
						$cross[1] = ($x + $a).','.($y - $d);
						$cross[2] = ($x + $d).','.($y - $a);
						$cross[3] = ($x + $b).','.$y;
						$cross[4] = ($x + $d).','.($y + $a);
						$cross[5] = ($x + $a).','.($y + $d);
						$cross[6] = $x.','.($y + $b);
						$cross[7] = ($x - $a).','.($y + $d);
						$cross[8] = ($x - $d).','.($y + $a);
						$cross[9] = ($x - $b).','.$y;
						$cross[10] = ($x - $d).','.($y - $a);
						$cross[11] = ($x - $a).','.($y - $d);
						$this->svg .= '<polygon class="scatterPoint" pointtype="cross" xval="'.$dataset->x_name.'" yval="'.$value.'" points="'.implode(' ', $cross).'" fill="'.$color.'" />';
						break;
					case 'triangle':
						$this->svg .= '<polygon class="scatterPoint" pointtype="triangle" xval="'.$dataset->x_name.'" yval="'.$value.'" points="'.$x.','.($y -($this->config['symbolSize'] / 4 * sqrt(3))).' '.($x +($this->config['symbolSize'] / 2)).','.($y + ($this->config['symbolSize'] / 4 * sqrt(3))).' '.($x -($this->config['symbolSize'] / 2)).','.($y + ($this->config['symbolSize'] / 4 * sqrt(3))).'" fill="'.$color.'" />';
						break;
				}
				$i++;
			}
			$j++;
		}
	}
	function drawLineGraph($stacked = false, $connection=0, $nonNumericXAxis = false, $secondYAxis = false){
		$this->type = 'lineGraph';
		$this->drawBasics($stacked, $nonNumericXAxis ? 1 : 0);
		$this->drawXYAxes(false, true, !$nonNumericXAxis);
		if(!$nonNumericXAxis){
			$this->createCustomZeroLineY();
		}
		$this->createCustomZeroLineX();
		$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
		$this->xLabels();
		$datasets = $this->graphData->getDatasets(!$nonNumericXAxis);
		//$scaleX = ($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1'])/($nonNumericXAxis?count($datasets)-1:$this->graphData->getXLabels(2)[1]);
		//$scaleY = ($this->graphFunctions->graph['y2'] - $this->graphFunctions->graph['y1'])/$this->graphData->getYLabels(2, $stacked)[1];
		
		$linePoints = array();
		for($i = 0; $i < count($datasets) - 1; $i++){
			for($j = 0; $j < count($datasets[$i]->values); $j++){
				$x1 = ($nonNumericXAxis?$this->graphFunctions->graph['x1']:$this->graphFunctions->graph['x0']) +  ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']*($i):$this->graphFunctions->graph['scaleNumericX'] *$datasets[$i]->x_name);
				$y1 = $this->graphFunctions->graph['y0'] - $this->graphFunctions->graph['scaleNumericY'] * $datasets[$i]->values[$j];
				$x2 = ($nonNumericXAxis?$this->graphFunctions->graph['x1']:$this->graphFunctions->graph['x0']) +  ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']*($i+1):$this->graphFunctions->graph['scaleNumericX'] *$datasets[$i + 1]->x_name);
				$y2 = $this->graphFunctions->graph['y0'] - $this->graphFunctions->graph['scaleNumericY'] * $datasets[$i + 1]->values[$j];
				//$x2 = $this->graphFunctions->graph['x1'] + $scaleX * ($nonNumericXAxis?$i+1:$datasets[$i + 1]->x_name);
				//$y2 = $this->graphFunctions->graph['y2'] - $scaleY * $datasets[$i + 1]->values[$j];
				$color = $this->graphData->getColor($j,true);
				if($connection == 0){
					$this->svg .= '<line class="graphLine" x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke-linecap="round" style="stroke: '.$color.'; stroke-width: '.$this->graphData->config['graphLineThickness'].'; " />';
				}else{
					$linePoints[$j] []= array($x1, $y1);
					if($i == count($datasets) - 2){
						$linePoints[$j] []= array($x2, $y2);
					}
				}
			}
			/*
			if($nonNumericXAxis){
				//Beschriftung der x-Achse
				$xPos = $x1 - (($this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],$datasets[$i]->x_name)['x'])/2);
				$temp = $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],str_repeat('X',$this->graphData->getLimits(true)['xLabelLength']),$this->graphData->config['colLabelRotation']);
				$yPos = $this->graphData->config['containerHeight']-$this->graphData->config['bottomPadding']-($temp['y']-$temp['startY']);
				$this->xLabel($xPos, $yPos,$datasets[$i]->x_name);
			}
			*/					
		}
		if($connection != 0){
			require_once 'cubicSplineInterpolation.php';
			$j = 0;
			foreach($linePoints as $line){
				$spline = new cubicSplineInterpolation($line, $connection == 2);
				$spline->interpolate();
				$lineOut = $spline->getLine($this->pointCount);
				$color = $this->graphData->getColor($j, true);
				$this->svg .= '<polyline class="graphCubicLine" points="'.implode(' ', array_map(function ($element){return implode(',', $element);}, $lineOut)).'" stroke-linecap="round" style="fill: transparent; stroke: '.$color.'; stroke-width: '.$this->graphData->config['graphLineThickness'].'; " />';
				$j++;
			}
		}
		$this->drawScatterGraph($stacked, true, $nonNumericXAxis);
	}
	/*
	 * Kuchendiagramm
	 */
	function drawPieChart(){
		$this->type = 'pieChart';
		$this->drawBasics();
		$this->graphFunctions->calcCenteredGraph();
		$sumValue = 0;
		foreach($this->graphData->datasets as $dataset){
			foreach($dataset->values as $value){
				$sumValue += $value;
			}
		}
		$start = 0;
		$cx = $this->graphFunctions->graph['x'];
		$cy = $this->graphFunctions->graph['y'];
		$radius = $this->graphFunctions->graph['radius']/2;
		$startX = $cx+$radius;
		$startY = $cy;
		foreach($this->graphData->datasets as $dataset){
			$colors = ($dataset->colors != null)?$dataset->colors:$this->graphData->row_colors;
			$i = 0;
			foreach($dataset->values as $value){
				//$end = round($start + ($value/$sumValue)*360);
				$angle = ($value/$sumValue)*360;
				$temp = $this->rotatePoint(array($cx, $cy), array($startX,$startY), $angle);
				$endX = $temp[0];
				$endY = $temp[1];
				$color = $this->graphData->getColor($i);
				//imagefilledarc ($this->img, $cx , $cy , $radius , $radius , $start , $end , $this->setColorHex($color), IMG_ARC_PIE);
				$this->svg .= '<path d="M '.$cx.' '.$cy.' L '.$startX.' '.$startY.' A '.$radius.' '.$radius.' 0 '.($angle >= 180 ? 1 : 0).' 1 '.$endX.' '.$endY.'" fill="'.$color.'" ></path>';
				//echo ($value/$sumValue).';start'.$start.';Ende'.$end.'|';
				//$start = $end;
				$startX = $endX;
				$startY = $endY;
				$i++;
			}
		}
	}
	/*
	 *	Netzdiagramm
	 */
	function drawRadarChart($stacked = false){
		$this->type = 'radarChart';
		$this->drawBasics($stacked, 1);
		$this->graphFunctions->calcCenteredGraph();
		$cx = $this->graphFunctions->graph['x'];
		$cy = $this->graphFunctions->graph['y'];
		$radius = $this->graphFunctions->graph['radius']/2;
		$x = $cx+$radius;
		$y = $cy;
		$angle = (1/count($this->graphData->datasets))*360;
		//drawing axes & grid:
		$j = 0;
		foreach($this->graphData->datasets as $dataset){
			for($i = 0; $i < count($this->graphFunctions->yLabels); $i++){
				$init = array($cx + ($i * $radius / (count($this->graphFunctions->yLabels)-1)), $cy);
				$current = $this->rotatePoint(array($cx, $cy), $init, $angle * $j);
				$next = $this->rotatePoint(array($cx, $cy), $init, $angle * ($j + 1));
				if($i > 0){ // skip grid at the centerpoint
					$this -> svg .= '<line x1="'.$current[0].'" y1="'.$current[1].'" x2="'.$next[0].'" y2="'.$next[1].'" style="stroke:'.$this->graphData->config['gridColor'].'; stroke-width: 0.4; stroke-dasharray: 4,4;" />'; //drawing grid
				}
				if($j == 0){ // only draw tickmarks and labels at the first axis
					$tickX = $cx + ($i * $radius / (count($this->graphFunctions->yLabels)-1));
					$this->addTickMark($tickX, false, $cy);
					$label = $this->graphFunctions->yLabels[$i];
					$this->xLabel($tickX, $cy + $label['startY'], $label['display']);
				}
			}
			
			$this->svg .= '<line x1="'.$cx.'" y1="'.$cy.'" x2="'.$x.'" y2="'.$y.'" stroke-linecap="round" style="stroke: '.$this->config['axisColor'].'; stroke-width: '.$this->graphData->config['axisThickness'].'; " />'; // drawing axes
			$this->xLabel($x,$y,$this->graphFunctions->xLabels[$j]['display']);
			$temp = $this->rotatePoint(array($cx, $cy), array($x,$y), $angle);
			$x = $temp[0];
			$y = $temp[1];
			$j++;
		}
		
		$j = 0;
		foreach($this->graphData->datasets as $dataset){
			$i = 0;
			foreach($dataset->values as $value){
				$color = $this->graphData->getColor($i);
				$sec_color = $this->graphData->getColor($i, true);
				
				//calculate scaling:
				$labelsY = $this->graphData->getYLabels(2, $stacked);
				$scaling = (((abs($labelsY[0]) + $labelsY[1]) == 0)?0:($radius)/(abs($labelsY[0]) + $labelsY[1]));
				
				if($j == 0){
					$xmodPrev = $scaling * end($this->graphData->datasets)->values[$i];
				}else{
					$xmodPrev = $scaling * $this->graphData->datasets[$j-1]->values[$i];
				}
				$initPrev = array($cx + $xmodPrev, $cy);
				if($j == 0){
					$turnedPrev = $this->rotatePoint(array($cx, $cy), $initPrev, $angle * (count($this->graphData->datasets)-1));
				}else{
					$turnedPrev = $this->rotatePoint(array($cx, $cy), $initPrev, $angle * ($j-1));
				}
				
				
				$xmod = $scaling * $value;
				$init = array($cx + $xmod, $cy);
				$turned = $this->rotatePoint(array($cx, $cy), $init, $angle * $j);
				
				$this->svg .= '<line class="graphLine" x1="'.$turnedPrev[0].'" y1="'.$turnedPrev[1].'" x2="'.$turned[0].'" y2="'.$turned[1].'" stroke-linecap="round" style="stroke: '.$sec_color.'; stroke-width: '.$this->graphData->config['graphLineThickness'].'; " />';
				
				$this->svg .= '<circle xval="'.$dataset->x_name.'" yval="'.$value.'" cx="'.$turned[0].'" cy="'.$turned[1].'" r="'.($this->config['symbolSize'] / 2).'" fill="'.$color.'" />';
				$i++;
			}
			$j++;
		}
	}

	function drawLegend(){

		$row_names = $this->graphData->row_names;
		$font = $this->graphData->config['generalFont'];
		if($row_names != null){
			if(count($row_names) == count($this->graphData->datasets[0]->values)){
				$legend = '<g id="legend">';

				$maxWidth = 0;
				$maxHeight = 0;
				foreach($row_names as $name){
					$dims = $this->calcWordDim($font, $this->config['generalFontSize'], ' '.$name);
					if($dims['x'] > $maxWidth) $maxWidth = $dims['x'];
					if($dims['y']*1.6 > $maxHeight) $maxHeight = $dims['y'] *1.6;
				}

				$lineHeight = max($maxHeight, $this->config['symbolSize'] * 1.6);

				$legendWidth = $maxWidth + $this->config['symbolSize'] + 2 * $this->config['graphSupComponentSpacing'];
				$legendHeight =  $lineHeight * count($row_names) + 2 * $this->config['graphSupComponentSpacing'];

				switch($this->config['legendPosition']){
					case 'topLeft':
						$x = $this->graphFunctions->graph['x1'] + $this->config['graphSupComponentSpacing'];
						$y = $this->graphFunctions->graph['y1'] + $this->config['graphSupComponentSpacing'];
						break;

					default:
					case 'topRight':
						$x = $this->graphFunctions->graph['x2'] - ($legendWidth + $this->config['graphSupComponentSpacing']);
						$y = $this->graphFunctions->graph['y1'] + $this->config['graphSupComponentSpacing'];
						break;

					case 'bottomLeft':
						$x = $this->graphFunctions->graph['x1'] + $this->config['graphSupComponentSpacing'];
						$y = $this->graphFunctions->graph['y2'] - ($legendHeight + $this->config['graphSupComponentSpacing']);
						break;

					case 'bottomRight':
						$x = $this->graphFunctions->graph['x2'] - ($legendWidth + $this->config['graphSupComponentSpacing']);
						$y = $this->graphFunctions->graph['y2'] - ($legendHeight + $this->config['graphSupComponentSpacing']);
						break;
				}

				

				$legend .= '<rect x="'.$x.'" y="'.$y.'" width="'.$legendWidth.'" height="'.$legendHeight.'" style="stroke: '.$this->config['legendBorderColor'].'; fill: '.$this->config['containerBackgroundColor'].'; fill-opacity:'.$this->config['legendOpacity'].';" />';

				$lineStart = $x +  $this->config['graphSupComponentSpacing'];
				$currentLine = $y + $this->config['graphSupComponentSpacing'];
				for($i = 0; $i < count($row_names); $i++){
					$currentLine += $lineHeight;
					$color = $this->graphData->getColor($i);
					$x = $lineStart + ($this->config['symbolSize']/2);
					$y = $currentLine - ($lineHeight/2);
					switch($this->graphData->row_symbols[$i]){
						case 'square':
							$legend .= '<rect pointtype="square" x="'.($x - ($this->config['symbolSize'] / 2)).'" y="'.($y - ($this->config['symbolSize'] / 2)).'" width="'.$this->config['symbolSize'].'" height="'.$this->config['symbolSize'].'" fill="'.$color.'" />';
							break;
						case 'circle':
						default:
							$legend .= '<circle pointtype="circle" cx="'.$x.'" cy="'.$y.'" r="'.($this->config['symbolSize'] / 2).'" fill="'.$color.'" />';
							break;
						case 'cross':
							$c = 0.25 * $this->config['symbolSize'];
							$b = $c * sqrt(0.5);
							$d = $this->config['symbolSize'] / 2;
							$a = $d - $b;
							$cross = array();	
							$cross[0] = $x.','.($y - $b);
							$cross[1] = ($x + $a).','.($y - $d);
							$cross[2] = ($x + $d).','.($y - $a);
							$cross[3] = ($x + $b).','.$y;
							$cross[4] = ($x + $d).','.($y + $a);
							$cross[5] = ($x + $a).','.($y + $d);
							$cross[6] = $x.','.($y + $b);
							$cross[7] = ($x - $a).','.($y + $d);
							$cross[8] = ($x - $d).','.($y + $a);
							$cross[9] = ($x - $b).','.$y;
							$cross[10] = ($x - $d).','.($y - $a);
							$cross[11] = ($x - $a).','.($y - $d);
							$legend .= '<polygon pointtype="cross" points="'.implode(' ', $cross).'" fill="'.$color.'" />';
							break;
						case 'triangle':
							$legend .= '<polygon pointtype="triangle" points="'.$x.','.($y -($this->config['symbolSize'] / 4 * sqrt(3))).' '.($x +($this->config['symbolSize'] / 2)).','.($y + ($this->config['symbolSize'] / 4 * sqrt(3))).' '.($x -($this->config['symbolSize'] / 2)).','.($y + ($this->config['symbolSize'] / 4 * sqrt(3))).'" fill="'.$color.'" />';
							break;
					}

					$legend .= '<text x="'.($lineStart  + $this->config['symbolSize']).'" y="'.($currentLine - $lineHeight / 2 + $maxHeight * 0.25).'" style="fill: '.$this->config['generalFontColor'].'; font-family: '.$this->config['generalFont'].'; font-size: '.$this->config['generalFontSize'].'pt;">&nbsp;'.$row_names[$i].'</text>';
				}

				$legend .= '</g>';
				$this->svg .= $legend;
			}
		}
	}

	function drawBasics($stacked = false, $labelType = false, $swapAxes = false){
		$this->calcGraph($stacked, $labelType, false, $swapAxes);
		$this->writeTitle();	//Titel hinzufuegen
	}

	function calcWordDim($font, $size, $text, $angle = 0){                               
		$font  = $this->font_dir.$font.'.ttf';
		$dimensions = imagettfbbox($size, $angle, $font, $text);
		return array('x'=>max(abs($dimensions[4] - $dimensions[0]),abs($dimensions[6] - $dimensions[2])),
				'y'=>max(abs($dimensions[7] - $dimensions[3]),abs($dimensions[5] - $dimensions[1])),
				'startY'=>abs($dimensions[7]-$dimensions[1]));
	}

	function addTickMark($pos, $Ymode = false, $customVal = null){
		$tickLength = 1.5;
		if($Ymode){
			if($customVal != null){
				$x = $customVal;
			}else{
				$x = $this->graphFunctions->graph['x1'];
			}
			$this->svg .= '<line x1="'.($x - ($this->config['axisThickness'] * $tickLength)).'" y1="'.$pos.'" x2="'.($x + ($this->config['axisThickness'] * $tickLength)).'" y2="'.$pos.'" style="stroke:'.$this->config['axisColor'].';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}else{
			if($customVal != null){
				$y = $customVal;
			}else{
				$y = $this->graphFunctions->graph['y2'];
			}
			$this->svg .= '<line x1="'.$pos.'" y1="'.($y - ($this->config['axisThickness'] * $tickLength)).'" x2="'.$pos.'" y2="'.($y + ($this->config['axisThickness'] * $tickLength)).'" style="stroke:'.$this->config['axisColor'].';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}
	}

	function xLabels($stacked = false, $ySwapped = false, $nonNumeric = false){
		$labels = $this->graphFunctions->xLabels;
		$x = $this->graphFunctions->graph['x1'];
		if($nonNumeric) $x += ($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1']) / count($labels) / 2;
		$y = $this->graphFunctions->graph['y2'] + 3*$this->config['axisThickness'];
		$dist = ($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1']) / ((count($labels)- ($nonNumeric ? 0 : 1)==0?1:count($labels) - ($nonNumeric ? 0 : 1)));
		foreach($labels as $label){
			$this->xLabel($x - ($nonNumeric ? $label['x'] / 2 : $label['startX']), $y + $label['startY'], $label['display']);
			if(!$nonNumeric) $this->addTickMark($x);
			$x += $dist;
		}
	}

	private function yLabels($stacked = false){
		if(isset($this->graphFunctions->graph['x1'])){
			$yLabels = '';
			$font = $this->graphData->config['generalFont'];
			$count = $this->graphData->config['yLabelCount'];
			$i = 0;
			$x = $this->graphFunctions->graph['x1'] - $this->config['axisThickness'] * 3;
			//$font_height = ($this->calcWordDim($font,$this->graphData->config['generalFontSize'], "0.0")['y']/2);
			$label_spacing = ($this->graphFunctions->graph['y2'] - $this->graphFunctions->graph['y1'])/($count-1);
			foreach($this->graphFunctions->yLabels as $label){
				$y = $this->graphFunctions->graph['y2'] - ($label_spacing*$i) + $label['y'] / 2;
				$this->yLabel($x, $y, $label['display']);
				$this->addTickMark($this->graphFunctions->graph['y2'] - ($label_spacing*$i), true);
				$i++;
			}
			$this->svg .= $yLabels;
		}
	}

	private function xLabel($x, $y, $text, $center = false){
		$args = "";
		if($center){
			$args .= ' text-anchor="middle" ';
		}
		if($this->config['colLabelRotation'] != 0){
			$args .= ' transform="rotate('.$this->config['colLabelRotation'].' '.$x.','.$y.')" ';
		}
		$this->svg .= '<text class="xLabel" x="'.$x.'" y="'.$y.'" '.$args.' style="fill: '.$this->config['generalFontColor'].'; font-family: '.$this->config['generalFont'].'; font-size: '.$this->config['generalFontSize'].'pt;">'.$text.'</text>';
	}
	private function yLabel($x, $y, $text){
		$this->svg .= '<text class="yLabel" x="'.$x.'" y="'.$y.'" text-anchor="end" style="fill: '.$this->config['generalFontColor'].'; font-family: '.$this->config['generalFont'].'; font-size: '.$this->config['generalFontSize'].'pt;">'.$text.'</text>';
	}
	function calcGraph($stacked, $labelType = 0, $sort = false, $swapAxes = false){
		$limits = $this->graphData->getLimits(true);
		$this->graphFunctions->calcGraphSize($stacked, false, $swapAxes, $labelType);
		$xLabels = "";
		if($this->graphData->numericData() && ($labelType == 0 || $labelType == 1)){
			$xLabels = ';'.implode(';', $this->graphData->getxLabels(2, $stacked));
			$this->config['xType'] = 'numeric';
		}else{
			$this->config['xType'] = 'string';
		}
		$row_colors = null;
		if($this->graphData->row_colors != null){
			$row_colors = implode(',', $this->graphData->row_colors);
		}
		$sec_row_colors = null;
		if($this->graphData->sec_row_colors != null){
			$sec_row_colors = implode(',', $this->graphData->sec_row_colors);
		}
		$row_symbols = null;
		if($this->graphData->row_symbols != null){
			$row_symbols = implode(',', $this->graphData->row_symbols);
		}
		$this->svg = '<svg id="'.$this->graphData->id.'" graphtype="'.$this->type.'" graphframe="'.implode(';', $this->graphFunctions->graph).';'.implode(';', $this->graphData->getyLabels(2, $stacked)).$xLabels.'" graphconfig="'.str_replace('"', '\'', json_encode($this->config)).'" datasets="'.str_replace('"', '\'', json_encode($this->graphData->getDatasets($sort))).'" presets="'.$row_colors.';'.$sec_row_colors.';'.$row_symbols.'" viewBox="0 0 '.$this->config['containerWidth'].' '.$this->config['containerHeight'].'" width="100%" style="'.$this->readStyle().'">'; //svg-element beginnen
	}

	function drawXYAxes($secondYAxis = false, $disableXAxis = false, $disableYAxis = false){
		$axes = '';
		$axis_color = $this->graphData->config['axisColor'];
		if(!$disableYAxis){
			$axes .= '<line x1="'.$this->graphFunctions->graph['x1'].'" y1="'.$this->graphFunctions->graph['y1'].'" x2="'.$this->graphFunctions->graph['x1'].'" y2="'.$this->graphFunctions->graph['y2'].'" style="stroke:'.$axis_color.';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}
		if(!$disableXAxis){
			$axes .= '<line x1="'.$this->graphFunctions->graph['x1'].'" y1="'.$this->graphFunctions->graph['y2'].'" x2="'.$this->graphFunctions->graph['x2'].'" y2="'.$this->graphFunctions->graph['y2'].'" style="stroke:'.$axis_color.';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}
		$this->svg .= $axes;
	}

	function createCustomZeroLineX($xYflipped = false){
		$axes = '';
		$axis_color = $this->graphData->config['axisColor'];
		if($xYflipped){
			$axes .= '<line x1="'.$this->graphFunctions->graph['xFlipped0'].'" y1="'.$this->graphFunctions->graph['y1'].'" x2="'.$this->graphFunctions->graph['xFlipped0'].'" y2="'.$this->graphFunctions->graph['y2'].'" style="stroke:'.$axis_color.';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}else{
			$axes .= '<line x1="'.$this->graphFunctions->graph['x1'].'" y1="'.$this->graphFunctions->graph['y0'].'" x2="'.$this->graphFunctions->graph['x2'].'" y2="'.$this->graphFunctions->graph['y0'].'" style="stroke:'.$axis_color.';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		}
		$this->svg .= $axes;
	}

	function createCustomZeroLineY(){
		$axes = '';
		$axis_color = $this->graphData->config['axisColor'];
		$axes .= '<line x1="'.$this->graphFunctions->graph['x0'].'" y1="'.$this->graphFunctions->graph['y1'].'" x2="'.$this->graphFunctions->graph['x0'].'" y2="'.$this->graphFunctions->graph['y2'].'" style="stroke:'.$axis_color.';stroke-width:'.$this->graphData->config['axisThickness'].'" />';
		$this->svg .= $axes;
	}

	function createGrid($x_position = 0, $y_position = 0){
		$grid = '';		
		if($y_position != 0){
			if($y_position == 1){
				$data_count = Count($this->graphData->getDatasets());
			}else if($y_position == 2){
				$data_count = $this->graphData->config['yLabelCount'] - 1;
			}else{
				$data_count = Count($this->graphData->getDatasets()) - 1;
			}
			$label_spacing = ($this->graphFunctions->graph['y2'] - $this->graphFunctions->graph['y1'])/($data_count);
			for($i = 0; $i <= $data_count;$i++){
				$y = $this->graphFunctions->graph['y2'] - ($label_spacing*$i);
				$grid .= '<line x1="'.$this->graphFunctions->graph['x1'].'" y1="'.$y.'" x2="'.$this->graphFunctions->graph['x2'].'" y2="'.$y.'" style="stroke:'.$this->graphData->config['gridColor'].'; stroke-width: 0.4; stroke-dasharray: 4,4;" />';
			}
		}
		if($x_position != 0){
			if($x_position == 1){
				$data_count = Count($this->graphData->getDatasets());
			}else if($x_position == 2){
				$data_count = $this->graphData->config['xLabelCount'] - 1; //, $stacked
			}else{
				$data_count = Count($this->graphData->getDatasets()) - 1;
			}
			$bar_width = (($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1'])/($data_count==0?1:$data_count));
			for($i = 0; $i <= $data_count;$i++){
				$x = $this->graphFunctions->graph['x1'] + ($bar_width*$i);
				$grid .= '<line x1="'.$x.'" y1="'.$this->graphFunctions->graph['y1'].'" x2="'.$x.'" y2="'.$this->graphFunctions->graph['y2'].'" style="stroke:'.$this->graphData->config['gridColor'].'; stroke-width: 0.4; stroke-dasharray: 4,4;" />';
			}
		}
		$this->svg .= $grid;
	}
	function writeTitle(){
		$font = $this->graphData->config['generalFont'];
		if($this->config['graphTitlePosition'] == 'left'){
			$x = $this->graphData->config['leftPadding']; 
			$alignment = 'start';
		}else if($this->config['graphTitlePosition'] == 'right'){
			$x = $this->config['containerWidth'] - $this->graphData->config['rightPadding'];
			$alignment = 'end';
		}else{
			$x = (($this->graphData->config["containerWidth"] - $this->graphData->config['leftPadding'] - $this->graphData->config['rightPadding'] ) / 2) + $this->graphData->config['leftPadding']; //zentrieren des Titels
			$alignment = 'middle';
		}
		$y = $this->graphData->config['topPadding'] + $this->calcWordDim($font, $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['y'];
		$this->svg .= '<text x="'.$x.'" y="'.$y.'" text-anchor="'.$alignment.'" style="font-family: '.$this->config['generalFont'].'; font-size: '.$this->config['graphTitleFontSize'].'pt; fill: '.$this->config['graphTitleColor'].';"><tspan >'.$this->graphData->title.'</tspan></text>'; 
	}

	function getSVG(){
		if($this->config['showLegend']) $this->drawLegend();
		return $this->svg.'</svg>';
	}

	private function rotatePoint($origin, $point, $angle){
		$s = sin($angle * M_PI/180);
		$c = cos($angle * M_PI/180);

		// translate point back to origin:
		$point[0] -= $origin[0];
		$point[1] -= $origin[1];

		// rotate point
		$xnew = $point[0] * $c - $point[1] * $s;
		$ynew = (double) ($point[0] * $s) + (double) ($point[1] * $c);

		// translate point back:
		$point[0] = (double) $xnew + (double) $origin[0];
		$point[1] = (double) $ynew + (double) $origin[1];
		return $point;
	}
}