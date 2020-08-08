<?php
class Font{
	public string $path;
	public string $name;

    /**
     * font constructor.
     * @param string $path
     * @param string $name
     */
	function __construct(string $path, string $name){
		$this->path = $path;
		$this->name = $name;
	}
}
?>