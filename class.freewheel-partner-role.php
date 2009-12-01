<?php
class FreeWheel_Partner_Role extends FreeWheel {

	protected $key_name = "partner_role";
	protected $keys = array();
	protected $root_path = "/services/partner";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"partner_network_id:required"	=>	"integer"
		);
	}
	
	public function create($data)
	{
		$this->method = "POST";
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($this->root_path . "/" . $data['partner_network_id'] . "/partner_role.xml", 1);
	}
	
	public function find($v=NULL)
	{
		if (!is_numeric($v)) {
			$this->set_error("The partner network ID must be numeric in order to extract the partner's role.");
			return false;
		}
		$this->method = "GET";
		return $this->call($this->root_path . "/" . $v . "/partner_role.xml", "all");
	}

}
?>