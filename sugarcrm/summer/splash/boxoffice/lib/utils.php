<?php
function generate_guid_section($characters)
{
	$return = "";
	for($i=0; $i<$characters; $i++)
	{
		$return .= dechex(mt_rand(0,15));
	}
	return $return;
}


function generate_guid()
{
	$microTime = microtime();
	list($a_dec, $a_sec) = explode(" ", $microTime);

	$dec_hex = dechex($a_dec* 1000000);
	$sec_hex = dechex($a_sec);

    ensure_guid_length($dec_hex, 5);
    ensure_guid_length($sec_hex, 6);

	$guid = "";
	$guid .= $dec_hex;
	$guid .= generate_guid_section(3);
	$guid .= '-';
	$guid .= generate_guid_section(4);
	$guid .= '-';
	$guid .= generate_guid_section(4);
	$guid .= '-';
	$guid .= generate_guid_section(4);
	$guid .= '-';
	$guid .= $sec_hex;
	$guid .= generate_guid_section(6);
	return $guid;

}




function ensure_guid_length(&$string, $length)
{
	$strlen = strlen($string);
	if($strlen < $length)
	{
		$string = str_pad($string,$length,"0");
	}
	else if($strlen > $length)
	{
		$string = substr($string, 0, $length);
	}
}


/**
  * Helper for debugging PDO statements
  * @param $sth
  * @param bool $die
  */
 function debugStatement($sth, $die=True){
     print_r($sth->debugDumpParams());
     print_r(var_export($sth->errorInfo()));
     if($die)die();
 }