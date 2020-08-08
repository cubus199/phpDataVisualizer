<?php
require_once 'GraphScaling.php';
interface GraphIF{
    /**
     * @return GraphScaling basic scaling information
     */
	public function getGraphScaling() : GraphScaling;

    /**
     * Draws the graph
     * @param drawingAgentIF $drawingAgentIF
     * @param GraphScaling $scale
     */
	public function drawGraph(drawingAgentIF &$drawingAgentIF, GraphScaling $scale) : void;
}
?>