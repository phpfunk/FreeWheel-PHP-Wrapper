<?php
class FreeWheel_IO extends FreeWheel {

	protected $key_name = "insertion_order";
	protected $keys = array();
	protected $root_path = "/services/insertion_order";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name:required"			=>	"string:255",
			"description"			=>	"string:4096",
			"client_po"				=>	"string:255",
			"internal_id"			=>	"string:255",
			"campaign_id:required"	=>	"integer"
		);
	}
	
	public function book($id=NULL)
	{
		return $this->schedule($id);
	}
	
	public function create($data, $id=NULL)
	{
		if (is_numeric($id)) {
			$this->method = "PUT";
			$path = $this->root_path . "/" . $id . ".xml";
		}
		else {
			$this->method = "POST";
			$path = "/services/campaign/" . $data['campaign_id'] . "/insertion_order.xml";
		}
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($path, 1);
	}
	
	public function delete($id=NULL)
	{
		if (is_null($id)) {
			$this->set_error("The insertion order ID must be numeric in order to delete an IO.");
			return false;
		}
		
		$this->method = "DELETE";
		return $this->call($this->root_path . "/" . $id . ".xml");
	}
	
	public function find($v=NULL)
	{
		$query = (!is_null($v)) ? "?name=" . urlencode($v) : NULL;		
		$path = (is_numeric($v)) ? "/" . $v . ".xml" : ".xml" . $query;
		$this->method = "GET";
		return $this->call($this->root_path . $path, $v);
	}
	
	public function find_by_campaign($v=NULL)
	{
		if (!is_numeric($v)) {
			$this->set_error("The ID must be numeric to find all IO's by campaign.");
			return false;
		}
		$this->method = "GET";
		return $this->call("/services/campaign/" . $v . "/insertion_order.xml", "all");
	}
	
	protected function schedule($id=NULL, $book=TRUE)
	{
		if (is_null($id) ||!is_numeric($id)) {
			$this->set_error("The insertion order ID must be numeric in order to schedule an IO.");
			return false;
		}
		
		$this->method = "PUT";
		$page = ($book === TRUE) ? "book" : "unbook";
		return $this->call($this->root_path . "/" . $id . "/" . $page . ".xml");
	}
	
	public function unbook($id=NULL)
	{
		return $this->schedule($id, FALSE);
	}
	
	public function update($data, $id)
	{
		return $this->create($data, $id);
	}
	
}
?>