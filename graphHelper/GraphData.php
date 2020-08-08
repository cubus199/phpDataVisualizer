<?php
require_once 'DataSet.php';
class GraphData{
	public array $datasets;
	public array $row_colors;
	public array $row_symbols;

    /**
     * creates a new GraphData object
     * @param array $datasets
     * @param array $row_colors
     * @param array $row_symbols
     */
	public function __construct(array $datasets, array $row_colors = array(), array $row_symbols = array()){
		$this->datasets = $datasets;
		$this->row_colors = $row_colors;
		$this->row_symbols = $row_symbols;
	}

    /**
     * add a new dataset to the GraphData object
     * @param Dataset $dataset
     */
	public function addDataset(Dataset $dataset) : void{
		array_push($this->datasets, $dataset);
	}

    /**
     * return all Datasets
     * @return array of datasets
     */
	public function getDatasets() : array{
		return $this->datasets;
	}

    /**
     * get the data limits
     * @return GraphScaling object containing all important scaling information, needs to be resized
     */
	public function getLimits(){
		$maxX = 0;
		$minX = 0;
		$maxY = 0;
		$minY = 0;
		$labelXWidth = 0;
		$labelXHeight = 0;
		$labelYWidth = 0;
		$labelYHeight = 0;
		return new GraphScaling($labelXWidth,$labelXHeight,-$labelYWidth,-$labelYHeight,$maxX,$maxY,$minX,$minY);
	}
}
?>