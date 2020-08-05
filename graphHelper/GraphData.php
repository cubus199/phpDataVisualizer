<?php
class GraphData{
	public array $datasets;
	public array $row_colors;
	public array $row_symbols;

	/**
	 * creates a new GraphData object
	 */
	public function __construct(array $datasets, array $row_colors = null, array $row_symbols = null){
		$this->datasets = $datasets;
		$this->row_colors = $row_colors;
		$this->row_symbols = $row_symbols;
	}

	/**
	 * add a new dataset to the GraphData object
	 */
	public function addDataset(Dataset $dataset){
		array_push($this->datasets, $dataset);
	}
}
?>