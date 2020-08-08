<?php
require_once 'GraphIF.php';
require_once 'GraphData.php';
class LineGraph implements GraphIF{
	public GraphData $gData;

    /**
     * creating a new LineGraph object
     * @param GraphData $gData
     */
	public function __construct(GraphData $gData){
		$this->gData = $gData;
	}

	/**
	 * calculating the scaling
	 */
	public function getGraphScaling() : GraphScaling{
		return $this->gData->getLimits();
	}

    /**
     * drawing finally the graph
     * @param drawingAgentIF $drawingAgentIF
     * @param GraphScaling $scale
     */
	public function drawGraph(drawingAgentIF &$drawingAgentIF, GraphScaling $scale) : void{
		$points = array();
		foreach($this->gData->getDatasets() as $dataset){
			$x = $dataset->x_name * $scale->calcScaleX() + ($scale->graphX1 - $scale->minX);
			$y = $scale->graphY2 - $scale->calcScaleY() * ($dataset->values[0] - $scale->minY);
			array_push($points, $x, $y);
		}
		$drawingAgentIF->drawPolyLine($points, $width = 1, $this->gData->row_colors[0]);
	}
}