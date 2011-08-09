<?php
//FILE SUGARCRM flav=pro ONLY
class HooversConnectorsMockClient
{
	function __construct($mock) {
		$this->_client = $mock;
	}

	function call($name, $args)
	{
		$result = call_user_func(array($this->_client, $name), $args);
		return $result;
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array($this->_client, $name), $args);
	}

	public function __get($name)
	{
		if($name == "response") {
			return $this->_client->__getLastResponse();
		}
	}
}