<?php
class FreeWheel_Placement extends FreeWheel {

	protected $key_name = "placement";
	protected $keys = array();
	protected $root_path = "/services/placement";

	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
		$this->keys = array(
			"name:required"					=>	"string:3,255",
			"description"					=>	"string:4096",
			"placement_type"				=>	"enum:NORMAL,MAKE_GOOD,PROMO",
			"instruction"					=>	"string:4096",
			"start"							=>	"datetime",
			"end"							=>	"datetime",
			"timezone"						=>	"string:255",
			"priority"						=>	"enum:PREEMPTIBLE,GUARANTEED",
			"pace"							=>	"enum:EVEN,AS_FAST_AS_POSSIBLE",
			"cutoff_type"					=>	"enum:HARD,SOFT",
			"frequency_cap"					=>	"integer",
			"frequency_period"				=>	"enum:TEN_MIN,HOUR,DAY,WEEK,CAMPAIGN",
			"budget_model"					=>	"enum:ACTUAL_ECPM,PERCENTILE_ECPM,EXEMPT,FLAT_FEE_SPONSORSHIP,STANDARD_SPONSORSHIP",
			"currency"						=>	"decimal:16,2",
			"impression"					=>	"integer",
			"relative_priority"				=>	"enum:0,10,20,30,40,50,60,70,80,90,100",
			"wholesale_ecpm"				=>	"decimal:16,2",
			"internal_id"					=>	"string:255"
		);
	}
	
	public function activate($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to activate it.");
			return false;
		}
		$this->method = "PUT";
		return $this->call($this->root_path . "/" . $id . "/activate.xml", 1);
	}
	
	protected function call($xml, $type, $summary=FALSE)
	{
		$xml = parent::call($xml, $type);
		
		if ($this->method == "DELETE") { return $xml; }
		
		if ($xml !== FALSE) {
			foreach ($xml as $num => $arr) {
				if ($summary === FALSE) {
					//Multiples
					$subs = array("schedule","delivery","budget");
					if (is_array($xml[$num])) {
						foreach ($xml[$num] as $k => $v) {
							if (in_array($k, $subs)) {
								foreach ($xml[$num][$k] as $kk => $vv) {
									$xml[$num][$kk] = $this->cdata_remove((string)$vv);
								}
								unset($xml[$num][$k]);
							}
						}
					}
				}
				else {
					//Summary
					$subs = array("forecast","schedule","delivery");
					if (in_array($num, $subs)) {
						if (is_array($xml[$num])) {
							foreach ($xml[$num] as $k => $v) {
								$xml[$k] = $this->cdata_remove((string)$v);
							}
							unset($xml[$num]);
						}
					}
					$xml['ad_units'] = (array)$xml['ad_units'];
				}
			}
			return $xml;
		}
		return false;
	}
	
	public function cancel($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to cancel it.");
			return false;
		}
		$this->method = "PUT";
		return $this->call($this->root_path . "/" . $id . "/cancel.xml", 1);
	}
	
	public function create($data, $id=NULL)
	{
		
		if (is_numeric($id)) {
			$this->method = "PUT";
			$path = $this->root_path . "/" . $id . ".xml";
		}
		else {
			$this->method = "POST";
			$path = "/services/insertion_order/" . $data['insertion_order_id'] . "/placement.xml";
		}
		
		if (isset($data['insertion_order_id'])) { unset($data['insertion_order_id']); }
		
		$xml = $this->create_xml($data, $this->keys);
		if ($xml === FALSE) { return false; }
		
		$this->options[CURLOPT_POSTFIELDS] = "<" . $this->key_name . ">" . $xml . "</" . $this->key_name . ">";
		return $this->call($path, 1);
	}
	
	public function deactivate($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to deactivate it.");
			return false;
		}
		$this->method = "PUT";
		return $this->call($this->root_path . "/" . $id . "/deactivate.xml", 1);
	}
	
	public function delete($id)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to delete it.");
			return false;
		}
		$this->method = "DELETE";
		return $this->call($this->path . "/" . $id . ".xml");
	}
	
	public function find($v=NULL)
	{
		if (is_numeric($v)) {
			$path = $this->root_path . "/" . $v . ".xml";
		}
		else {
			$path = $this->root_path . ".xml";
			$v = "all";
		}
		$this->method = "GET";
		return $this->call($path, $v);
	}
	
	public function find_by_io($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The IO ID must be numeric in order to find its placement.");
			return false;
		}
		$this->method = "GET";
		return $this->call("/services/insertion_order/" . $id . "/placement.xml", "all");
	}
	
	public function find_summary($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to extract its summary.");
			return false;
		}
		$this->method = "GET";
		return $this->call($this->root_path . "/" . $id . "/summary.xml", 1, TRUE);
	}
	
	public function soft_release($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to do a soft release.");
			return false;
		}
		$this->method = "PUT";
		return $this->call($this->root_path . "/" . $id . "/soft_release.xml", 1);
	}
	
	public function soft_reserve($id=NULL)
	{
		if (!is_numeric($id)) {
			$this->set_error("The placement ID must be numeric in order to do a soft reserve.");
			return false;
		}
		$this->method = "PUT";
		return $this->call($this->root_path . "/" . $id . "/soft_reserve.xml", 1);
	}
	
	public function update($data, $id)
	{
		return $this->create($data, $id);
	}
}
?>