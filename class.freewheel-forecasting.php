<?php
class FreeWheel_Forecasting extends FreeWheel {

	protected $key_name = "forcasting_result";
	protected $root_path = "/services/forecasting";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
	}
	
	public function find($id=NULL, $type="nightly", $tag=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The Placement ID must be numeric to extract forecasting results.");
			return false;
		}
		
		$query = NULL;
		$type = strolower($type);
		if (!is_null($tag)) {
			$type = "demand_result";
			$query = "?tag=" . $tag;
		}
		
		if ($type == "demand_result" && is_null($tag)) {
			$xml = $this->call($this->root_path . "/" . $id . "/nightly.xml", 1);
			if (!$this->errors()) {
				$this->find($id, $type, $xml['tag']);
				exit;
			}
			else {
				return false;
			}
		}
		$this->method = "GET";
		return $this->call($this->root_path . "/" . $id . "/" . $type . ".xml" . $query, 1);
	}
	
}
?>