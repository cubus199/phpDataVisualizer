<?php
define('THEME_DIR', __DIR__.'/themes');
/*
$config=array(
	"containerWidth" => 600,					// width of the whole graphing space
	"containerHeight" => 250,					// height of the whole graphing space
	"containerBackgroundColor" => "#ffffff",	// background-color of the whole graphing space
	"graphTitleFontSize" => 20,					// fontsize of the graphs title
	"graphTitleColor" => "#303030", 			// color of the graphs title
	"graphTitlePosition" => "center",			// position of the graphs title (left,center,right)
	"generalFont" => "Arial",				// font-family of the title
	"generalFontSize" => 16,
	"generalFontColor" => "#303030",			// font-color of all text except the title
	"axisThickness" => 1,						// thickness of the axes
	"axisColor" => "#3F3F3F",					// color of the axes
	"gridEnabled" => false,
	"gridColor" => "#505050",					// color of the grid
	"leftPadding" => 20,						// padding
	"rightPadding" => 20,
	"topPadding" => 20,
	"bottomPadding" => 20,
	"colLabelRotation" => 0,					// rotation of the column labels
	"showLegend" => false,						// displaying the graphs legend
	"xAxisLabel" => "",								// label of the x-axis
	"yAxisLabel" => "",								// label of the y-axis
	"xPrecision" => 1,							// number of decimal places on x-axis
	"yPrecision" => 1,							// number of decimal places on y-axis
	"yLabelCount" => 7
);
*/

class graphData{
	public $title;			//String Anzeigename
	public $id; 			//String Identifikation
	public $row_names;		//String Name der Datenreihen (fuer die Legende)
	public $datasets;		//Array Datensaetze
	public $row_colors;		//Array Farben
	public $sec_row_colors;	//Array Farben
	public $row_symbols;	//Array Symbole
	public $config;			//Object Konfiguration
	
	function __construct($title, $id, $datasets = null, $theme=null, $nconfig=array(), $row_names = null){
		$this->title = $title;
		if($id == null){
			$temp = str_ireplace(" ", "", $id);
			$id = strtolower($temp[0]).substr($temp, 1);
		}
		$this->id = $id;
		include THEME_DIR.'/default.php';
		if($theme != null){
			include THEME_DIR.'/'.$theme;
			$config = array_merge($defaultConfig, $config);
		}else{
			$config = $defaultConfig;
		}
		
		$this->config = array_merge($config, $nconfig);
		$this->row_names = $row_names;
		$this->sec_row_colors = null;
		$this->datasets = $datasets;
	}
	
	function addDataset($dataset){
		if($dataset instanceof dataset){
			if($this->datasets == null){
				$this->datasets = array();
			}
			array_push($this->datasets, $dataset);
			return true;
		}
		return 'no valid dataset provided';
	}
	
	function compareDatasets($a, $b){
		if($a->x_name == $b->x_name) return 0;
		return ($a->x_name < $b->x_name) ? -1 : 1;
	}
	
	function getDatasets($sorted = false){
		if($this->datasets != null){
			$dataset = $this->datasets;
			if($sorted && $this->numericData()){
				usort($dataset, array($this, "compareDatasets"));
			}
			return $dataset;
		}
		return 'no datasets found';
	}

	function clearDatasets(){
		$this->datasets = array();
	}
	
	function getLimits($stacked = false){
		if($this->datasets != null){
			$labels =$this->getYLabels(2);
			$limits = array(
				"xCount" => count($this->datasets),			// number of datasets
				"maxValue" => $this->getMaxValue($stacked),	// maximum y-value
				"minValue" => $this->getMinValue($stacked), // minimum y-value
				"maxKey" => $this->getMaxKey(),				// longest / largest x-value
				"xLabelLength" => 0,						// length of longest x-label
				"yLabelLength" => strlen(end($labels))		// length of longest y-label
			);
			
			foreach($this->datasets as $dataset){
				if(strlen($dataset->x_name) > $limits['xLabelLength']){
					$limits['xLabelLength'] = strlen($dataset->x_name);
				}
			}
			return $limits;
		}
		return 'no datasets found';
	}
	
	function getMaxKey(){
		if($this->datasets != null){
			$max = -2147483647;
			$numeric = $this->numericData();
			if($numeric){
				$max = "";
			}			
			
			foreach($this->datasets as $dataset){
				if($numeric){
					if($dataset->x_name > $max){
						$max = $dataset->x_name;
					}
				}else{
					if(strlen($dataset->x_name) > strlen($max)){
						$max = $dataset->x_name;
					}
				}
			}
			return $max;
		}
		return 'no datasets found';
	}
	
	function getMinKey(){
		if($this->datasets != null){
			$min = 0;
			$numeric = $this->numericData();
			
			foreach($this->datasets as $dataset){
				if($numeric){
					if($dataset->x_name < $min){
						$min = $dataset->x_name;
					}
				}
			}
			return $min;
		}
		return 'no datasets found';
	}
	
	function numericData(){
		if($this->datasets != null){
			$numeric = true;
			foreach($this->datasets as $dataset){
				if(!is_numeric($dataset->x_name)){
					$numeric = false;
					break;
				}
			}
			return $numeric;
		}
		return 'no datasets found';
	}
	
	function getMaxValue($stacked = false){
		if($this->datasets != null){
			$max = -2147483647;
			foreach($this->datasets as $dataset){
				if($stacked){
					$neg = null;
					$pos = null;
					foreach ($dataset->values as $val){
						if($val > 0){
							$pos += $val;
						}else if($val < 0){
							$neg += $val;
						}else if($val == 0){
							$neg += 0;
							$pos += 0;
						}
					}
					if($pos != null && $neg != null){
						if(max($pos, $neg) > $max){
							$max = max($pos, $neg);
						}
					}else if($pos == null){
						if($neg > $max){
							$max = $neg;
						}
					}else{
						if($pos > $max){
							$max = $pos;
						}
					}
				}else{
					foreach($dataset->values as $value){
						if($value > $max){
							$max = $value;
						}
					}
				}
			}
			return $max;
		}
		return 'no datasets found';
	}

	function getMinValue($stacked = false){
		if($this->datasets != null){
			$min = 2147483647;
			foreach($this->datasets as $dataset){
				if($stacked){
					$neg = null;
					$pos = null;
					foreach ($dataset->values as $val){
						if($val > 0){
							$pos += $val;
						}else if($val < 0){
							$neg += $val;
						}else if($val == 0){
							$neg += 0;
							$pos += 0;
						}
					}
					if($pos != null && $neg != null){
						if(min($pos, $neg) < $min){
							$min = min($pos, $neg);
						}
					}else if($pos == null){
						if($neg < $min){
							$min = $neg;
						}
					}else{
						if($pos < $min){
							$min = $pos;
						}
					}
				}else{
					foreach($dataset->values as $value){
						if($value < $min){
							$min = $value;
						}
					}
				}
			}
			return $min;
		}
		return 'no datasets found';
	}
	
	function getYLabels($labelCount, $stacked = false, $axisShift = false){
		if($this->datasets != null){
			$limits = $this->calcLimits($this->getMinValue($stacked), $this->getMaxValue($stacked), $axisShift);
			$labels = array();
			for($i = 0; $i < $labelCount; $i++){
				array_push($labels, $limits[0] + ($limits[1]-$limits[0]) / ($labelCount-1) * $i);
			}
			return $labels;
		}
		return 'no datasets found';
	}
	
	function getXLabels($labelCount, $axisShift = false){
		if($this->datasets != null){
			if($this->numericData()){
				$limits = $this->calcLimits($this->getMinKey(), $this->getMaxKey(), $axisShift);
				$labels = array();
				for($i = 0; $i < $labelCount; $i++){
					array_push($labels, $limits[0] + ($limits[1] - $limits[0]) / ($labelCount-1) * $i);
				}
				return $labels;
			}
			return 'x-labels are not numeric';
		}
		return 'no datasets found';
	}

	function calcLimits($min, $max, $shiftAxes = false){
		$minVal = 0;
		$maxVal = 0;
		if($max > 0 || $shiftAxes){
			$maxVal = $max;
		}
		if($min < 0 || $shiftAxes){
			$minVal = $min;
		}


		$scope = min($this->getDecimalShift($minVal), $this->getDecimalShift($maxVal));
		$minVal = floor($minVal * pow(10, $scope));
		$maxVal = ceil($maxVal * pow(10, $scope));
		
		/*while(!($minVal == -1 || $minVal % 10 == 0 || $minVal % 5 == 0)){
			$minVal--;
		}
		while(!($maxVal == 1|| $maxVal % 10 == 0 || $maxVal % 5 == 0)){
			$maxVal++;
		}*/
		$limits = array(
			array('mult' => 2),
			array('mult' => 5),
			array('mult' => 10)
		);
		
		foreach($limits as &$limit){
			$limit['min'] = $this->nearestMultiple($minVal, false, $limit['mult']) / pow(10, $scope);
			$limit['max'] = $this->nearestMultiple($maxVal, true, $limit['mult']) / pow(10, $scope);
			$limit['dist'] = $limit['max'] - $limit['min'];
		}
		
		$optKey = array_keys(array_column($limits, 'dist'), min(array_column($limits, 'dist')))[0];
		
		return array($limits[$optKey]['min'] , $limits[$optKey]['max']);
	}
	
	function nearestMultiple($value, $direction /* true => up; false => down*/, $multiplier){
		if(!$direction) $value = -$value;
		while($value % $multiplier != 0){
			$value += 1;
		}
		if(!$direction) $value = - $value;
		return $value;
	}
	
	function getDecimalShift($number){
		$number = abs($number);
		$shift = 0;
		if($number < 1 && $number > 0){
			while($number < 1){
				$number *= 10;
				$shift++;
			}
		}
		return $shift;
	}
	
	function getColor($id, $secondary = false){
		if(isset($this->row_colors[$id]) && !$secondary || isset($this->sec_row_colors[$id]) && $secondary){
			if(!$secondary) return $this->row_colors[$id];
			return $this->sec_row_colors[$id];
		}
		if(!$secondary) return $this->config['primaryColors'][$id % count($this->config['primaryColors'])];
		return $this->config['secondaryColors'][$id % count($this->config['secondaryColors'])];
	}
}
?>