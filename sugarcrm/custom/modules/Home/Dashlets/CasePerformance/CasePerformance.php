<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/Cases/Case.php');
require_once('CasePerformance.data.php');

class CasePerformance extends DashletGeneric {

	var $configureTpl = 'custom/modules/Home/Dashlets/CasePerformance/CasePerformanceConfigure.tpl';
	var $displayTpl = 'custom/modules/Home/Dashlets/CasePerformance/CasePerformanceDisplay.tpl';
	var $holidays;
	var $closedValues;
	var $businessHours;
	var $opentime= array(
	     "Mon" => array(
	        "open" => "11:00:00",
	        "close" => "23:00:00",
	        ),
	    "Tue" => array(
	        "open" => "11:00:00",
	        "close" => "23:00:00",
	        ),
	    "Wed" => array(
	        "open" => "11:00:00",
	        "close" => "23:00:00",
	        ),
	    "Thu" => array(
	        "open" => "11:00:00",
	        "close" => "23:00:00",
	        ),
	    "Fri" => array(
	        "open" => "11:00:00",
	        "close" => "23:00:00",
	        ),
	    );

    function CasePerformance($id, $def = null) {
        global $current_user, $app_strings, $dashletData, $timedate;
        parent::DashletGeneric($id, $def);

        $this->hasScript = true;  // dashlet has javascript attached to it
        $this->loadLanguage('CasePerformance', 'custom/modules/Home/Dashlets/');

		if(!empty($def['businessHours'])) $this->businessHours = $def['businessHours'];
		if(!empty($def['opentime'])) $this->opentime = $def['opentime'];
		if(!empty($def['closedValues'])) $this->closedValues = $def['closedValues'];
        if(empty($def['title'])) $this->title = $this->dashletStrings['LBL_TITLE'];

        $this->searchFields = $dashletData['CasePerformance']['searchFields'];
        $this->columns = $dashletData['CasePerformance']['columns'];
        $this->holidays = $dashletData['CasePerformance']['holidays'];
        $this->seedBean = new aCase();
    }

    /**
     * Saves the display options template
     *
     * @return $options array
     */
    function saveOptions($req) {
    	global $current_user, $timedate;
        $options = array();

        foreach($req as $name => $value) {
            if(!is_array($value)) $req[$name] = trim($value);
        }
        $options['filters'] = array();
        foreach($this->searchFields as $name=>$params) {
            $widgetDef = $this->seedBean->field_defs[$name];
            if($widgetDef['type'] == 'datetime' || $widgetDef['type'] == 'date') { // special case datetime types
                $options['filters'][$widgetDef['name']] = array();
                if(!empty($req['type_' . $widgetDef['name']])) { // save the type of date filter
                    $options['filters'][$widgetDef['name']]['type'] = $req['type_' . $widgetDef['name']];
                }
                if(!empty($req['date_' . $widgetDef['name']])) { // save the date
                    $options['filters'][$widgetDef['name']]['date'] = $req['date_' . $widgetDef['name']];
                }
            }
            elseif(!empty($req[$widgetDef['name']])) {
                $options['filters'][$widgetDef['name']] = $req[$widgetDef['name']];
            }
        }

        if(!empty($req['dashletTitle'])) {
            $options['title'] = $req['dashletTitle'];
        }

        if(!empty($req['myItemsOnly'])) {
            $options['myItemsOnly'] = $req['myItemsOnly'];
        }
        else {
           $options['myItemsOnly'] = false;
        }
		if(!empty($req['monOpen'])){
			$options['opentime']['Mon']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['monOpen']);
		}
		if(!empty($req['monClose'])){
			$options['opentime']['Mon']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['monClose']);
		}
		if(!empty($req['tueOpen'])){
			$options['opentime']['Tue']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['tueOpen']);
		}
		if(!empty($req['tueClose'])){
			$options['opentime']['Tue']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['tueClose']);
		}
		if(!empty($req['wedOpen'])){
			$options['opentime']['Wed']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['wedOpen']);
		}
		if(!empty($req['wedClose'])){
			$options['opentime']['Wed']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['wedClose']);
		}
		if(!empty($req['thuOpen'])){
			$options['opentime']['Thu']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['thuOpen']);
		}
		if(!empty($req['thuClose'])){
			$options['opentime']['Thu']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['thuClose']);
		}
		if(!empty($req['friOpen'])){
			$options['opentime']['Fri']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['friOpen']);
		}
		if(!empty($req['friClose'])){
			$options['opentime']['Fri']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['friClose']);
		}
		if(!empty($req['satOpen'])){
			$options['opentime']['Sat']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['satOpen']);
		}
		if(!empty($req['satClose'])){
			$options['opentime']['Sat']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['satClose']);
		}
		if(!empty($req['sunOpen'])){
			$options['opentime']['Sun']['open'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['sunOpen']);
		}
		if(!empty($req['sunClose'])){
			$options['opentime']['Sun']['close'] = $timedate->to_db_time(date($timedate->get_date_format(). ' ', time()) . $req['sunClose']);
		}

        $options['businessHours'] = $req['businessHours'];

		foreach($req['closedValues'] as $key => $val){
			$closedValues[]=$val;
		}
        $options['closedValues'] =  $closedValues;

        $options['displayRows'] = empty($req['displayRows']) ? '5' : $req['displayRows'];
        // displayColumns
        if(!empty($req['displayColumnsDef'])) {
            $options['displayColumns'] = explode('|', $req['displayColumnsDef']);
        }
        return $options;
    }

        /**
     * Displays the javascript for the dashlet
     *
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();
        $ss->assign('id', $this->id);

        // quicksearch
        require_once('include/QuickSearchDefaults.php');
        $qsd = new QuickSearchDefaults();
        $json = getJSONobj();
        $sqs_objects = array('account_name' . $this->id => $qsd->getQSParent());
        $sqs_objects['account_name' . $this->id]['field_list'] = array('name');
        $sqs_objects['account_name' . $this->id]['populate_list'] = array('account_name' . $this->id);

        $quicksearch_js = $qsd->getQSScripts();
        $quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
        $ss->assign('QS', $quicksearch_js);

        $str = $ss->fetch('custom/modules/Home/Dashlets/CasePerformance/CasePerformanceScript.tpl');
        return $str; // return parent::display for title and such
    }

    /**
     * Sets up the display options template
     *
     * @return string HTML that shows options
     */
    function processDisplayOptions() {
    	global $current_user, $timedate;
         require_once('include/templates/TemplateGroupChooser.php');

        $this->configureSS = new Sugar_Smarty();
        // column chooser
        $chooser = new TemplateGroupChooser();

        $chooser->args['id'] = 'edit_tabs';
        $chooser->args['left_size'] = 5;
        $chooser->args['right_size'] = 5;
        $chooser->args['values_array'][0] = array();
        $chooser->args['values_array'][1] = array();

        $this->addCustomFields();

        if($this->displayColumns) {
             // columns to display
             foreach($this->displayColumns as $num => $name) {
                    // defensive code for array being returned
                    $translated = translate($this->columns[$name]['label'], $this->seedBean->module_dir);
                    if(is_array($translated)) $translated = $this->columns[$name]['label'];
                    $chooser->args['values_array'][0][$name] = trim($translated, ':');
             }
             // columns not displayed
             foreach(array_diff(array_keys($this->columns), array_values($this->displayColumns)) as $num => $name) {
                    // defensive code for array being returned
                    $translated = translate($this->columns[$name]['label'], $this->seedBean->module_dir);
                    if(is_array($translated)) $translated = $this->columns[$name]['label'];
                    $chooser->args['values_array'][1][$name] = trim($translated, ':');
             }
        }
        else {
             foreach($this->columns as $name => $val) {
                // defensive code for array being returned
                $translated = translate($this->columns[$name]['label'], $this->seedBean->module_dir);
                if(is_array($translated)) $translated = $this->columns[$name]['label'];
                if(!empty($val['default']) && $val['default'])
                    $chooser->args['values_array'][0][$name] = trim($translated, ':');
                else
                    $chooser->args['values_array'][1][$name] = trim($translated, ':');
            }
        }

        $chooser->args['left_name'] = 'display_tabs';
        $chooser->args['right_name'] = 'hide_tabs';
        $chooser->args['max_left'] = '8';

        $chooser->args['left_label'] =  $GLOBALS['app_strings']['LBL_DISPLAY_COLUMNS'];
        $chooser->args['right_label'] =  $GLOBALS['app_strings']['LBL_HIDE_COLUMNS'];
        $chooser->args['title'] =  '';
        $this->configureSS->assign('columnChooser', $chooser->display());

        $query = false;
        $count = 0;

        if(!is_array($this->filters)) {
            // use default search params
            $this->filters = array();
            foreach($this->searchFields as $name => $params) {
                if(!empty($params['default']))
                    $this->filters[$name] = $params['default'];
            }
        }
        foreach($this->searchFields as $name=>$params) {
            if(!empty($name)) {
                $name = strtolower($name);
                $currentSearchFields[$name] = array();

                $widgetDef = $this->seedBean->field_defs[$name];

                $widgetDef['input_name0'] = empty($this->filters[$name]) ? '' : $this->filters[$name];
                $currentSearchFields[$name]['label'] = translate($widgetDef['vname'], $this->seedBean->module_dir);
                $currentSearchFields[$name]['input'] = $this->layoutManager->widgetDisplayInput($widgetDef, true, (empty($this->filters[$name]) ? '' : $this->filters[$name]));
            }
            else { // ability to create spacers in input fields
                $currentSearchFields['blank' + $count]['label'] = '';
                $currentSearchFields['blank' + $count]['input'] = '';
                $count++;
            }
        }
        $this->currentSearchFields = $currentSearchFields;

        $this->configureSS->assign('strings', array('general' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_GENERAL'],
                                     'filters' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_FILTERS'],
                                     'myItems' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY'],
                                     'displayRows' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_DISPLAY_ROWS'],
                                     'title' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_TITLE'],
                                     'save' => $GLOBALS['app_strings']['LBL_SAVE_BUTTON_LABEL']));
        $this->configureSS->assign('id', $this->id);
        $this->configureSS->assign('myItemsOnly', $this->myItemsOnly);
        $this->configureSS->assign('searchFields', $this->currentSearchFields);
        // title
        $this->configureSS->assign('dashletTitle', $this->title);

        // display rows
        $displayRowOptions = $GLOBALS['sugar_config']['dashlet_display_row_options'];
        $this->configureSS->assign('displayRowOptions', $displayRowOptions);
        $this->configureSS->assign('displayRowSelect', $this->displayRows);

        $closedValues['status'] = array();

        $closedDef = $this->seedBean->field_defs['status'];

        $closedDef['input_name0'] =  $this->closedValues/*empty($this->closedValues) ? '' : $this->closedValues*/;
        $closedValues['status']['label'] = $this->dashletStrings['LBL_CLOSED_STATUS'];
        $closedValues['status']['input'] = $this->layoutManager->widgetDisplayInput($closedDef, true, (empty($this->closedValues) ? '' : $this->closedValues));
        $closedValues['status']['input'] = preg_replace('/status\[\]/', 'closedValues[]', $closedValues['status']['input']);

        $this->closedVal = $closedValues;

        $this->configureSS->assign('strings', array('general' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_GENERAL'],
                                     'filters' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_FILTERS'],
                                     'myItems' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY'],
                                     'businessHours' => $this->dashletStrings['LBL_BUSINESS_HOURS'],
                                     'hoursStart' => $this->dashletStrings['LBL_HOURS_START'],
                                     'hoursEnd' => $this->dashletStrings['LBL_HOURS_END'],
                                     'mon' => $this->dashletStrings['LBL_MON'],
                                     'tue' => $this->dashletStrings['LBL_TUE'],
                                     'wed' => $this->dashletStrings['LBL_WED'],
                                     'thu' => $this->dashletStrings['LBL_THU'],
                                     'fri' => $this->dashletStrings['LBL_FRI'],
                                     'sat' => $this->dashletStrings['LBL_SAT'],
                                     'sun' => $this->dashletStrings['LBL_SUN'],
                                     'displayRows' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_DISPLAY_ROWS'],
                                     'title' => $GLOBALS['mod_strings']['LBL_DASHLET_CONFIGURE_TITLE'],
                                     'save' => $GLOBALS['app_strings']['LBL_SAVE_BUTTON_LABEL']));

		$this->configureSS->assign('closedValues', $this->closedVal);
        $this->configureSS->assign('businessHours', $this->businessHours);

		if(isset($this->opentime['Mon']['open'])) $this->configureSS->assign('monOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Mon']['open']));
		if(isset($this->opentime['Mon']['close'])) $this->configureSS->assign('monClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Mon']['close']));
		if(isset($this->opentime['Tue']['open'])) $this->configureSS->assign('tueOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Tue']['open']));
		if(isset($this->opentime['Tue']['close'])) $this->configureSS->assign('tueClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Tue']['close']));
		if(isset($this->opentime['Wed']['open'])) $this->configureSS->assign('wedOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Wed']['open']));
		if(isset($this->opentime['Wed']['close'])) $this->configureSS->assign('wedClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Wed']['close']));
		if(isset($this->opentime['Thu']['open'])) $this->configureSS->assign('thuOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Thu']['open']));
		if(isset($this->opentime['Thu']['close'])) $this->configureSS->assign('thuClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Thu']['close']));
		if(isset($this->opentime['Fri']['open'])) $this->configureSS->assign('friOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Fri']['open']));
		if(isset($this->opentime['Fri']['close'])) $this->configureSS->assign('friClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Fri']['close']));
		if(isset($this->opentime['Sat']['open'])) $this->configureSS->assign('satOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Sat']['open']));
		if(isset($this->opentime['Sat']['close'])) $this->configureSS->assign('satClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Sat']['close']));
		if(isset($this->opentime['Sun']['open'])) $this->configureSS->assign('sunOpen', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Sun']['open']));
		if(isset($this->opentime['Sun']['close'])) $this->configureSS->assign('sunClose', $timedate->to_display_time(gmdate('Y-m-d ') .$this->opentime['Sun']['close']));


    }

    /**
     * Calculate the time  from date_created to date_modified
     * @param string $date_entered The start time (Y-m-d H:i:s) of the given case
     * @param string $date_modified The end time (Y-m-d H:i:s) of the given case
     *
     * @return string Time returned in days hours and seconds
     */
    function format($start, $end, $use_secs=false){

		$start = strtotime($start);
		$end = strtotime($end);

    	if ($this->businessHours == true){
    		require_once('BusinessTime.php');
    		$bt = new BusinessTimeForCP();
    		$bt->start = $start;
    		$bt->end = $end;
    		$bt->holiday = $this->holidays;
    		$bt->opentime = $this->opentime;
			$time = $bt->calculate();
			$elapsedSeconds = $time['ontime'];

    	} else {
    		$elapsedSeconds = $end - $start;
    	}

    	$elapsedDays = $elapsedSeconds/86400;
    	$elapsedDays = floor($elapsedDays);

    	$temp_remainder = $elapsedSeconds - ($elapsedDays * 86400);
    	$remainderHours = floor($temp_remainder/3600);

    	$temp_remainder = $temp_remainder - ($remainderHours * 3600);
		$remainderMinutes = floor($temp_remainder/60);

    	$temp_remainder = $temp_remainder - ($remainderMinutes/60);
    	$remainderSeconds= floor($temp_remainder/60);

    	if ($elapsedDays == 0 && $remainderHours == 0 && $remainderMinutes == 0 && $remainderSeconds==0){
			$noresults=true;
    		$result = $this->dashletStrings['LBL_NO_TIME_ELAPSE'];
		} elseif ($elapsedDays == 0 && $remainderHours == 0 && $remainderMinutes==0  && $use_secs==true) {
    		$result = $remainderSeconds .' ' . $this->dashletStrings['LBL_SECS'];
    	} elseif ($elapsedDays == 0 && $remainderHours == 0) {
    		$result = $remainderMinutes .' ' . $this->dashletStrings['LBL_MINS'];
    	} elseif ($elapsedDays == 0) {
    		$result = $remainderHours .' ' . $this->dashletStrings['LBL_HOURS'] .' '. $remainderMinutes .' ' . $this->dashletStrings['LBL_MINS'];
    	} else {
    		$result = $elapsedDays . $this->dashletStrings['LBL_DAYS'] .' '. $remainderHours . $this->dashletStrings['LBL_HOURS'] .' '. $remainderMinutes . $this->dashletStrings['LBL_MINS'];
    	}


    	if($use_secs && !isset($noresults))$result .= ' '. $remainderSeconds .' ' . $this->dashletStrings['LBL_SECS'];

    	if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){
			return $elapsedSeconds/60; // Export time in minutes to be manipulated by the user to build graphs in excel.
    	} else {
    		return $result;
    	}

    }


    function buildWhere() {
        global $current_user;

        $returnArray = array();

        if(!is_array($this->filters)) {
            // use defaults
            $this->filters = array();
            foreach($this->searchFields as $name => $params) {
                if(!empty($params['default']))
                    $this->filters[$name] = $params['default'];
            }
        }
        foreach($this->filters as $name=>$params) {
            if(!empty($params)) {
                if($name == 'assigned_user_id' && $this->myItemsOnly) continue; // don't handle assigned user filter if filtering my items only
                $widgetDef = $this->seedBean->field_defs[$name];

                $widgetClass = $this->layoutManager->getClassFromWidgetDef($widgetDef, true);
                $widgetDef['table'] = $this->seedBean->table_name;
                $widgetDef['table_alias'] = $this->seedBean->table_name;

                switch($widgetDef['type']) {// handle different types
                    case 'date':
                    case 'datetime':
                        if(!empty($params['date']))
                            $widgetDef['input_name0'] = $params['date'];
                        $filter = 'queryFilter' . $params['type'];
                        array_push($returnArray, $widgetClass->$filter($widgetDef, true));
                        break;
                    default:
                        $widgetDef['input_name0'] = $params;
                        if(is_array($params) && !empty($params)) { // handle array query
                            array_push($returnArray, $widgetClass->queryFilterone_of($widgetDef, false));
                        }
                        else {
                            array_push($returnArray, $widgetClass->queryFilterStarts_With($widgetDef, true));
                        }
                        $widgetDef['input_name0'] = $params;
                    break;
                }
            }
        }

        if($this->myItemsOnly) array_push($returnArray, $this->seedBean->table_name . '.' . "assigned_user_id = '" . $current_user->id . "'");
        if(isset($_REQUEST['caseNumber']) && ($_REQUEST['caseNumber']!='' || $_REQUEST['caseNumber']!=null)) array_push($returnArray, $this->seedBean->table_name . '.' . "case_number IN (" . $_REQUEST['caseNumber'] . ")");

        if(isset($_REQUEST['accountName']) && ($_REQUEST['accountName']!='' || $_REQUEST['accountName']!=null)){
        	$query = "SELECT id FROM accounts WHERE name LIKE '%" . $_REQUEST['accountName'] . "%'";
        	$result = $GLOBALS['db']->query($query);
        	while($row = $GLOBALS['db']->fetchByAssoc($result)) {
        		array_push($returnArray, $this->seedBean->table_name . '.' . "account_id = '" . $row['id'] . "'");
        	}
        }

        return $returnArray;
    }

    function process() {
    	global $current_language, $current_user, $locale, $app_strings, $mod_strings;

    	if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){
    			$this->displayTpl = 'custom/modules/Home/Dashlets/CasePerformance/CasePerformanceExport.tpl';
    			$this->displayRows = 1000000;//some ridicoulous amount to handle all rows of data

    	}

        parent::process();

		$keys = array();

        foreach($this->lvs->data['data'] as $num => $row) {
            $keys[] = $row['ID'];
        }


        $columnsDisplay = array();
        if(is_array($this->displayColumns) && count($this->displayColumns) > 0) $columnsDisplay = array_flip($this->displayColumns);

        // Check for Resolution Time on a Case
        if (isset($columnsDisplay['resolution_time'])){

	        // Staticly coded for now :( - will add more flexiblity in the future
	        $query = "SELECT cases_audit.parent_id, cases_audit.date_created, cases.id, cases.date_entered
	        		 FROM cases_audit
	        		 INNER JOIN cases
	        		 	ON cases_audit.parent_id=cases.id
	        		 WHERE field_name='status'
	        		 	AND after_value_string IN ('" . implode("','", $this->closedValues) . "')
	        		 	AND parent_id IN ('" . implode("','", $keys ). "')";

	        $result = $GLOBALS['db']->query($query);

	     	while($row = $GLOBALS['db']->fetchByAssoc($result)) {

	             $rowNums = $this->lvs->data['pageData']['idIndex'][$row['parent_id']]; // figure out which rows have this guid
	             foreach($rowNums as $rowNum) {
	                $this->lvs->data['data'][$rowNum]['RESOLUTION_TIME'] = $this->format($row['date_entered'], $row['date_created']);
	             }
	        }
        }

        //Check for the first Response
        if (isset($columnsDisplay['first_response'])){

	        // Staticly coded for now :( - will add more flexiblity in the future
	        $query = "SELECT cases_audit.parent_id, cases_audit.date_created, cases.id, cases.date_entered
	        		 FROM cases_audit
	        		 INNER JOIN cases
	        		 	ON cases_audit.parent_id=cases.id
	        		 WHERE field_name='status'
	        		 	AND before_value_string='New'
	        		 	AND parent_id IN ('" . implode("','", $keys ). "')";

	        $result = $GLOBALS['db']->query($query);

	     	while($row = $GLOBALS['db']->fetchByAssoc($result)) {

	             $rowNums = $this->lvs->data['pageData']['idIndex'][$row['parent_id']]; // figure out which rows have this guid
	             foreach($rowNums as $rowNum) {
	                $this->lvs->data['data'][$rowNum]['FIRST_RESPONSE'] = $this->format($row['date_entered'], $row['date_created']);

             	}
	        }
        }
        $this->lvs->ss->assign('ACCOUNT', $app_strings['LBL_ACCOUNT']);
        $this->lvs->ss->assign('EXPORT', $app_strings['LBL_EXPORT']);
        $this->lvs->ss->assign('id', $this->id);
        require_once('include/utils.php');
        $case_mod_strings= return_module_language($GLOBALS['current_language'], 'Cases');
        $this->lvs->ss->assign('CASE_NUM', $case_mod_strings['LBL_CASE_NUMBER']);
        if (isset($_REQUEST['caseNumber'])) $this->lvs->ss->assign('CASE_NUM_VALUE', $_REQUEST['caseNumber']);
     	if (isset($_REQUEST['accountName'])) $this->lvs->ss->assign('ACCOUNT_HIDDEN', $_REQUEST['accountName']);

        if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){

			$this->lvs->ss->assign('YES', $GLOBALS['app_list_strings']['checkbox_dom'][1]);

			for($i=0;isset( $this->lvs->data['data'][$i]);$i++){

				foreach ($this->lvs->data['data'][$i] as $key => $value){

					$value = str_replace('"', '""',$value);
					$value = strtr($value, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
					$value = strip_tags($value);
					$this->lvs->data['data'][$i][$key]= '"'.$value.'"';
				}

			}

        	header("Pragma: cache");
			header("Content-type: application/octet-stream; charset=".$locale->getExportCharset());
			header("Content-Disposition: attachment; filename=CasePerformanceExport.csv");
			header("Content-transfer-encoding: binary");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
			header("Cache-Control: post-check=0, pre-check=0", false );

        }
    }

   /**
     * Displays the Dashlet, must call process() prior to calling this
     *
     * @return string HTML that displays Dashlet
     */
    function display() {
    	if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){
        	return str_replace("&nbsp;", "", strip_tags($this->lvs->display(false)));
    	} else {
    		return Dashlet::display() . $this->lvs->display(false);
    	}
    }

   function getHeader(){
	if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){
		return '';
	}else{
		return parent::getHeader();
	}
   }

   function getFooter(){
	if(isset($_REQUEST['doExport']) && $_REQUEST['doExport']){
		return '';
	}else{
		return parent::getFooter();
	} 
   }
}

?>
