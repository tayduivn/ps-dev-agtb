<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/Dashlets/DashletGenericChart.php');

class SalesActivityTrackerDashlet extends DashletGenericChart {

	// filter settings
	var $filter_date_start;
	var $filter_date_end;
	
	// setup dashlet
	function __construct($id, $options = null) {

		$this->loadLanguage('SalesActivityTrackerDashlet','custom/modules/Home/Dashlets/');
        $this->isConfigurable = true; // dashlet is configurable
		
        // search fields
        require('custom/modules/Home/Dashlets/SalesActivityTrackerDashlet/SalesActivityTrackerDashlet.data.php');
        $this->_searchFields = $dashletData['SalesActivityTrackerDashlet']['searchFields'];
        
        // setup bogus bean as seed for searchfields to work
        $this->_seedName = 'User';
        $this->_seedBean = new User();
		
        // if no custom title, use default
        if(empty($def['title'])) {
        	$this->title = $this->dashletStrings['LBL_TITLE'];
        } else {
        	$this->title = $def['title'];        
        }

        // setup filters
        global $timedate;
        // default to today minus one week
        if(empty($options['filter_date_start'])) 
            $options['filter_date_start'] = date($timedate->get_db_date_format(), strtotime("-1 week", time()));
        // default end date always to today
        //if(empty($options['filter_date_end']))
            $options['filter_date_end'] = date($timedate->get_db_date_format(), time());
        
        return parent::__construct($id, $options);
	}
	
	// output
    function display() {

    	// custom chart
    	require_once('custom/modules/Home/Dashlets/SalesActivityTrackerDashlet/SalesActivityTrackerChart.php');
       	$chart = new SalesActivityTrackerChart($this->id, $this->dashletStrings);
  		return '<div align="center">'.$chart->get_chart($this->filter_date_start, $this->filter_date_end).'</div><br />';
    }  
    
    // filters (override full class from DashletGenericChart because we dont work with a seed bean
    public function displayOptions() {
        $currentSearchFields = array();
        if ( is_array($this->_searchFields) ) {
            foreach($this->_searchFields as $name=>$params) {
                if(!empty($name)) {
                    $name = strtolower($name);
                    if($name <> 'filter_date_end') {
                    	if(empty($params['input_name0'])) {
                        	$params['input_name0'] = empty($this->$name) ? '' : $this->$name;
                    	}
                    	$currentSearchFields[$name] = array();
                    	$currentSearchFields[$name]['label'] = $this->dashletStrings[$params['vname']];
                    	$currentSearchFields[$name]['input'] = $this->layoutManager->widgetDisplayInput($params, true, (empty($this->$name) ? '' : $this->$name));
                    }
                }
            }
        }
        $this->currentSearchFields = $currentSearchFields;
        $this->getConfigureSmartyInstance()->assign('title',$this->dashletStrings['LBL_TITLE']);
        $this->getConfigureSmartyInstance()->assign('save',$GLOBALS['app_strings']['LBL_SAVE_BUTTON_LABEL']);
        $this->getConfigureSmartyInstance()->assign('id', $this->id);
        $this->getConfigureSmartyInstance()->assign('searchFields', $this->currentSearchFields);
        $this->getConfigureSmartyInstance()->assign('dashletTitle', $this->title);
        $this->getConfigureSmartyInstance()->assign('dashletType', 'predefined_chart');
        $this->getConfigureSmartyInstance()->assign('module', $_REQUEST['module']);
              
        return $this->getConfigureSmartyInstance()->fetch($this->_configureTpl);
    }
        
    
}

?>
