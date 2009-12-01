<?php
class FreeWheel_Partner_Category extends FreeWheel {

	protected $key_name = "partner_category";
	protected $keys = array();
	protected $root_path = "/services/partner_category";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name"									=>	"string:3,255",
			"description"							=>	"string:4096",
			"metadata"								=>	"string:4096",
			"partner_category_type:required"		=>	"enum:CONTENT_OWNER,DISTRIBUTOR"
		);
	}
	
	public function create($data)
	{
		$this->method = "POST";
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($this->root_path . ".xml", 1);
	}
	
	public function find($v=NULL, $role=NULL)
	{
		if (is_numeric($v)) {
			$path = $this->root_path . "/" . $v . ".xml";
		}
		else {
			$query = array();
			$role = strtolower($role);
			if (!is_null($v) && !empty($v)) {
				array_push($query, "keyword=" . $v);
			}
			if ($role == "content_owner" || $role == "distributor") {
				array_push($query, "role=" . $role);
			}
			$query = implode("&", $query);
			$query = (!empty($query)) ? "?" . $query : NULL;
			$path = $this->root_path . ".xml" . $query;
			$v = "all";
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function find_parent_category($id=NULL, $type=NULL)
	{
		$type = strtolower($type);
		if (!is_numeric($v)) {
			$this->set_error("The ID must be numeric to find all IO's by campaign.");
			return false;
		}
		
		if ((is_null($type) || empty($type)) || ($type != "content_owner" && $type != "distributor")) {
			$this->set_error("You must set a valid category type in find the parent category.");
			return false;
		}
		$this->method = "GET";
		return $this->call("/services/partner/" . $v . "/partner_category/get_parent_category.xml?partner_category_type=" . $type, 1);
	}
}
?>