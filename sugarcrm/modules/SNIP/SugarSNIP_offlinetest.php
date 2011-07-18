<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('SugarSNIP.php');

class SugarSNIP_offlinetest extends SugarSNIP{
	private $ot_status='notpurchased';

	public static function getInstance()
    {
        if(!parent::$instance) {
            parent::$instance = new self;
        }
        return parent::$instance;
    }

    public function unregister(){
    	$this->ot_status='purchased_disabled';
    	return true;
    }

    public function register(){
    	$this->ot_status='purchased_enabled';
    	return true;
    }

    /**
     * Get status of the SNIP installation
     * Expects to receive one of the following:
     * - purchased_enabled  (instance has snip license, and snip is enabled)
     * - purchased_down     (instance has snip license, but snip server is down)
     * - purchased_disabled (instance has snip license, but snip has been disabled by instance admin)
     * - notpurchased       (instance has no active snip license)
     */
	public function getStatus(){
		return $this->ot_status;
	}
}
?>