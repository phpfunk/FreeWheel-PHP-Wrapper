<?php
class FreeWheel_Network extends FreeWheel {

	protected $key_name = "network";
	protected $root_path = "/services/network";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
	}
	
	public function find($v=NULL)
	{
		if (empty($v) || is_null($v)) {
			$this->set_error("You must supply either a network ID or a keyword to search by.");
			return false;
		}
		
		$path = (is_numeric($v)) ? "/" . $v . ".xml" : ".xml?keyword=" . urlencode($v);
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
}
?>