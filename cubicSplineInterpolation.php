<?php
class cubicSplineInterpolation{
	public $points = array();
	public $splines = array();
	
	function __construct($points, $detectPeaksAndValleys = false){
		if(is_array($points)){
			$this->points = $points;
			if($detectPeaksAndValleys){
				$this->peakValleyDetection();
			}
			return 'success';
		}else{
			return 'error';
		}
	}
	
	function interpolate(){
		$n = count($this->points);
		if($this->points != array() && $n > 3){
			$this->splines[0]['c'] = $this->splines[$n-1]['c'] = 0;
			$Hs = array();
			$Hs[0] = $this->points[1][0] - $this->points[0][0];
			$alphas[0] = $betas[0] = 0;
			for($i = 0; $i < $n; $i++){
				$this->splines[$i]['x'] = $this->points[$i][0];
				$this->splines[$i]['a'] = $this->points[$i][1];
				if($i >0 && $i < $n-1){
					$Hs[$i] = $this->points[$i+1][0] - $this->points[$i][0]; //h = [next point] - [current point]
					$A = $Hs[$i-1];
					$B = $Hs[$i];
					$C = 2.0 * ($Hs[$i-1] + $Hs[$i]);
					$F = 6.0 * (($this->points[$i+1][1] - $this->points[$i][1]) / $Hs[$i] - ($this->points[$i][1] - $this->points[$i-1][1])/ $Hs[$i-1]);
					$z = ($A *  $alphas[$i - 1] + $C);
					$alphas[$i] = - $B / $z;
					$betas[$i] = ($F - $A * $betas[$i - 1]) / $z;
				}
			}
			for($i = $n - 1; $i > 0; $i--){
				$j = $i-1;
				if($j > 0 && $j <= $n - 2){
					$this->splines[$j]['c'] = $alphas[$j] * $this->splines[$j+1]['c'] + $betas[$j];
				}
				$this->splines[$i]['d'] = ($this->splines[$i]['c'] - $this->splines[$i-1]['c']) / $Hs[$i - 1];
				$this->splines[$i]['b'] = $Hs[$i - 1] * (2.0 * $this->splines[$i]['c'] + $this->splines[$i - 1]['c']) / 6.0 + ($this->points[$i][1] - $this->points[$i - 1][1]) / $Hs[$i-1];
			}
		}
	}
	
	function getVal($x){
		$n = count($this->points);
		if($x >= $this->points[0][0] && $x <= $this->points[$n-1][0]){
			$i;
			for($i = 1; $i < $n && $x > $this->points[$i][0]; $i++);
			$spline = $this->splines[$i];
			$dx = $x - $spline['x'];
			return $spline['a'] + ($spline['b'] + ($spline['c'] / 2.0 + $spline['d'] * $dx / 6.0) * $dx) * $dx;
		}
		return null;
	}
	
	function getLine($pointCount){
		if($pointCount > 1){
			$n = count($this->points);
			$line = array();
			for($i = $this->points[0][0]; $i <= $this->points[$n-1][0]; $i += ($this->points[$n-1][0] - $this->points[0][0]) / ($pointCount - 1)){
				array_push($line, array($i, $this->getVal($i)));
			}
			return $line;
		}
		return null;
	}
	
	function peakValleyDetection(){
		$n = count($this->points);
		if($n > 1){
			$outPoints = array();
			$last = array();
			$next = array();
			for($i = 0; $i < $n - 1; $i++){
				$current = $this->points[$i];
				array_push($outPoints, $current);
				$next = $this->points[$i+1];
				if($last != array()){
					if(($last[1] < $current[1] && $next[1] < $current[1])||($last[1] > $current[1] && $next[1] > $current[1])){
						array_push($outPoints, array($current[0]+0.000001, $current[1]));
					}
				}
				$last = $current;
			}
			array_push($outPoints, $this->points[$n-1]);
		}
		$this->points = $outPoints;
	}
}
?>