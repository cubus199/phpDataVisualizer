<?php
class GraphScaling{
	public int $graphX1;//X coordinate of the upper left corner
	public int $graphY1;//Y coordinate of the upper left corner
	public int $graphX2;//X coordinate of the bottom right corner
	public int $graphY2;//Y coordinate of the bottom right corner
	public float $maxX;// scaling in X direction
	public float $maxY;// scaling in Y direction
	public float $minX;// scaling in X direction
	public float $minY;// scaling in Y direction

	public function __construct(int $x1, int $y1, int $x2, int $y2, float $maxX, float $maxY, float $minX = 0, float $minY = 0){
		$this->graphX1 = $x1;
		$this->graphY1 = $y1;
		$this->graphX2 = $x2;
		$this->graphY2 = $y2;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->minX = $minX;
		$this->minY = $minY;
	}

	/**
	 * calculates the X scaling according to the given limits
	 */
	public function calcScaleX(){
		$height = $x2 - $x1;
		return $height / ($maxX - $minX);
	}

	/**
	 * calculates the y scaling according to the given limits
	 */
	public function calcScaleY(){
		$width = $y2 - $y1;
		return $width / ($maxY - $minY);
	}
}
?>