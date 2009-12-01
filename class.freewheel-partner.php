<?php
class FreeWheel_Partner extends FreeWheel {

	protected $key_name = "partner";
	protected $keys = array();
	protected $root_path = "/services/partner";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name"						=>	"string:255",
			"partner_network_id"		=>	"integer",
			"address_1"					=>	"string:255",
			"address_2"					=>	"string:255",
			"address_3"					=>	"string:255",
			"city"						=>	"string:255",
			"state_region_name"			=>	"string",
			"postal_code"				=>	"string:255",
			"country_name"				=>	"string",
			"email"						=>	"string:255",
			"phone"						=>	"string:255",
			"fax"						=>	"string:255",
			"url"						=>	"string:255",
			"notes"						=>	"string:4096",
			"billing_term"				=>	"string:4096",
			"meta_data"					=>	"string:4096"
		);
	}
	
	public function create($data, $id=NULL)
	{
		if (!is_null($id)) {
			if (!is_numeric($id)) {
				$this->set_error("You must submit a numeric ID in order to update a partner.");
				return false;	
			}
			
			$this->method = "PUT";
			$path = $this->root . "/" . $id . ".xml";
		}
		else {
			$this->method = "POST";
			if ((!isset($data['name']) || empty($data['name'])) && (!isset($data['partner_network_id']) || empty($data['partner_network_id']))) {
				$this->error("You must set either a Network ID or a Name in order to add a partner.");
				return false;
			}
			
			//You can only set the name or id, not both
			if (isset($data['name'])) {
				unset($data['partner_network_id']);
			}
			else {
				unset($data['name']);
			}
			
			$path = $this->root_path . ".xml";
		}
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($path, 1);
	}
	
	public function find($v=NULL, $role=NULL)
	{
		if (empty($v) || is_null($v)) {
			$this->set_error("You must supply a numeric partner_network_id to search for a partner.");
			return false;
		}
		
		$query = array();
		
		if (!is_numeric($v)) {
			if (empty($v) && empty($role)) {
				$this->set_error("You must supply either one keyword or the role in order to search for partners.");
				return false;
			}
			
			if (!is_null($v)) {
				array_push($query, "keyword=" . urlencode($v));
			}
			
			if (!is_null($role)) {
				$role = strolower($rolw);
				if ($role == "content_owner" || $role == "distributor" || $role == "reseller") {
					array_push($query, "role=" . $role);
				}
			}
			$path = ".xml?" . implode("&", $query);
		}
		else {
			$path = $this->root_path . "/" . $v . ".xml";
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function find_by_cat($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("You must submit a numeric category ID in order to search by category.");
			return false;
		}
		$this->method = "GET";
		return $this->call($this->root_path . "_category/" . $id . "/partner.xml", "all");
		
	}
	
}
?>