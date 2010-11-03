 <?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/Cases/Case.php');
require_once('SLAcountdown.data.php');
require_once('BusinessTime.php');

class SLAcountdown extends DashletGeneric {

	var $configureTpl = 'custom/modules/Home/Dashlets/SLAcountdown/SLAcountdownConfigure.tpl';
	var $displayTpl = 'custom/modules/Home/Dashlets/SLAcountdown/SLAcountdownDisplay.tpl';
	var $holidays;
	var $newStatus; //the index key for status that represents new cases
	var $myItemsOnly = false;
	var $businessHours = true;
	var $enableSearch = false;  //Show the search bar for accounts and case numbers
	var $enableSeconds = false;  //Show seconds in the timer
	var $refresh = '300';  // number of seconds to auto refresh the dashlet, less than 60 seconds will not refresh
	var $jsTimer = false;  //use Javascript Timer - still expirmental
	var $slaRange; // Define the SLA range in the SLAcountdown.data.php file..
	var $sla = "support_service_level_c"; //the field used for determining the sla of a case.  No SLA is set by default, should be custom field in cases.
	var $priority = "priority_level";  // just in case you didn't use the out of the box prioity, you should define the field here.  The default is the OOTB.
	var $opentime= array(
	     "Mon" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Tue" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Wed" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Thu" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Fri" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    );

    function SLAcountdown($id, $def = null) {
        global $current_user, $app_strings, $dashletData, $timedate;
        parent::DashletGeneric($id, $def);

        if($this->enableSearch || $this->jsTimer){
        	$this->hasScript = true;  // dashlet has javascript attached to it
        }
        $this->loadLanguage('SLAcountdown', 'custom/modules/Home/Dashlets/');

		if(!empty($def['businessHours'])) $this->businessHours = $def['businessHours'];
		if(!empty($def['opentime'])) $this->opentime = $def['opentime'];
		if(!empty($def['priority'])) $this->priority = $def['priority'];
		if(!empty($def['sla'])) $this->sla = $def['sla'];
        if(empty($def['title'])) $this->title = $this->dashletStrings['LBL_TITLE'];
		if(!empty($dashletData['SLAcountdown']['autoRefresh'])) $this->refresh = $dashletData['SLAcountdown']['autoRefresh'];

        $this->searchFields = $dashletData['SLAcountdown']['searchFields'];
        $this->columns = $dashletData['SLAcountdown']['columns'];
        $this->holidays = $dashletData['SLAcountdown']['holidays'];
        $this->slaRange = $dashletData['SLAcountdown']['sla'];
        $this->newStatus = $dashletData['SLAcountdown']['newStatus'];

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
        $options['sla'] = $req['sla'];
        if(!isset($req['priority']) || $req['priority'] =='') {
        	$options['priority'] = 'priority';
        } else {
        	$options['priority'] = $req['priority'];
        }

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






        $str = $ss->fetch('custom/modules/Home/Dashlets/SLAcountdown/SLAcountdownScript.tpl');
        return $str; // return parent::display for title and such
    }

    /**
     * Sets up the display options template
     *
     * @return string HTML that shows options
     */
    function processDisplayOptions() {
    	global $current_user, $timedate;
    	$selected = '';
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
                // this is for the columns we'll use for SLA and Priority.
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

        foreach ($this->seedBean->column_fields as $num => $name){
			$translated = $name;
			if(!empty($this->seedBean->field_defs[$name]['vname'])){
	    		$translated = translate($this->seedBean->field_defs[$name]['vname'] , $this->seedBean->module_dir);
			}
			$select['sla'][$name] =  trim($translated, ':');
			$select['priority'][$name] = trim($translated, ':');
        }

		$sla_select = "<select size='3' name='sla' >\n";
        foreach ($select['sla'] as $k => $v){

        	if ($k == $this->sla) $selected = "selected";
        	$sla_select .= "<OPTION value='$k' $selected>$v</OPTION>\n";
        	if ($selected == "selected") $selected = null;
        }
   		$sla_select .= "</select>";

		$prioirty_select = "<select size='3' name='priority' >\n";
        foreach ($select['priority'] as $k => $v){

        	if ($k == $this->priority) $selected = "selected";
        	$prioirty_select .= "<OPTION value='$k' $selected>$v</OPTION>\n";
        	if ($selected == "selected") $selected = null;
        }
   		$prioirty_select .= "</select>";


		$sla[0]['label'] =$this->dashletStrings['LBL_SLA'];
		$sla[0]['input'] = $sla_select;
		$priority[0]['input'] = $prioirty_select;
        $priority[0]['label'] = $this->dashletStrings['LBL_PRIORITY'];

        $this->configureSS->assign('sla', $sla);
        $this->configureSS->assign('priority', $priority);

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
    function format($elapsedSeconds, $type, $showSecs = false){
    	global $timedate;
 
		if ($type == 'timeleft') {

	    	$extra_text = null;

	    	if ($elapsedSeconds < 0) {

	 			$format_color="red";
				$elapsedSeconds = abs($elapsedSeconds);
				$extra_text = "<blink> " . $this->dashletStrings['LBL_OVERDUE'];
				$extra_text .= "</blink>";
	    	}  	elseif ($elapsedSeconds <1800  && $elapsedSeconds > 1){
	    		$format_color="orange";
	    		$extra_text = " ". $this->dashletStrings['LBL_UNTIL'];
	    	} elseif ($elapsedSeconds < 1){
	    		$format_color="red";
	    	} else {
	    		$format_color = "green";
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
	    		$result = $this->dashletStrings['LBL_NO_TIME_LEFT'];
			} elseif ($elapsedDays == 0 && $remainderHours == 0 && $remainderMinutes==0  && $showSecs==true) {
	    		$result = $remainderSeconds  . $this->dashletStrings['LBL_SECS'];
	    	} elseif ($elapsedDays == 0 && $remainderHours == 0) {
	    		$result = $remainderMinutes  . $this->dashletStrings['LBL_MINS'];
	    	} elseif ($elapsedDays == 0) {
	    		$result = $remainderHours  . $this->dashletStrings['LBL_HOURS'] .' '. $remainderMinutes . $this->dashletStrings['LBL_MINS'];
	    	} else {
	    		$result = $elapsedDays . $this->dashletStrings['LBL_DAYS'] .' '. $remainderHours . $this->dashletStrings['LBL_HOURS'] .' '. $remainderMinutes . $this->dashletStrings['LBL_MINS'];
	    	}


	    	if($this->enableSeconds && !isset($noresults))$result .= ' '. $remainderSeconds . $this->dashletStrings['LBL_SECS'];

	    	$result = "<font color=$format_color> " . $result . $extra_text . "</font>";
    	}


    	if ($type == 'datedue'){
    		$result = $timedate->handle_offset(date('Y-m-d H:i:s', $elapsedSeconds), $timedate->get_date_time_format());
    	}

		return $result;
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
    	global $current_language, $app_strings, $mod_strings, $app_list_strings, $timedate;

    	$displayRows = "1000";  // a hack to get teh listview to increase the limit and show EVERYTHING

    	//yet another dirty hack to get the list view to always get everything
     	if (isset($_REQUEST['Home2_CASE_offset'])) {
			$offset = $_REQUEST['Home2_CASE_offset'];
		} else {
			$offset = 0;
		}
		$_REQUEST['Home2_CASE_offset'] = 0;

		$keys = array();
        $currentSearchFields = array();
        $configureView = true; // configure view or regular view
        $query = false;
        $whereArray = array();
        $lvsParams['massupdate'] = false;

        // apply filters
        if(isset($this->filters) || $this->myItemsOnly) {
            $whereArray = $this->buildWhere();
        }

        $this->lvs->export = false;
        $this->lvs->multiSelect = false;

        $this->addCustomFields();

        // columns
        $displayColumns = array();
        if(isset($this->displayColumns)) { // use user specified columns
            foreach($this->displayColumns as $name => $val) {
                $displayColumns[strtoupper($val)] = $this->columns[$val];
                $displayColumns[strtoupper($val)]['label'] = trim($displayColumns[strtoupper($val)]['label'], ':');// strip : at the end of headers
            }
        }
        else { // use the default
            foreach($this->columns as $name => $val) {
                if(!empty($val['default']) && $val['default']) {
                    $displayColumns[strtoupper($name)] = $val;
                    $displayColumns[strtoupper($name)]['label	'] = trim($displayColumns[strtoupper($name)]['label'], ':');
                }
            }
        }

        $this->lvs->displayColumns = $displayColumns;

        $this->lvs->lvd->setVariableName($this->seedBean->object_name, array());

		$lvsParams['overrideOrder'] = true;
	    $lvsParams['orderBy'] = $this->priority;



		if (!empty($this->filters) && !in_array($this->newStatus, $this->filters['status'])){
			$lvsParams['custom_select'] = " , max(notes.date_entered) last_note ";
			$lvsParams['custom_from'] = " JOIN notes ON cases.id = notes.parent_id ";
			$lvsParams['custom_where'] = " GROUP BY cases.id ";
		}

        if(!empty($this->displayTpl))
        {
            $this->lvs->setup($this->seedBean, $this->displayTpl, implode(' AND ', $whereArray), $lvsParams, 0, $displayRows/*, $filterFields*/);
            if(in_array('CREATED_BY', array_keys($displayColumns))) { // handle the created by field
                foreach($this->lvs->data['data'] as $row => $data) {
                    $this->lvs->data['data'][$row]['CREATED_BY'] = get_assigned_user_name($data['CREATED_BY']);
                }
            }
	    /*
            ** @author: DTam
            ** SUGARINTERNAL CUSTOMIZATION
            ** ITRequest #: 17057
	    ** Description: Fixing links in case priority dashlet
	    */
	    if(in_array('ACCOUNT_NAME', array_keys($displayColumns))) { // check that account id is set as a display option
            	foreach($this->lvs->data['data'] as $row => $data) { // loop over each Case
			$tempCaseID = $this->lvs->data['data'][$row]['ID'];
			//create case bean and get account info 
			$tempCaseBean = new aCase();
			$tempAccount = $tempCaseBean->getAccount($tempCaseID);
			$tempAccountID = $tempAccount['account_id'];
			// set account ID in case data to retrieved
                    	$this->lvs->data['data'][$row]['ACCOUNT_ID'] = $tempAccountID;
                }
            }
	    /* END SUGARINTERNAL CUSTOMIZATION */


            // assign a baseURL w/ the action set as DynamicAction
            foreach($this->lvs->data['pageData']['urls'] as $type => $url) {
                if($type == 'orderBy')
                    $this->lvs->data['pageData']['urls'][$type] = preg_replace('/(action=.*&)/Ui', 'action=DynamicAction&', $url) . '&DynamicAction=displayDashlet';
                else
                    $this->lvs->data['pageData']['urls'][$type] = preg_replace('/(action=.*&)/Ui', 'action=DynamicAction&', $url) . '&DynamicAction=displayDashlet&sugar_body_only=1&id=' . $this->id;
            }

            $this->lvs->ss->assign('dashletId', $this->id);

        }

	    $columnsDisplay = array();
        if(is_array($this->displayColumns) && count($this->displayColumns) > 0) $columnsDisplay = array_flip($this->displayColumns);

        foreach($this->lvs->data['data'] as $num => $row) {
            $keys[] = $row['ID'];
        }

        //Check for the timeleft column and make sure that it displays
        if ((isset($columnsDisplay['time_left'])  || isset($columnsDisplay['date_due'])) && count($keys) > 0){

			$lvsParams['custom_select'] = isset($lvsParams['custom_select']) ? $lvsParams['custom_select'] : null;
			$lvsParams['custom_from'] = isset($lvsParams['custom_from']) ? $lvsParams['custom_from'] : null;
			$lvsParams['custom_where'] = isset($lvsParams['custom_where'] ) ? $lvsParams['custom_where'] : null;

	       // Variables are there in case we are looking for cases that are not in new status
	        $query = "SELECT cases.*, cases_cstm.* " . $lvsParams['custom_select'] ."
	        		 FROM cases" . $lvsParams['custom_from'] .
					 " LEFT JOIN cases_cstm ON cases.id=cases_cstm.id_c
	        		 WHERE  cases.id IN ('" . implode("','", $keys ). "')" . $lvsParams['custom_where'];


	        $result = $GLOBALS['db']->query($query);

	     	while($row = $GLOBALS['db']->fetchByAssoc($result)) {

	     		if (isset($lvsParams['custom_select'])){
	     			$date = $row['last_note'];
	     		} else {
	     			$date = $row['date_entered'];
	     		}

	     		$rowNums = $this->lvs->data['pageData']['idIndex'][$row['id']]; // figure out which rows have this guid
		        foreach($rowNums as $rowNum) {
		        	if (isset($columnsDisplay['time_left'])){
		        		$this->lvs->data['data'][$rowNum]['TIME_LEFT'] = $this->getTimeLeft($date,$row[$this->priority], $row[$this->sla]);
		        	}
		        	if (isset($columnsDisplay['date_due'])){
		        		$this->lvs->data['data'][$rowNum]['DATE_DUE'] = $this->getTimeLeft($date,$row[$this->priority], $row[$this->sla], true);
		        	}


		     	}
	   		}

			uasort($this->lvs->data['data'], array($this,"sort_time_left"));

			//we have all the data and it is sorted, so let's slice the array for the offset and limit

			$limit = $this->displayRows;
			$totalCount = $this->lvs->data['pageData']['offsets']['total'];

	 		$this->lvs->data['data'] = array_slice($this->lvs->data['data'], $offset, $limit, true);

			// now we need to lie to the pagination so it thinks it has pages to flip through.
			$totalCounted = empty($GLOBALS['sugar_config']['disable_count_query']);


			$this->lvs->data['pageData']['offsets']['current']= $offset;
			$this->lvs->data['pageData']['offsets']['next'] = $offset + $limit > $totalCount ? "-1" : $offset + $limit ;
			$this->lvs->data['pageData']['offsets']['prev'] = $offset - $limit < 0 ? "-1" : $offset - $limit ;

			$endOffset = (floor(($totalCount - 1) / $limit)) * $limit;


			if ($this->lvs->data['pageData']['offsets']['next'] != "-1" && $offset <= $endOffset - $limit){
				$this->lvs->data['pageData']['urls']['nextPage'] = 'index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&Home2_CASE_offset=' . $this->lvs->data['pageData']['offsets']['next'] . '&sugar_body_only=1&id=' . $this->id;
			}

			if (isset($offset) && $offset !=0){
				$this->lvs->data['pageData']['urls']['prevPage'] = 'index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&Home2_CASE_offset=' . $this->lvs->data['pageData']['offsets']['prev'] . '&sugar_body_only=1&id=' . $this->id;
			}
			if (isset($offset) && $offset !=0){
				$this->lvs->data['pageData']['urls']['startPage'] = 'index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&Home2_CASE_offset=0&sugar_body_only=1&id=' . $this->id;
			}
			$this->lvs->data['pageData']['urls']['endPage'] = 'index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&Home2_CASE_offset='.$endOffset.'&sugar_body_only=1&id=' . $this->id;

			//ok, pagination is fixed... just format it now.
	        foreach($this->lvs->data['data'] as $key => $value){
	        	if (isset($columnsDisplay['time_left'])){
	           		$this->lvs->data['data'][$key]['TIME_LEFT'] = $this->format($this->lvs->data['data'][$key]['TIME_LEFT'],'timeleft');
	        	}

	        	if (isset($columnsDisplay['date_due'])){
	           		$this->lvs->data['data'][$key]['DATE_DUE'] = $this->format($this->lvs->data['data'][$key]['DATE_DUE'], 'datedue');
	        	}
	     	}

        }

        $this->lvs->ss->assign('ACCOUNT', $app_strings['LBL_ACCOUNT']);
        $this->lvs->ss->assign('id', $this->id);
        $this->lvs->ss->assign('refreshTime', $this->refresh * 1000);
        $this->lvs->ss->assign('enableSearch', $this->enableSearch);
        require_once('include/utils.php');
        $case_mod_strings= return_module_language($GLOBALS['current_language'], 'Cases');
        $this->lvs->ss->assign('CASE_NUM', $case_mod_strings['LBL_CASE_NUMBER']);

    }

   /**
     * Displays the Dashlet, must call process() prior to calling this
     *
     * @return string HTML that displays Dashlet
     */
    function display() {
  		return Dashlet::display() . $this->lvs->display(false);

    }


	function getTimeLeft($startDate, $priority = 'P3', $sla = 'standard', $displayTimeDue = false){
		date_default_timezone_set("UTC");

		$gmnow = gmdate("Y-m-d H:i:s");
		$strgmnow = strtotime($gmnow);

		$slaTimeMin = $this->slaRange[$priority][$sla];

		$slaTimeSec = $slaTimeMin*60;

		$start = strtotime($startDate);
		if($this->businessHours == true) {

			$timeDue = $this->timeToGo($start, $slaTimeSec);
			$timeDue = $timeDue+$start;
		} else {
			$timeDue = $start +$slaTimeSec;
		}
		if($this->jsTimer == false && $displayTimeDue != true){

			$timeLeft = $timeDue - $strgmnow;
			return $timeLeft;
		}	else {
			return $timeDue;
		}
	}


	function timeToGo($start, $sla){

		$timeLeft = array();
		$timeToGo = 0;
		$timeOff= 0;
		$offTimeStart = 0;
		$timeLeftToday = 0;
		$remainder = $sla;


		$bt = new BusinessTimeForSLA();
		$bt->start = $start;
		$bt->holiday = $this->holidays;
		$bt->opentime = $this->opentime;

		//start with triming start time if the start time is before the beginning of the work day. Then count offtime.
		if (!$bt->openOffice($start)) {
			$startTime = $bt->nextOpenOffice($start);
		} else {
			$startTime = $start;
		}

		if ($startTime != $bt->start) {
			$offTimeStart = $startTime - $bt->start;
		}

		while($remainder != 0){
			$offTimeToday=0;
    		$currentTime = $startTime + $remainder;
			$cob = $bt->nextCloseOffice($startTime);
			$nextSob = $bt->nextOpenOffice($cob);

			// if SLA is met within the day
			if($currentTime <= $cob){
				$onTimeToday = $currentTime - $startTime; //basically the sla at the start of the event.
			// if there is a remanider, add it, bump to the next day, and loop
			} else {
				$onTimeToday = $cob - $startTime;
				$offTimeToday = $nextSob-$cob;
				$startTime = $nextSob;
			}

			$timeLeft['ontime'][] = $onTimeToday;
			$timeToGo = array_sum($timeLeft['ontime']);

			$remainder = $sla-$timeToGo;
			if ($remainder === 0) {
				//check to see if the duedate is at the end of the day, if so, add the offtime to the end of the day.
				if ($cob == $start+$timeToGo+$timeOff+$offTimeStart+$offTimeToday){
					$offTimeToday = $nextSob - $cob;
				} else {

					$offTimeToday = 0;
				}
			}

			$timeLeft['offtime'][] = $offTimeToday;
			$timeOff = array_sum($timeLeft['offtime']);


		}

		$timeReturn = $timeToGo+$timeOff+$offTimeStart;

		return $timeReturn;
	}

	function sort_time_left($x, $y){
		return ($x['TIME_LEFT'] > $y['TIME_LEFT']);
	}

	function to_display_date_time($date, $meridiem = true, $offset = true, $user = null) {
		global $timedate;
		$date = trim($date);

		if (empty($date)) {
			return $date;
		}
		if ($offset) {
			$date = $timedate->handle_offset($date, "Y-m-d H:i:s", true, $user);
		}

		return $timedate->to_display($date, $timedate->get_db_date_time_format(), $timedate->get_date_time_format($meridiem, $user));
	}

}

?>
