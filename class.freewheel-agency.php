<?php
class FreeWheel_Agency extends FreeWheel_Advertiser {

	protected $key_name = "agency";
	protected $root_path = "/services/agency";
	
	function __construct($api_key=NULL)
	{
		parent::__construct($api_key);
	}
}