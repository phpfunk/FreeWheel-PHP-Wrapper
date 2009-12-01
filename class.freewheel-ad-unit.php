<?php
class FreeWheel_Ad_Unit extends FreeWheel {

	protected $key_name = "ad_unit";
	protected $root_path = "/services";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
	}
	
	public function find($v=NULL)
	{
		$v = (is_null($v) || empty($v)) ? "all" : strtolower(trim($v));
		
		if (is_numeric($v)) {
			$path = $this->root_path . "/ad_unit/" . $v . ".xml";
		}
		elseif ($v == "custom") {
			$path = $this->root_path . "/ad_unit.xml";
		}
		else {
			$path = $this->root_path . "/ad_unit/all.xml";
			$v = "all";
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
}
?>