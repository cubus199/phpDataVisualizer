<?php
require_once ('graphFunctions.php');
class graphRenderedImage{
	public $graphData;
	private $graphFunctions;
	private $colors = array();
	public $img;
	private $font_dir;
	private $pointCount = 1000;
	/*
	 * Erstellen eines leeren Bild und setzen der Hintergrundfarbe
	 * Benoetigt ein graphData Objekt
	 */
	function __construct($graphData){
		$this->font_dir = __DIR__.'/fonts/';
		$this->graphData = $graphData;		//temporaerer Datenspeicher fuer Graphendaten
		$this->graphFunctions = new graphFunctions($graphData, $this->font_dir);
		$this->img = @imagecreatetruecolor ($this->graphData->config["containerWidth"], $this->graphData->config["containerHeight"]) //Erzeugen eines leeren Bildes
			or die("Kann keinen neuen GD-Bild-Stream erzeugen");		//Fehlermeldung
		//Hintergundfarbe
		list($r, $g, $b) = sscanf($this->graphData->config['containerBackgroundColor'], "#%02x%02x%02x"); //Hex Farbe ins RGB Format umwandeln
		$background_color = imagecolorallocate($this->img, $r, $g, $b); //Hintergrundfarbe setzen
		imagefill($this->img, 0, 0, $background_color);
		imagesetthickness ($this->img, 1);//Setzen der Standardzeichendicke auf 1 Punkt
	}
	/*
	 * Zeichnen eines Balkendiagramms
	 */
	function drawVertBarGraph($stacked = false){
		$this->createXYAxes($stacked, false, true, false, false, 1);	//Achsen zeichnen
		if($this->graphData->config['gridEnabled']){
			$this->createGrid(1,2);	//Gitter zeichnen
		}
		$this->writeTitle();			//Titel hinzufuegen
		$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
		$datasets = $this->graphData->getDatasets();//uebergebene Daten zwischenspeichern
		$i = 0;//Zaehler
		foreach($datasets as $dataset){
			$x1 = $this->graphFunctions->graph['x1'] + $i * $this->graphFunctions->graph['scaleNonNumericX'] + 0.5*$this->graphData->config['graphComponentSpacing'];
			if($i == 0){
				$x1 += (($this->graphData->config['axisThickness']-1));
			}
			$x2 = $this->graphFunctions->graph['x1'] + ($i + 1) * $this->graphFunctions->graph['scaleNonNumericX'] - 0.5*$this->graphData->config['graphComponentSpacing'] -1;
			$y2 = $this->graphFunctions->graph['y0'];
			$j = 0;
			//Setzen der Beschriftung
			$xPos = $x1 + (($x2 - 1 - $x1 - $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],$dataset->x_name,$this->graphData->config['colLabelRotation'])['x'])/2);
			$temp = $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],str_repeat('X',$this->graphData->getLimits()['xLabelLength']),$this->graphData->config['colLabelRotation']);
			$yPos = $this->graphFunctions->graph['y2'] + 3 * $this->graphData->config['axisThickness'];
			$this->xLabel($xPos, $yPos,$dataset->x_name);
			//spezielle Werte fuer Balken, falls die zusammenhaengenden Werte nebeneinander sind
			if(!$stacked){
				$x = $x1;
				$y1 = $y2;
				$y = $y2;
				$width = ($x2 - $x1)/(count($dataset->values));
			}else{
				$y_2 = $this->graphFunctions->graph['y0'];
			}
			$first = true;
			//Balken entsprechend der Werte einzeichnen
			foreach($dataset->values as $value){
				if($value != 0){
					$switch = false;
					if($stacked){	//Die verschiedenen Werte übereinander stapeln
						if($value >= 0){
							$y1 = $y2;
							$y2 = $y2 - $value * $this->graphFunctions->graph['scaleNumericY'];
						}else{
							$switch = true;
							$y_1 = $y_2;
							$y_2 = $y_1 - $value * $this->graphFunctions->graph['scaleNumericY'];
						}
					}else{			//Die Ergebnisse nebeneinander zeichnen
						$x1 = $x + ($j * $width) + $this->graphData->config['graphSubComponentSpacing']*0.5 + ($first?0:1);
						$x2 = $x + (($j+1) * $width) - $this->graphData->config['graphSubComponentSpacing']*0.5;
						$y2 = $y - $value * $this->graphFunctions->graph['scaleNumericY'];
						$first = false;
					}
					if(!$switch){
						$y1Temp = $y1;
						$y2Temp = $y2;
					}else{
						$y1Temp = $y_1;
						$y2Temp = $y_2;
					}
					$color = $this->graphData->getColor($j);
					imagefilledrectangle ($this->img, $x1, $y1Temp-1, $x2, $y2Temp-1, $this->setColorHex($color));
				}
				$j++;
			}			
			$i++;
		}
		$this->createCustomZeroLineX();
	}
	function drawHorBarGraph($stacked = false){
		$this->createXYAxes($stacked, false, false, true, true,2);	//Achsen zeichnen
		if($this->graphData->config['gridEnabled']){
			$this->createGrid(2, 1);	//Gitter zeichnen
		}
		$this->xLabels($stacked, true);
		$this->writeTitle();			//Titel hinzufuegen
		$datasets = $this->graphData->getDatasets();//uebergebene Daten zwischenspeichern
		$i = 0;
		foreach($datasets as $dataset){
			$x2 = $this->graphFunctions->graph['xFlipped0'];
			$y1 = $this->graphFunctions->graph['y1'] + $i * $this->graphFunctions->graph['scaleNonNumericY'] + 0.5*$this->graphData->config['graphComponentSpacing']+1;
			$y2 = $this->graphFunctions->graph['y1'] + ($i+1) * $this->graphFunctions->graph['scaleNonNumericY'] - 0.5*$this->graphData->config['graphComponentSpacing'];
			$wordLength = $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],$dataset->x_name);
			//Beschriftung
			$xPos = $this->graphFunctions->graph['x1'] - 3 * $this->graphData->config['axisThickness'] - $wordLength['x'];
			$yPos = $this->graphFunctions->graph['y1'] + (($i+0.5) * $this->graphFunctions->graph['scaleNonNumericY']) +  0.5 * $wordLength['y'];
			$this->xLabel($xPos, $yPos,$dataset->x_name, true);
			
			$first = true;
			$j = 0;
			if(!$stacked){
				$x = $x2;
				$x1 = $x2;
				$y = $y1;
				$width = ($y2- $y1)/(count($dataset->values));
			}else{
				if($i == (count($datasets) - 1)){
					$y2 -= ($this->graphData->config['axisThickness']-1);
				}
				$x_2 = $this->graphFunctions->graph['xFlipped0'];
			}
			foreach($dataset->values as $value){
				if($value != 0){
					$switch = false;
					if($stacked){
						if($value >= 0){
							$x1 = $x2;
							$x2 = $x1 + $value * $this->graphFunctions->graph['scaleNumericFlippedX'];
						}else{
							$switch = true;
							$x_1 = $x_2;
							$x_2 = $x_1 + $value * $this->graphFunctions->graph['scaleNumericFlippedX'];
						}
					}else{
						$y1 = $y + ($j * $width) + $this->graphData->config['graphSubComponentSpacing']*0.5 + ($first?0:1);
						$y2 = $y + (($j + 1) * $width) - $this->graphData->config['graphSubComponentSpacing']*0.5;
						if($i == (count($datasets) - 1) && $j == (count($dataset->values)-1)){
							$y2 -= ($this->graphData->config['axisThickness']-1);
						}
						$x2 = $x + $value * $this->graphFunctions->graph['scaleNumericFlippedX'];
						$first = false;
					}
					if(!$switch){
						$x1Temp = $x1;
						$x2Temp = $x2;
					}else{
						$x1Temp = $x_1;
						$x2Temp = $x_2;

					}
					$color = $this->graphData->getColor($j);
					imagefilledrectangle ($this->img, $x1Temp+1, $y1, $x2Temp+1, $y2, $this->setColorHex($color));
				}
				$j++;
			}
			$i++;
		}
		$this->createCustomZeroLineX(true);
	}
	function drawScatterGraph($stacked = false, $secondYAxis = false){
		$this->createXYAxes($stacked, $secondYAxis, true, true);	//Achsen zeichnen
		$this->createCustomZeroLineX();
		$this->createCustomZeroLineY();
		if($this->graphData->config['gridEnabled']){
			$this->createGrid(2, 2);	//Gitter zeichnen
		}
		$this->writeTitle();			//Titel hinzufuegen
		$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
		$this->xLabels();
		$this->drawPointsScatter($stacked);
	}
	/*
	 * Liniengraphen, 
	 * $connection ( 0 = direkte Verbindung, 1 = interpolierte Verbindung, 2 = interpoliert und für Extrempunkte optimiert)
	 */
	function drawLineGraph($stacked = false, $connection=0, $nonNumericXAxis = false,$secondYAxis = false){
		$this->createXYAxes($stacked,$secondYAxis, true, !$nonNumericXAxis, false,($nonNumericXAxis?1:0));	//Achsen zeichnen
		$this->createCustomZeroLineX();
		if(!$nonNumericXAxis){
			$this->createCustomZeroLineY();
		}
		if($this->graphData->config['gridEnabled']){
			$this->createGrid(($nonNumericXAxis?3:2), 2);	//Gitter zeichnen
		}
		$this->writeTitle();			//Titel hinzufuegen
		$this->yLabels($stacked);		//Y-Achsen Beschriftung hinzufuegen
		$xLabels = false;
		if(!$nonNumericXAxis){
			$this->xLabels();
			$xLabels = true;
		}
		//require_once 'interpolateCubicSplines.php';
		//$test = new CubicSplines();
		require_once 'cubicSplineInterpolation.php';
		$datasets = $this->graphData->getDatasets(!$nonNumericXAxis);//uebergebene Daten zwischenspeichern
		// $scaleX = ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']:$this->graphFunctions->graph['scaleNumericX']);
		// $scaleY = $this->graphFunctions->graph['scaleNumericY'];
		imagesetthickness ($this->img, $this->graphData->config['graphLineThickness']);//Setzen der Liniendicke
		for($i = 0; $i < count($datasets[0]->values); $i++){
			$points = array();
			$color = $this->setColorHex($this->graphData->getColor($i,true));
			$j = 0;
			foreach($datasets as $dataset){
				array_push($points, ($nonNumericXAxis?$this->graphFunctions->graph['x1']:$this->graphFunctions->graph['x0']) + ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']:$this->graphFunctions->graph['scaleNumericX']) * ($nonNumericXAxis?$j:$dataset->x_name), $this->graphFunctions->graph['y0'] - $this->graphFunctions->graph['scaleNumericY'] * $dataset->values[$i]);
				if(!$xLabels){
					$xPos = $this->graphFunctions->graph['x1'] + $this->graphFunctions->graph['scaleNonNumericLineX'] * $j -(($this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'],$dataset->x_name,$this->graphData->config['colLabelRotation'])['x'])/2);
					$temp = $this->calcWordDim($this->graphData->config['generalFont'],  $this->graphData->config['generalFontSize'], $dataset->x_name,$this->graphData->config['colLabelRotation']);
					$yPos = $this->graphFunctions->graph['y2'] + 3 * $this->graphData->config['axisThickness'] + $temp['startY'];
					$this->xLabel($xPos, $yPos,$dataset->x_name);
				}
				$j++;
			}
			$xLabels = true;
			$countData = count($datasets);
			if($connection <> 0 && $countData > 3){
				//creating 2D-array:
				$rect = array();
				for($j = 0; $j < count($points) / 2; $j++){
					array_push($rect, array($points[$j * 2], $points[$j * 2 + 1]));
				}
				
				//interpolating the spline:
				$spline = new cubicSplineInterpolation($rect, $connection == 2);
				$spline->interpolate();
				$line = $spline->getLine($this->pointCount);
				
				//flattening the result
				$points = array();
				foreach($line as $p){
					array_push($points, $p[0], $p[1]);
				}
			}
			for($k = $countData; $k < 3; $k++){
				array_push($points, $points[$countData - 2],$points[$countData - 1]);
			}
			imageopenpolygon($this->img,$points,count($points)/2,$color);
		}
		imagesetthickness ($this->img, 1);//Setzen der Standardzeichendicke auf 1 Punkt
		$this->drawPointsScatter($stacked, $nonNumericXAxis);
	}
	function drawPointsScatter($stacked = false, $nonNumericXAxis=false){
		$datasets = $this->graphData->getDatasets();//uebergebene Daten zwischenspeichern
		$limits = $this->graphData->getLimits($stacked);
		$j = 0;
		foreach($datasets as $dataset){
			$x = ($nonNumericXAxis?$this->graphFunctions->graph['x1']:$this->graphFunctions->graph['x0']) + ($nonNumericXAxis?$this->graphFunctions->graph['scaleNonNumericLineX']:$this->graphFunctions->graph['scaleNumericX']) * ($nonNumericXAxis?$j:$dataset->x_name);
			$i = 0;
			foreach($dataset->values as $value){
				$y = $this->graphFunctions->graph['y0'] - $this->graphFunctions->graph['scaleNumericY'] * $value;
				$color = $this->setColorHex($this->graphData->getColor($i));
				$size = $this->graphData->config['symbolSize'];

				$this->drawSymbol($dataset->symbols != null?$dataset->symbols[$i]:$this->graphData->row_symbols[$i], $x, $y, $color);

				$i++;
			}
			$j++;
		}
	}
	/*
	 * Kuchendiagramm
	 */
	function drawPieChart(){
		$this->writeTitle();			//Titel hinzufuegen
		$this->graphFunctions->calcCenteredGraph();
		$sumValue = 0;
		foreach($this->graphData->datasets as $dataset){
			foreach($dataset->values as $value){
				$sumValue += $value;
			}
		}
		$start = 0;
		$cx = round($this->graphFunctions->graph['x']);
		$cy = round($this->graphFunctions->graph['y']);
		$radius = $this->graphFunctions->graph['radius'];
		foreach($this->graphData->datasets as $dataset){
			$colors = ($dataset->colors != null)?$dataset->colors:$this->graphData->row_colors;
			$i = 0;
			foreach($dataset->values as $value){
				$end = round($start + ($value/$sumValue)*360);
				$color = $this->graphData->getColor($i);
				imagefilledarc ($this->img, $cx , $cy , $radius , $radius , $start , $end , $this->setColorHex($color), IMG_ARC_PIE);
				$start = $end;
				$i++;
			}
		}
	}

	function drawLegend(){
		$row_names = $this->graphData->row_names;
		$font = $this->graphData->config['generalFont'];
		if($row_names != null){
			if(count($row_names) == count($this->graphData->datasets[0]->values)){

				$maxWidth = 0;
				$maxHeight = 0;
				foreach($row_names as $name){
					$dims = $this->calcWordDim($font, $this->graphData->config['generalFontSize'], ' '.$name);
					if($dims['x'] > $maxWidth) $maxWidth = $dims['x'];
					if($dims['y']*1.6 > $maxHeight) $maxHeight = $dims['y'] *1.6;
				}

				$lineHeight = max($maxHeight, $this->graphData->config['symbolSize'] * 1.6);

				$legendWidth = $maxWidth + $this->graphData->config['symbolSize'] + 2 * $this->graphData->config['graphSubComponentSpacing'];
				$legendHeight =  $lineHeight * count($row_names) + 2 * $this->graphData->config['graphSubComponentSpacing'];
				
				switch($this->graphData->config['legendPosition']){
					case 'topLeft':
						$x = $this->graphFunctions->graph['x1'] + $this->graphData->config['graphSubComponentSpacing'];
						$y = $this->graphFunctions->graph['y1'] + $this->graphData->config['graphSubComponentSpacing'];
						break;

					default:
					case 'topRight':
						$x = $this->graphFunctions->graph['x2'] - ($legendWidth + $this->graphData->config['graphSubComponentSpacing']);
						$y = $this->graphFunctions->graph['y1'] + $this->graphData->config['graphSubComponentSpacing'];
						break;

					case 'bottomLeft':
						$x = $this->graphFunctions->graph['x1'] + $this->graphData->config['graphSubComponentSpacing'];
						$y = $this->graphFunctions->graph['y2'] - ($legendHeight + $this->graphData->config['graphSubComponentSpacing']);
						break;

					case 'bottomRight':
						$x = $this->graphFunctions->graph['x2'] - ($legendWidth + $this->graphData->config['graphSubComponentSpacing']);
						$y = $this->graphFunctions->graph['y2'] - ($legendHeight + $this->graphData->config['graphSubComponentSpacing']);
						break;
				}

				$opacity = dechex(round($this->graphData->config['legendOpacity'] * 255));
				imagefilledrectangle ($this->img, $x, $y, $x+$legendWidth, $y+$legendHeight, $this->setAlphaColorHex($this->graphData->config['containerBackgroundColor'].$opacity));
				imagerectangle($this->img, $x, $y, $x+$legendWidth, $y+$legendHeight, $this->setColorHex($this->graphData->config['legendBorderColor']));		
				
				$size = $this->graphData->config['symbolSize'];
				$lineStart = $x +  $this->graphData->config['graphSubComponentSpacing'];
				$currentLine = $y + $this->graphData->config['graphSubComponentSpacing'];
				for($i = 0; $i < count($row_names); $i++){
					$currentLine += $lineHeight;
					$color = $this->setColorHex($this->graphData->getColor($i));
					$x = $lineStart + ($this->graphData->config['symbolSize']/2);
					$y = $currentLine - ($lineHeight/2);

					$this->drawSymbol($this->graphData->row_symbols[$i], $x, $y, $color);

					imagettftext($this->img, $this->graphData->config['generalFontSize'], 0, $lineStart  + $this->graphData->config['symbolSize'], $currentLine - $lineHeight / 2 + $maxHeight * 0.25, $this->setColorHex($this->graphData->config['generalFontColor']), $this->font_dir.$this->graphData->config['generalFont'].'.ttf', ' '.$row_names[$i]);
				}
			}
		}
	}

	/*
	 * Graphgroesse Berechnung
	 */
	
	function calcWordDim($font, $size, $text, $angle = 0){
		$font_path = $this->font_dir.$font.'.ttf';
		$dimensions = imagettfbbox($size, $angle, $font_path, $text);
		return array('x'=>max(abs($dimensions[4] - $dimensions[0]),abs($dimensions[6] - $dimensions[2])),
				'y'=>max(abs($dimensions[7] - $dimensions[3]),abs($dimensions[5] - $dimensions[1])),
				'startY'=>abs($dimensions[7]-$dimensions[1]));
	}
	//Funktinen um x uns y Label zu schreiben
	function xLabel($x, $y, $text, $disableRotate=false){
		$this->label($text, $x, $y, ($disableRotate?0:- $this->graphData->config['colLabelRotation']));
	}
	function label($text, $x, $y, $angle = 0){
		$label_color = $this->setColorHex($this->graphData->config['generalFontColor']);
		$font = $this->font_dir.$this->graphData->config['generalFont'].'.ttf';
		imagettftext($this->img, $this->graphData->config['generalFontSize'], $angle, $x, $y, $label_color, $font, $text);
	}
	private function yLabels($stacked = false){ 
		if(isset($this->graphFunctions->graph['x1'])){
			$i = 0;
			$half_font_height = ($this->graphFunctions->highestYLabel/2);
			$xLabel_end = $this->graphFunctions->graph['x1'] - $this->graphData->config['axisThickness']*3;
			$label_spacing = ($this->graphFunctions->graph['y2'] - $this->graphFunctions->graph['y1'])/($this->graphData->config['yLabelCount']-1);
			foreach($this->graphFunctions->yLabels as $label){
				$y_marker = $this->graphFunctions->graph['y2'] - ($label_spacing*$i);
				$y = $y_marker + $half_font_height;
				$this->setAxisLabelMarker($this->graphFunctions->graph['x1'],$y_marker, $this->graphData->config['axisThickness']*3, $this->graphData->config['axisThickness']);
				$this->label($label['display'], $xLabel_end - $label['x'], $y);
				$i++;
			}
		}
	}
	private function xLabels($stacked = false, $useYLabel = false){
		$x = $this->graphFunctions->graph['x1'];
		$y = $this->graphFunctions->graph['y2'] + 3 * $this->graphData->config['axisThickness'];
		$dist = ($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1']) / ($this->graphData->config['xLabelCount'] - 1);
		$j = 0;
		foreach($this->graphFunctions->xLabels as $label){
			$x_mark = $x + ($j*$dist);
			$x1 = $x_mark - $label['startX'];
			$this->setAxisLabelMarker($x_mark,$this->graphFunctions->graph['y2'], $this->graphData->config['axisThickness'], $this->graphData->config['axisThickness']*3);
			$this->label($label['display'], $x1, $y + $label['startY'], -$this->graphData->config['colLabelRotation']);
			$j++;
		}
	}
	/*
	 * Labelmarkierungen an der Achse
	 */
	private function setAxisLabelMarker($x1, $y1, $width, $height){
		$axis_color = $this->setColorHex($this->graphData->config['axisColor']);
		imagefilledrectangle($this->img,$x1+(0.5*($width-1)), $y1+(0.5*($height-1)), $x1-(0.5*($width-1)), $y1-(0.5*($height-1)), $axis_color);
	}
	/*
	 * Erstellen der Achsen und berechnung des zu bezeichnenden Bereichs
	 * nonNumiericType {0: numeric(false), 1: x nonNumeric, 2: y nonNumeric}
	 */
	function createXYAxes($stacked = false, $secondYAxis = false, $disabledXAxis = false, $disableYAxis = false, $axesSwapped = false, $labelType = 0){
		$axis_color = $this->setColorHex($this->graphData->config['axisColor']);
		$font = $this->graphData->config['generalFont'];
		//Graphen berechnen
		$this->graphFunctions->calcGraphSize($stacked, false, $axesSwapped, $labelType);
		//Achsen zeichnen
		if(!$disableYAxis){
			imagefilledrectangle($this->img,$this->graphFunctions->graph['x1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['x1'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'] + (($this->graphData->config['axisThickness']-1)*0.5), $axis_color);	//Y Achse zeichnen
		}
		if(!$disabledXAxis){
			imagefilledrectangle($this->img,$this->graphFunctions->graph['x1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['x2'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'] - (($this->graphData->config['axisThickness']-1)*0.5), $axis_color);	//X Achse zeichnen
		}
		if($secondYAxis){
			imagefilledrectangle($this->img,$this->graphFunctions->graph['x2'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['x2'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'] + (($this->graphData->config['axisThickness']-1)*0.5), $axis_color);	//Y Achse zeichnen
		}
	}
	function createCustomZeroLineX($xYflipped = false){
		$axis_color = $this->setColorHex($this->graphData->config['axisColor']);
		if($xYflipped){
			imagefilledrectangle($this->img,$this->graphFunctions->graph['xFlipped0'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['xFlipped0'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'] + (($this->graphData->config['axisThickness']-1)*0.5), $axis_color);	//Y Achse zeichnen
		}else{
			imagefilledrectangle($this->img,$this->graphFunctions->graph['x1'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y0'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['x2'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y0'] - (($this->graphData->config['axisThickness']-1)*0.5), $axis_color);	//X Achse zeichnen
		}
	}
	function createCustomZeroLineY(){
		$axis_color = $this->setColorHex($this->graphData->config['axisColor']);
		imagefilledrectangle($this->img,$this->graphFunctions->graph['x0'] - (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y1'], $this->graphFunctions->graph['x0'] + (($this->graphData->config['axisThickness']-1)*0.5), $this->graphFunctions->graph['y2'], $axis_color);	//X Achse zeichnen
	}
	/*
	 * Zeichnen des Gitters
	 * Grid-Position 0=>disabled, 1=>amount of keys, 2=>value based, 3 => amount of keys -1
	 */
	function createGrid($x_position = 0, $y_position = 0){
		// Y-Achsen Linien zeichnen
		if($y_position != 0){//Keine Y-Achsen Linien zeichnen
			if($y_position == 1){	//Y-Achsen Linien abhaengig der Datenanzahl zeichnen
				$data_count = Count($this->graphData->getDatasets());
			}else if($y_position == 2){		//Y-Achsen Linien abhaenig von der Skalierung zeichnen
				$data_count = $this->graphData->config['yLabelCount']-1;
			}else{
				$data_count = Count($this->graphData->getDatasets())-1;
			}
			$label_spacing = ($this->graphFunctions->graph['y2'] - $this->graphFunctions->graph['y1'])/($data_count);
			for($i = 0; $i <= $data_count;$i++){
				$y = $this->graphFunctions->graph['y2'] - ($label_spacing*$i);
				imagedashedline($this->img, $this->graphFunctions->graph['x1'],$y, $this->graphFunctions->graph['x2'],$y, $this->setColorHex($this->graphData->config['gridColor']));
			}
		}
		
		// X-Achse Linien zeichnen
		if($x_position != 0){	//Keine X-Achse zeichnen
			if($x_position == 1){	//X-Achse abhaengig der Datenanzahl
				$data_count = Count($this->graphData->getDatasets());
			}else if($x_position == 2){		//X-Achsen Linien abhaenig von der Skalierung zeichnen
				$data_count = $this->graphData->config['xLabelCount']-1;
			}else{
				$data_count = Count($this->graphData->getDatasets())-1;
			}
			$bar_width = (($this->graphFunctions->graph['x2'] - $this->graphFunctions->graph['x1'])/$data_count);
			for($i = 0; $i <= $data_count;$i++){
				$x = $this->graphFunctions->graph['x1'] + ($bar_width*$i);
				imagedashedline($this->img, $x,$this->graphFunctions->graph['y1'], $x,$this->graphFunctions->graph['y2'], $this->setColorHex($this->graphData->config['gridColor']));
			}
		}
	}
	/*
	 * Titel schreiben
	 */
	function writeTitle(){
		$font = $this->graphData->config['generalFont'];
		switch($this->graphData->config['graphTitlePosition']){
			case 'left':	//Titel linksbuendig schreiben
				$x = $this->graphData->config['leftPadding']; 
				break;
			case 'center':	//Titel zentriert schreiben
				$x = $this->graphData->config['leftPadding']+(($this->graphData->config["containerWidth"] - $this->graphData->config['leftPadding'] - $this->graphData->config['rightPadding'] - $this->calcWordDim($this->graphData->config['generalFont'], $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['x']) / 2); //zentrieren des Titels
				break;
			case 'right':	//Titel rechtsbuendig schreiben
				$x = $this->graphData->config["containerWidth"] - $this->graphData->config['rightPadding'] - $this->calcWordDim($this->graphData->config['generalFont'], $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['x'];
				break;
		}
		$y = $this->graphData->config['topPadding'] + $this->calcWordDim($font, $this->graphData->config['graphTitleFontSize'], $this->graphData->title)['y'];
		imagettftext($this->img, $this->graphData->config['graphTitleFontSize'], 0, $x, $y, $this->setColorHex($this->graphData->config['graphTitleColor']), $this->font_dir.$this->graphData->config['generalFont'].'.ttf', $this->graphData->title);
	}

	function drawSymbol($name, $x, $y, $color){
		switch($name){
			case 'square':
				imagefilledrectangle ($this->img, $x - $this->graphData->config['symbolSize']*0.5, $y - $this->graphData->config['symbolSize']*0.5, $x + $this->graphData->config['symbolSize']*0.5, $y + $this->graphData->config['symbolSize']*0.5, $color);
				break;
			case 'circle':
			default:
				imagefilledellipse ($this->img, $x , $y , $this->graphData->config['symbolSize'], $this->graphData->config['symbolSize'], $color);
				break;
			case 'cross':
				$c = 0.25 * $this->graphData->config['symbolSize'];
				$b = $c * sqrt(0.5);
				$d = $this->graphData->config['symbolSize'] / 2;
				$a = $d - $b;
				$cross = array(
					$x,($y - $b),
					($x + $a),($y - $d),
					($x + $d),($y - $a),
					($x + $b),$y,
					($x + $d),($y + $a),
					($x + $a),($y + $d),
					$x,($y + $b),
					($x - $a),($y + $d),
					($x - $d),($y + $a),
					($x - $b),$y,
					($x - $d),($y - $a),
					($x - $a),($y - $d)
				);	

				imagefilledpolygon($this->img,$cross,count($cross)/2,$color);
				break;
			case 'triangle':
				$points = array($x, $y -($this->graphData->config['symbolSize'] /4 * sqrt(3)),
							$x -($this->graphData->config['symbolSize'] / 2), $y + ($this->graphData->config['symbolSize'] / 4 * sqrt(3)),
							$x +($this->graphData->config['symbolSize'] / 2), $y + ($this->graphData->config['symbolSize'] / 4 * sqrt(3))
							);
				imagefilledpolygon($this->img,$points,count($points)/2, $color);
				break;
			case 'diamond':
				$points = array($x, $y - $this->graphData->config['symbolSize'] / 2,
							$x + $this->graphData->config['symbolSize'] / 2, $y,
							$x, $y + $this->graphData->config['symbolSize'] / 2,
							$x - $this->graphData->config['symbolSize'] / 2, $y
							);
				imagefilledpolygon($this->img,$points,count($points)/2, $color);
				break;
		}
	}

	/*
	 * Farben erstellen
	 */
	function setAlphaColorHex($hex){
		if(!isset($this->colors[$hex])){
			list($r, $g, $b, $a) = sscanf($hex, '#%02x%02x%02x%02x'); //Hex Farbe ins RGB Format umwandeln
			$this->colors[$hex] = $this->setColor($r, $g, $b, 127- floor($a / 2));
		}
		return $this->colors[$hex];
	}
	function setColorHex($hex){
		if(!isset($this->colors[$hex])){
			list($r, $g, $b) = sscanf($hex, '#%02x%02x%02x'); //Hex Farbe ins RGB Format umwandeln
			$this->colors[$hex] = $this->setColor($r, $g, $b);
		}
		return $this->colors[$hex];
	}
	function setColor($r, $g, $b, $a = 0){
		return imagecolorallocatealpha($this->img, $r, $g, $b, $a);
	}
	/*
	 * Bild ausgeben
	 */
	function outputImg(){
		if($this->graphData->config['showLegend']) $this->drawLegend();
		imagepng($this->img);
		imagedestroy($this->img);
	}
}