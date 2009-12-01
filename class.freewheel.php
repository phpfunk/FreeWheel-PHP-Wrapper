<?php
class FreeWheel extends HTTP {
	
	public $api_key = NULL;
	public $api_url = "https://api.freewheel.tv";
	public $total_records = 0;
	
	function __construct($api_key=NULL)
	{
		if (is_null($api_key)) {
			$this->set_error("API KEY was NULL. You must supply an API KEY to use this library.");
			exit;
		}
		$this->api_key = $api_key;	
		$this->method = "GET";
	}
	
	function __destruct() {}
	
	protected function call($path=NULL, $type="all")
	{
		$this->total_records = 0;
		$this->errors = array();
		if (is_null($path)) {
			$this->set_error("The API path is not set. You cannot make any API calls without this path being set.");
			return false;
		}
	
		$headers = array(
			"Accept: application/xml",
			"Content-Type: application/xml",
			"X-FreeWheelToken: " . $this->api_key
		);
		
		$this->options[CURLOPT_HTTPHEADER] = $headers;
		
		$this->url = $this->api_url . $path;
		$xml = $this->connect(TRUE);
		
		//If an error, address it
		//GET, PUT, DELETE should return 200 on thumbs up
		//POST should return 201 on thumbs up
		$status_code = (strtoupper($this->method) == "POST") ? "201" : "200";
		if (!stristr($this->get("status"), $status_code)) {
			$this->set_error("Returned a status error: " . $this->get("status"));
			try {
				$errors = (array)@new SimpleXMLElement($xml);
				if (is_array($errors)) {
					foreach ($errors as $k => $v) {
						$this->set_error($v);
					}
				}
			}
			catch (Exception $e) {}
			return false;
		}
		
		//If no errors and method is DELETE, return true
		if (strtoupper($this->method) == "DELETE") {
			return true;
		}
		
		//Any other method (POST, GET, PUT); try to return the XML
		try {
			return $this->format((array)@new SimpleXMLElement($xml), $type, $this->key_name);
			}
		catch (Exception $e) {
			$this->set_error("XML returned from FreeWheel is invalid.");
			return false;
		}

	}
	
	protected function cdata_add($str)
	{
		if (stristr($str, "<") || stristr($str, ">")) {
			return "<![CDATA[" . $str . "]]>";
		}
		return $str;
	}
	
	protected function cdata_remove($str)
	{
		$pattern = "/((<|&lt;)!\[CDATA\[)(.*?)(\]\](>|&gt;))/is";
		if (preg_match($pattern, $str)) {
			return preg_replace($pattern, "$3", $str);
		}
		return $str;
	}
	
	public function countries($filepath=NULL, $find=NULL)
	{	
		return $this->parse_files($filepath, $find, "countries");
	}
	
	protected function create_xml($arr, $keys)
	{
		$tmp_keys = array();
		$required = array();
		$total_errors = 0;
		$xml = NULL;
		$arr_keys = array();
		
		foreach ($arr as $k => $v) {
			$arr_keys[$k] = strtolower($k);
		}
		
		foreach ($keys as $k => $v) {
			$tmp = explode(":", $k);
			$key = strtolower($tmp[0]);
			$tmp_keys[$key] = array();
			
			if (strtolower($tmp[1]) == "required") {
				if (!in_array($key, $arr_keys) && (strtoupper($this->method) == "POST" || $this->is_copy !== TRUE)) {
					$this->set_error("Missing Required Field: " . $key);
					$total_errors += 1;
				}
				$tmp_keys[$key]['required'] = TRUE;
			}
			else {
				$tmp_keys[$key]['required'] = FALSE;
			}
			
			$tmp = explode(":", $v);
			$type = $tmp[0];
			$tmp_keys[$key]['type'] = strtolower($type);
			
			$tmp = explode(",", $tmp[1]);
			
			if ($type == "enum") {
				$tmp_keys[$key]['enum'] = $tmp;
			}
			elseif ($type == "string") {
				$tmp_keys[$key]['max'] = (count($tmp) > 1) ? $tmp[1] : $tmp[0];
				$tmp_keys[$key]['min'] = (count($tmp) > 1) ? $tmp[0] : 0;
			}
			elseif ($type == "decimal") {
				$tmp_keys[$key]['max'] = $tmp[0];
				$tmp_keys[$key]['decimals'] = (isset($tmp[1])) ? $tmp[1] : NULL;
			}
		}
		
		foreach ($arr as $k => $v) {
			$ok = $k;
			$k = strtolower($k);
			$error_found = FALSE;
			if (!array_key_exists($k, $tmp_keys)) {
				$this->set_error("Invalid Key: " . $ok);
				$error_found = TRUE;
				$total_errors += 1;
			}
			else {
				if ($tmp_keys[$k]['required'] === TRUE && (empty($v) || is_null($v))) {
					$this->set_error("Field Cannot Be Blank: " . $ok);
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "string" && !is_string($v)) {
					$this->set_error("Type Mismatch (string): " . $ok);
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "boolean" && ($v !== FALSE && $v !== TRUE && strolower($v) != "true" && strtolower($v) != "false")) {
					$this->set_error("Type Mismatch (boolean): " . $ok);
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "datetime" && !preg_match("/(\d{4}-\d{2}-\d{2})(T|\s)(\d{2}:\d{2}:\d{2})((\+|-)(\d{2}:\d{2}))?/", $v)) {
					$this->set_error("Invalid format (datetime): Must be YYYY-MM-DDThh:mm:ssTZD or YYYY-MM-DD hh:mm:ss, your defined: " . $v);
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "string" && strlen($v) < $tmp_keys[$k]['min']) {
					$this->set_error("String too short: Length should be (" . $tmp_keys[$k]['min'] . ") was (" . strlen($v) . ")");
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "string" && strlen($v) > $tmp_keys[$k]['max']) {
					$this->set_error("String too long: Length should be (" . $tmp_keys[$k]['max'] . ") was (" . strlen($v) . ")");
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "enum" && !in_array($v, $tmp_keys[$k]['enum'])) {
					$this->set_error("Invalid Value: You sent - " . $ok . ", must be on of these - " . implode(",", $tmp_keys[$k]['enum']));
					$error_found = TRUE;
					$total_errors += 1;
				}
				if ($tmp_keys[$k]['type'] == "integer" || $tmp_keys[$k]['type'] == "decimal") {
					if (!is_numeric($v)) {
						$this->set_error("Type Mismatch (" . $tmp_keys[$k]['type'] . "): " . $ok);
						$error_found = TRUE;
						$total_errors += 1;
					}
					else {
						if ($tmp_keys[$k]['type'] == "decimal") {
							$tmp = explode(".", $v);
							if (!empty($tmp_keys[$k]['max']) && strlen($tmp[0]) > $tmp_keys[$k]['max']) {
								$this->set_error("Exceeding Numeric: " . $ok . " - Length should be (" . $tmp_keys[$k]['max'] . ") was (" . strlen($tmp[0]) . ")");
								$error_found = TRUE;
								$total_errors += 1;
							}
							if (!empty($tmp_keys[$k]['decimals']) && strlen($tmp[1]) > $tmp_keys[$k]['decimals']) {
								$this->set_error("Exceeding Decimal: " . $ok . " - Length should be (" . $tmp_keys[$k]['decimals'] . ") was (" . strlen($tmp[1]) . ")");
								$error_found = TRUE;
								$total_errors += 1;
							}
						}
					}
				}
				if (strlen($v) > $tmp_keys[$k]['max'] && !empty($tmp_keys[$k]['max'])) {
					$this->set_error("Max Character Mismatch: " . $ok . " - Looking For (" . $tmp_keys[$k]['max'] . ") was (" . strlen($v) . ")");
					$error_found = TRUE;
					$total_errors += 1;
				}
			}

			$xml .= ($error_found === FALSE) ? "<$k>" . $this->cdata_add($v) . "</$k>" : "";
		}
		
		return ($total_errors == 0) ? $xml : FALSE;
	}
	
	protected function format($xml, $var, $type)
	{
		$tmp = array();
		$x = 0;
		$xml = (is_numeric($var)) ? $xml : (array)$xml[$type];
		$this->total_records = 0;

		foreach ($xml as $k => $v) {
			if (!is_numeric($k)) {
				if ($k == "@attributes") {
					$k = "uri";
					$v = $xml['@attributes']['uri'];
				}
				$v = (is_object($v) && empty($v)) ? "" : $v;
				$tmp[$k] = $v;
				$this->total_records = 1;
			}
			else {
				$data = (array)$xml[$k];
				if (!is_array($tmp[$x])) { $tmp[$x] = array(); }
				foreach ($data as $kk => $vv) {
					if ($kk == "@attributes") {
						$kk = "uri";
						$vv = $data['@attributes']['uri'];
					}
					$vv = (is_object($vv) && empty($vv)) ? "" : $vv;
					$tmp[$x][$kk] = $this->cdata_remove($vv);
				}
				$x += 1;
			}
		}
		$this->total_records = ($this->total_records < 1) ? @count($tmp) : $this->total_records;
		return $tmp;
	}
	
	protected function is_serialized($data)
	{
    	return (@unserialize($data) !== false);
	}
	
	protected parse_files($filepath=NULL, $find=NULL, $sc="countries")
	{
		$filepath = (is_null($filepath)) ? "freewheel-api-$sc.txt" : $filepath;
		if (file_exists($filepath)) {
			$arr = array();
			$str = file_get_contents($filepath);
			if ($this->is_serialized($str)) {
				$arr = unserialize($str);
			}
			else {
				$str = explode("\n", $str);
				foreach (explode("\n", $str) as $line) {
					$tmp = explode("\t", $line);
					$arr[$tmp[0]] = $tmp[1];
				}
			}
			if (is_null($find)) {
				return $arr;
			}
			else {
				if (strlen($find) > 2) {
					foreach ($arr as $k => $v) {
						if (strtolower($find) == strtolower($v)) {
							return $k;
						}
					}
				}
				else {
					return $arr[strtoupper($find)];
				}
			}
		}
		else {
			$this->set_error("Could not open your $sc file under $filepath");
			return false;
		}
	}
	
	public function states($filepath=NULL, $find=NULL)
	{	
		return $this->parse_files($filepath, $find, "states");
	}

}
?>