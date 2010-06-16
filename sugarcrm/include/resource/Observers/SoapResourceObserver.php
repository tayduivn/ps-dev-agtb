<?php
/*********************************************************************************
 * The contents of this file are subject to
 ********************************************************************************/

require_once('include/resource/Observers/ResourceObserver.php');

class SoapResourceObserver extends ResourceObserver {

private $soapServer;

function SoapResourceObserver($module) {
   parent::ResourceObserver($module);
}


/**
 * set_soap_server
 * This method accepts an instance of the nusoap soap server so that a proper
 * response can be returned when the notify method is triggered.
 * @param $server The instance of the nusoap soap server
 */
function set_soap_server(& $server) {
   $this->soapServer = $server;
}


/**
 * notify
 * Soap implementation to notify the soap clients of a resource management error
 * @param msg String message to possibly display
 */
public function notify($msg = '') {

header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
header('Content-Type: text/xml; charset="ISO-8859-1"');
$error = new SoapError();
$error->set_error('resource_management_error');
//Override the description
$error->description = $msg;
$this->soapServer->methodreturn = array('result'=>$msg, 'error'=>$error->get_soap_array());
$this->soapServer->serialize_return();	
$this->soapServer->send_response();
sugar_cleanup(true);
//BEGIN SUGARCRM flav=int ONLY
/*
$url = $GLOBALS['sugar_config']['site_url'].'/soap.php';
header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
header('Content-Type: text/xml; charset="ISO-8859-1"');
$xml = <<<EOQ
<?xml version="1.0" encoding="ISO-8859-1"?>
<SOAP-ENV:Envelope
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:si="http://soapinterop.org/xsd">
 <SOAP-ENV:Body>
  <ns1:error xmlns:ns1="$url">
   <name xsi:type="xsd:string">$msg</name>
   <number xsi:type="xsd:string">-1</number>
   <description xsi:type="xsd:string">$msg</description>
  </ns1:error>
 </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOQ;
echo $xml;
*/
//END SUGARCRM flav=int ONLY

}	
	
}
?>