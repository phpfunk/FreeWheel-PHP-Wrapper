<?php
class FreeWheel_Campaign extends FreeWheel {

	protected $is_copy = FALSE;
	protected $key_name = "campaign";
	protected $keys = array();
	protected $root_path = "/services/campaign";

	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name:required"				=>	"string:3,255",
			"advertiser_id"				=>	"integer",
			"description"				=>	"string:4096",
			"campaign_type"				=>	"enum:TEMPLATE,PROPOSAL,CAMPAIGN",
			"internal_id"				=>	"string:255"
		);
	}
	
	protected function check_campaign($data)
	{
		if ($data['campaign_type'] == "CAMPAIGN" && !is_numeric($data['advertiser_id'])) {
			$this->set_error("You cannot set the campaign type to CAMPAIGN w/o an advertiser ID.");
			return false;
		}
		
		if ($data['campaign_type'] != "CAMPAIGN" && isset($data['advertiser_id'])) {
			unset($data['advertiser_id']);
		}
		
		return $data;
	}
	
	public function copy_campaign($data)
	{
		$this->method = "POST";
		$this->is_copy = TRUE;
		$id = $data['campaign_id'];
		
		if (empty($id) || !is_numeric($id)) {
			$this->set_error("The Campaign ID must be numeric in order to copy the campaign.");
			return false;
		}
		
		$data = $this->check_campaign($data);
		if ($data === FALSE) { return false; }

		unset($data['campaign_id']);
		
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		$this->is_copy = FALSE;
		
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($this->root_path . "/" . $id . "/save_as_new.xml", 1);
	}
	
	public function create($data, $id=NULL)
	{
		$data = $this->check_campaign($data);
		if ($data === FALSE) { return false; }
		
		if (!is_null($id)) {
			if (!is_numeric($id)) {
				$this->set_error("The ID must be numeric in order to update a campaign."); 
				return false;
			}

			$this->method = "PUT";
			$path = $this->root_path . "/" . $id . ".xml";
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
	
	/**
	Only IN_ACTIVE campaigns with no delivered impressions can be deleted
	**/
	public function delete($id=NULL)
	{
		$this->method = "DELETE";
		if (!is_numeric($id)) {
			$this->set_error("The ID must be numeric in order to delete a campaign.");
			return false;
		}
		
		return $this->call($this->root_path . "/" . $id . ".xml");
	}
	
	public function find($v=NULL)
	{
		if (is_null($v)) {
			$path = $this->root_path . ".xml";
			$v = "all";
		}
		else {
			if (is_numeric($v)) {
				$path = $this->root_path . "/" . $v . ".xml";
			}
			else {
				$this->set_error("The ID must be numeric in order to find a specific campaign.");
				return false;
			}
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function find_by_advertiser($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The ID must be numeric in order to find campaigns by a specific advertiser.");
			return false;
		}
		$this->method = "GET";
		return $this->call("/services/advertiser/" . $id . "/campaign.xml", 1);
	}
	
	public function update($data, $id)
	{
		return $this->create($data, $id);
	}

}
?>