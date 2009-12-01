<?php
class FreeWheel_Partner_Category_Assignment extends FreeWheel {

	protected $key_name = "partner_category_assignment";
	protected $keys = array();
	protected $root_path = "/services/partner_category";
	
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
		return $this->call($this->root_path . $data['partner_network_id'] . "partner_category_assignment.xml", 1);
	}

}
?>