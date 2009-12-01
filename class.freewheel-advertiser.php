<?php
class FreeWheel_Advertiser extends FreeWheel {

	protected $key_name = "advertiser";
	protected $keys = array();
	protected $root_path = "/services/advertiser";

	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name:required"				=>	"string:3,255",
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
			"meta_data"					=>	"string:4096",
			"parent_advertiser_id"		=>	"integer",
			"internal_id"				=>	"string:255"
		);
		
		if (!class_exists("FreeWheel_Agency", FALSE)) {
			$this->keys['agency_id'] = "integer";
		}
	}
	
	public function create($data, $id=NULL)
	{
		
		if (!is_null($id)) {
			if (!is_numeric($id)) {
				$find = $this->find($id);
				if ($this->total_records > 0) {
					$id = ($this->total_records == 1) ? $find['id'] : $find[0]['id'];
				}
				else {
					$this->set_error("The ID must be numeric or a valid name in order to update an advertiser."); 
					return false;
				}
			}

			$this->method = "PUT";
			$path = $this->root_path . "/" . $id . ".xml";
			
			//Can't update the agency id
			if (isset($data['agency_id'])) { unset($data['agency_id']); }
		}
		else {
			$this->method = "POST";
			$path = $this->root_path . ".xml";
		}

		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($path, 1);
	}
	
	public function find($v=NULL, $offset=NULL)
	{
		if (is_null($v) || empty($v)) {
			$offset = (!is_null($offset)) ? "?offset=" . $offset : $offset;
			$path = $this->root_path . ".xml" .  $offset;
			$v = "all";
		}
		else {
			$query = (is_numeric($v)) ? "/" . $v . ".xml" : ".xml?name=" . urlencode($v);
			$path = $this->root_path . $query;
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function find_by_agency($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("You must submit an numeric agency ID in order to see their children.");
			return false;
		}
		$this->method = "GET";
		return $this->call("/services/agency/" .  $id . "/advertiser.xml");
	}
	
	public function children($id=NULL)
	{
		if (is_null($id)) {
			$this->set_error("You must submit an numeric advertiser ID in order to see their children.");
			return false;
		}
		else {
			if (!is_numeric($id)) {
				$tmp = $this->find($id);
				if (@count($tmp) > 0) {
					$id = $tmp['id'];
				}
				else {
					$this->set_error("Parent does not exist.");
					return false;
				}
			}
			$this->method = "GET";
			return $this->call($this->root_path . "/" .  $id . "/child_advertiser.xml");
		}
	}
	
	public function update($data, $id)
	{
		return $this->create($data, $id);
	}
}
?>