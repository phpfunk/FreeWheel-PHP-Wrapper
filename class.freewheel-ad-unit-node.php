<?php
class FreeWheel_Ad_Unit_Node extends FreeWheel {

	protected $key_name = "ad_unit_node";
	protected $keys = array();
	protected $root_path = "/services";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"price_model:required"		=>	"enum:CPM",
			"price"						=>	"decimal:16,2",
			"ad_unit_id:required"		=>	"integer",
			"placement_id"				=>	"integer"
		);
	}
	
	public function create($data, $id=NULL)
	{
		if (!is_null($id)) {
			if (!is_numeric($id)) {
				$this->set_error("The ID must be numeric in order to update an ad unit node."); 
				return false;
			}

			$this->method = "PUT";
			$path = $this->root_path . "/ad_unit_node/" . $id . ".xml";
			
			//Cannot update price_model || placement_id
			if (isset($data['price_model'])) { unset($data['price_model']); }
			if (isset($data['placement_id'])) { unset($data['placement_id']); }
		}
		else {
			//Must be set to CPM
			if ($data['price_model'] != "CPM") { $data['price_model'] = "CPM"; }
			$this->method = "POST";
			$path = $this->root_path . "/placement/" . $data['placement_id'] . "/ad_unit_node.xml";
			if (isset($data['placement_id'])) { unset($data['placement_id']); }
		}

		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($path, 1);
	}
	
	public function delete($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The ID must be numeric in order to delete an ad unit node."); 
			return false;
		}
		
		$this->method = "DELETE";
		return $this->call($this->root_path . "/ad_unit_node/" . $id . ".xml");
	}
	
	public function find($v=NULL, $placement=FALSE)
	{
		if (is_null($v) || empty($v) || !is_numeric($v)) {
			$path = $this->root_path . "/ad_unit_node.xml";
			$v = "all";
		}
		else {
			$path = $this->root_path;
			$path .= ($placement === TRUE) ? "/placement/" . $v . "/ad_unit_node.xml" : "/ad_unit_node/" . $v . ".xml";
			$v = ($placement === TRUE) ? "all" : $v;
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function update($data, $id)
	{
		return $this->create($data, $id);
	}
}
?>