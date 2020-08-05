<?php
require_once 'GraphScaling.php';
interface GraphIF{
	public function getGraphScaling() : GraphScaling;
	public function drawGraph(drawingAgentIF &$drawingAgentIF, GraphScaling $scale) : void;
}
?>