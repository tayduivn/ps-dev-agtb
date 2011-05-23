<?php

class IBMHelper {

	// base URL for ISP server
	//public static $isp_base_url = 'https://btitwas.lexington.ibm.com/viewer/sugar.gt';
	public static $isp_base_url = 'https://isppilot.pok.ibm.com/viewer/sugar.gt';

	// provides a mapping between our two AVLs for countries -- one is numerically indexed, one is indexed with two-letter country codes
	public static $numeric_to_char_country_mapping = array(
		'614' => 'AF',
		'603' => 'AL',
		'617' => 'DZ',
		'610' => 'AO',
		'613' => 'AR',
		'607' => 'AM',
		'616' => 'AU',
		'618' => 'AT',
		'358' => 'AZ',
		'619' => 'BS',
		'620' => 'BH',
		'615' => 'BD',
		'621' => 'BB',
		'626' => 'BY',
		'624' => 'BE',
		'840' => 'BJ',
		'627' => 'BM',
		'629' => 'BO',
		'699' => 'BA',
		'636' => 'BW',
		'631' => 'BR',
		'643' => 'BN',
		'644' => 'BG',
		'841' => 'BF',
		'645' => 'BI',
		'625' => 'CM',
		'649' => 'CA',
		'669' => 'CV',
		'647' => 'KY',
		'810' => 'CF',
		'844' => 'TD',
		'655' => 'CL',
		'641' => 'CN',
		'661' => 'CO',
		'667' => 'CG',
		'662' => 'CD',
		'663' => 'CR',
		'637' => 'CI',
		'704' => 'HR',
		'666' => 'CY',
		'668' => 'CZ',
		'678' => 'DK',
		'670' => 'DJ',
		'681' => 'DO',
		'683' => 'EC',
		'865' => 'EG',
		'829' => 'SV',
		'383' => 'GQ',
		'745' => 'ER',
		'602' => 'EE',
		'698' => 'ET',
		'702' => 'FI',
		'706' => 'FR',
		'656' => 'GA',
		'753' => 'GM',
		'651' => 'GE',
		'724' => 'DE',
		'725' => 'GH',
		'726' => 'GR',
		'731' => 'GT',
		'679' => 'GN',
		'879' => 'GW',
		'640' => 'GY',
		'733' => 'HT',
		'735' => 'HN',
		'738' => 'HK',
		'740' => 'HU',
		'742' => 'IS',
		'744' => 'IN',
		'749' => 'ID',
		'750' => 'IR',
		'752' => 'IQ',
		'754' => 'IE',
		'755' => 'IL',
		'758' => 'IT',
		'759' => 'JM',
		'760' => 'JP',
		'762' => 'JO',
		'694' => 'KZ',
		'764' => 'KE',
		'766' => 'KR',
		'767' => 'KW',
		'695' => 'KG',
		'608' => 'LV',
		'768' => 'LB',
		'711' => 'LS',
		'770' => 'LR',
		'772' => 'LY',
		'638' => 'LT',
		'736' => 'MO',
		'705' => 'MK',
		'769' => 'MW',
		'778' => 'MY',
		'382' => 'ML',
		'780' => 'MT',
		'717' => 'MR',
		'781' => 'MX',
		'787' => 'MD',
		'713' => 'ME',
		'642' => 'MA',
		'782' => 'MZ',
		'646' => 'MM',
		'682' => 'NA',
		'788' => 'NL',
		'791' => 'AN',
		'796' => 'NZ',
		'799' => 'NI',
		'880' => 'NE',
		'804' => 'NG',
		'806' => 'NO',
		'805' => 'OM',
		'808' => 'PK',
		'811' => 'PA',
		'813' => 'PY',
		'815' => 'PE',
		'818' => 'PH',
		'820' => 'PL',
		'822' => 'PT',
		'823' => 'QA',
		'826' => 'RO',
		'821' => 'RU',
		'831' => 'RW',
		'839' => 'LC',
		'827' => 'ST',
		'832' => 'SA',
		'635' => 'SN',
		'707' => 'RS',
		'622' => 'SC',
		'833' => 'SL',
		'834' => 'SG',
		'693' => 'SK',
		'708' => 'SI',
		'835' => 'SO',
		'864' => 'ZA',
		'838' => 'ES',
		'652' => 'LK',
		'842' => 'SD',
		'843' => 'SR',
		'853' => 'SZ',
		'846' => 'SE',
		'848' => 'CH',
		'850' => 'SY',
		'858' => 'TW',
		'363' => 'TJ',
		'851' => 'TZ',
		'856' => 'TH',
		'718' => 'TG',
		'859' => 'TT',
		'729' => 'TN',
		'862' => 'TR',
		'359' => 'TM',
		'857' => 'UG',
		'889' => 'UA',
		'680' => 'AE',
		'866' => 'GB',
		'897' => 'US',
		'869' => 'UY',
		'741' => 'UZ',
		'871' => 'VE',
		'855' => 'VN',
		'849' => 'YE',
		'883' => 'ZM',
		'825' => 'ZW',
	);

	public static function getISPTargetURL($isp_page, $smarty_fields) {

		$account_id = $smarty_fields['id']['value'];

		$account_type = 'client';
		$client_cmr_number = $smarty_fields['client_id']['value'];
		$account_country = $smarty_fields['billing_address_country']['value'];

		if (self::isCMR($account_id)) {
			$account_type = 'cmr';
			$client_cmr_number = $smarty_fields['cmr_number']['value'];
		}

		// defaulting to USA
		$country_code = 897;

		if (!empty($account_country) && array_search($account_country, self::$numeric_to_char_country_mapping) !== FALSE) {
			$country_code = array_search($account_country, self::$numeric_to_char_country_mapping);
		}

		$user_email = $GLOBALS['current_user']->email1;

		$url = self::$isp_base_url . "?page=clientSelectProxy&user={$user_email}&source={$account_type}&id={$client_cmr_number}&cc={$country_code}&target={$isp_page}";

		return $url;

	}
	
	public static function getISPTabContent($module, $isp_page, $tabIndex, $url, $iframe_height = 500) {

		$isp_page = strtoupper($isp_page);

		return "<iframe style='border-width: 0px;' width='100%' height='{$iframe_height}' id='isp_{$isp_page}'></iframe>

		<script>
		YUI().use('node-base', function(Y) {
			function init() {
				tab = {$module}_detailview_tabs.getTab({$tabIndex});
				tab.on('click', function() {
					iframe = document.getElementById('isp_{$isp_page}');

					if (iframe.src == '') {
						document.getElementById('isp_{$isp_page}').src = '{$url}';
					}
				});
			}

			Y.on('domready', init); 
		});
		</script>";

	}

	
	// Generate an IBM Opportunity Number
	public static function generateUniqueOppNumber(){
		$u = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$opp_uniqid = '';
		for($i = 0; $i < 9; $i++){
			if($i == 2)
				$opp_uniqid .= "-";
			$opp_uniqid .= strtoupper($u[rand(0, strlen($u) - 1)]);
		}
		return $opp_uniqid;
	}
	
	// accepts an Account bean and decides whether or not it's a CMR
	// returns TRUE or FALSE
	public static function isCMR($account_bean_or_id) {
		$account_bean = $account_bean_or_id;
		
		if( is_string($account_bean_or_id)){
			$account_bean = new Account();
			$account_bean->retrieve($account_bean_or_id);
		}
		
		if (empty($account_bean->id)){
			return FALSE;
		}
		if (!empty($account_bean->parent_id)) {
			return TRUE;
		}

		return FALSE;
	}

	// accepts an Account bean and decides whether or not it's a Client
	// returns TRUE or FALSE
	public static function isClient($account_bean_or_id) {
		$account_bean = $account_bean_or_id;
		
		if(is_string($account_bean_or_id)){
			$account_bean = new Account();
			$account_bean->retrieve($account_bean_or_id);
		}
		
		if (empty($account_bean->id)){
			return FALSE;
		}
		if (empty($account_bean->parent_id)) {
			return TRUE;
		}

		return FALSE;
	}
	
	public static function getIDFromClientID($client_id){
		$return_val = '';
		$res = $GLOBALS['db']->query("SELECT id FROM accounts WHERE client_id = '{$client_id}' AND parent_id IS NULL AND deleted = 0");
		if($res){
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($row){
				$return_val = $row['id'];
			}
		}
		return $return_val;
	}
	
	public static function getCMROrClientIDFromAccountID($account_bean_or_id){
        $account_bean = $account_bean_or_id;

        if( is_string($account_bean_or_id)){
            $account_bean = new Account();
            $account_bean->retrieve($account_bean_or_id);
        }
		
		if(self::isCMR($account_bean)){
			return $account_bean->cmr_number;
		}
		if(self::isClient($account_bean)){
			return $account_bean->client_id;
		}
	}
	
	public static function getClientIDFromAccountID($account_id){
		$return_val = '';
		$query = "SELECT client_id FROM accounts WHERE id = '{$account_id}' AND deleted = 0";
		$res = $GLOBALS['db']->query($query);
		if($res){
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($row){
				$return_val = $row['client_id'];
			}
		}
		return $return_val;
	}

	public static function getCMRFromAccountID($account_id){
		$return_val = '';
		$query = "SELECT client_id FROM accounts WHERE id = '{$account_id}' AND deleted = 0";
		$res = $GLOBALS['db']->query($query);
		if($res){
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($row){
				$return_val = $row['cmr_number'];
			}
		}
		return $return_val;
	}

	// generates a random CMR Number (7 random digits)
	public static function generateCMRNumber() {
		return rand(1000000, 9999999);
	}

	// generates a random Client ID (0, followed by 7 random digits);
	public static function generateClientID() {
		return '0' . rand(1000000, 9999999);
	}

	/* TAGGING METHODS */

	// save tags for a given bean to the database
	// this could mean adding new tags or deleting tags that were not present in the user-submitted data
	public static function saveTags($module_name, $record_id, $raw_tags_string) {
		$submitted_tags = $raw_tags_string;
		if(is_string($raw_tags_string)){
			$submitted_tags = self::rawTagStringToArray($raw_tags_string);
		}
		
		$existing_tags = array();

		$existing_tags_res = $GLOBALS['db']->query("SELECT tag FROM tags WHERE module_name = '{$module_name}' AND record_id = '{$record_id}'");
		while ($row = $GLOBALS['db']->fetchByAssoc($existing_tags_res)) {
			$existing_tags[] = $row['tag'];
		}
		
		$tags_to_add = array_diff($submitted_tags, $existing_tags);
		$tags_to_remove = array_diff($existing_tags, $submitted_tags);
		
		foreach ($tags_to_add as $tag) {
			$GLOBALS['db']->query("INSERT INTO tags SET module_name = '{$module_name}', record_id = '{$record_id}', tag = '" . $GLOBALS['db']->quote($tag) . "'");
		}

		foreach ($tags_to_remove as $tag) {
			$GLOBALS['db']->query("DELETE FROM tags WHERE module_name = '{$module_name}' AND record_id = '{$record_id}' AND tag = '" . $GLOBALS['db']->quote($tag) . "'");
		}
		
		return TRUE;

	}

	public static function rawTagStringToArray($raw_tags_string) {
		$tags = explode(',', $raw_tags_string);

		// clean up the tags list
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = trim($tags[$i]);

			if (strlen($tags[$i]) == 0) {
				unset($tags[$i]);
			}
		}

		return $tags;

	}

	// get tags associated with this record
	function getRecordTags($bean) {
		$tags = array();

		$res = $GLOBALS['db']->query("SELECT DISTINCT tag FROM tags WHERE module_name = '{$bean->module_dir}' AND record_id = '{$bean->id}' ORDER BY tag");
		while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
			$tags[$row['tag']] = $row['tag'];
		}

		return $tags;
	}

	// get all tags associated with the given module
	public static function getModuleTags($module_name) {
		$tags = array();

		$res = $GLOBALS['db']->query("SELECT DISTINCT tag FROM tags WHERE module_name = '" . $GLOBALS['db']->quote($module_name) . "' ORDER BY tag");
		while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
			$tags[$row['tag']] = $row['tag'];
		}

		return $tags;
	}

	// Logic to find a related account starting from the passed bean, needed for last interaction update on Accounts 
	public static function searchRelatedAccount($bean) {

		require_once('modules/Contacts/Contact.php');

		// Handling for Calls/Meetings/Emails
		if($bean->object_name == 'Call' || $bean->object_name == 'Meeting' || $bean->object_name = 'Email') {

			// directly related account
			if(isset($bean->parent_type) && $bean->parent_type == 'Accounts') {
				return $bean->parent_id;
			}

			// via contact -> account
			if($bean->parent_type == 'Contacts') {
				$contact = new Contact();
				$contact->retrieve($bean->parent_id);
	
				if(isset($contact->account_id)) {
					return $contact->account_id;
				}
			}
		}

		return(false);

	}

	// update last interaction date for Account	
	public static function updateLastInteractionDate($account_id, $currentDateTime) {

		global $timedate;
		require_once('modules/Calendar/DateTimeUtil.php');
		require_once('modules/Accounts/Account.php');

		$account_bean = new Account();
		$account_bean->retrieve($account_id);

        if(!empty($account_bean->id)) {
		
			$GLOBALS['log']->debug('XXX account = '.$account_bean->name);	
			$oldDateTime = DateTimeUtil::get_time_start($timedate->to_db($account_bean->last_interaction_c));
			$newDateTime = DateTimeUtil::get_time_start($timedate->to_db($currentDateTime));	
			
			// update if newer
			if($newDateTime->ts > $oldDateTime->ts) {
				$account_bean->last_interaction_c = $currentDateTime;
				$account_bean->save();
			}
        }

	}
	
	// save opportunities_users relationship with user_rol
	public static function saveOpportunityUsers($opp_id, $rel_data) {
		
		// flush all relationships
		$flush = 'UPDATE opportunities_users SET deleted = 1 WHERE opportunity_id = "'.$opp_id.'"';
		$GLOBALS['db']->query($flush);
		
		// add relationships with role
		foreach($rel_data as $rel) {
			$rel_id = create_guid();
			$rel_date = gmdate('Y-m-d H:i:s');
			$insert = 'INSERT INTO opportunities_users (id, opportunity_id, user_id, user_role, date_modified)
						VALUES ("'.$rel_id.'", "'.$opp_id.'", "'.$rel['user_id'].'", "'.$rel['role_id'].'", "'.$rel_date.'")';
			$GLOBALS['db']->query($insert);		
		}
		
	}
	
	// Pass in an array of ibm_revenuelineitems_products id values
	public static function getProductValuesFromKeys($product_id_array, $include_blanks = true){
		$sql = "select id, name from ibm_revenuelineitems_products where id in ('".implode("','", $product_id_array)."') order by level ASC";
		$return_array = array();
		$res = $GLOBALS['db']->query($sql);
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$return_array[$row['id']] = $row['name'];
		}
		
		if($include_blanks){
			foreach($product_id_array as $id){
				if(!isset($return_array[$id])){
					$return_array[$id] = '';
				}
			}
		}
		
		return $return_array;
	}
	
	// BEGIN sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE
	public static function getLargeEnum($key){
		$i = 1;
		$s = array();
		while(file_exists("custom/include/language/{$key}{$i}.php")){
			require("custom/include/language/{$key}{$i}.php");
			$i++;
		}
		return $s;
	}
	// END sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE
	
	// START jvink - Abstraction to find opportunity owner as suggested by Julian
	// Opp owner = assinged user id for now
	public static function getOpptyOwner($oppId) {
		require_once('modules/Opportunities/Opportunity.php');
		$opp = new Opportunity();
		if($opp->retrieve($oppId)) {
			return $opp->assigned_user_id;
		}
		return false;
	}
	// END jvink
	
	// START jvink - hoover code stolen from ListViewData to be able to reuse it
	// params for popup = array(viewLink,editLink,width,icon,clickToOpen,clickToClose)
	// params for iframe = array(iframe,width,height,icon) 
	// params for both = delay (integer)
	public static function createHoover($title, $html, $params, $editAccess) {
        global $app_strings;
		
		$delay_string = " DELAY, 200, ";
 	 	if(!empty($params['iframe']) || !empty($params['clickToOpen']) || !empty($params['clickToClose']) || (isset($params['delay']) && $params['delay'] == 0)){
			$delay_string = '';
		}
		else if(!empty($params['delay'])){
			$delay_string = " DELAY, {$params['delay']}, ";
		}
        // iframe support
        if(!empty($params['iframe'])) {
        	$html = "<iframe src='{$params['iframe']}' style='border-width: 0px;' width='100%' height='".(empty($params['height']) ? '300' : $params['height'])."'>";
			$html .= "Your browser does not support iframes</iframe>";
        }       
		
		$editLinkOnClick = empty($params['editLinkOnClick']) ? false : true;
		$additionalJavascript = !empty($params['additionalJavascript']) ? $params['additionalJavascript'] : "";
		$editLink = !empty($params['editLink']) ? $params['editLink'] : "";
        // take care of single quotes
        $html = str_replace(array("&#039", "'"), '\&#039;', $html); // no xss!
        // take care of double quotes
        $html = str_replace(array("&#034", '"'), '\&#034;', $html); // no xss!
        $additionalJavascript = str_replace(array("&#034", '"'), '\&#034;', $additionalJavascript); // no xss!
        $editLink = str_replace(array("&#034", '"'), '\&#034;', $editLink); // no xss!

        // specify icon or use default
        (!empty($params['icon']) ? $icon = $params['icon'] : $icon = 'info_inline.png');
        
        // fill in default string if empty html & setup icon style (grayed out if no html)
        $gray_style = '';
		if(empty($params['iframe'])) {
	        if(trim($html) == '') {
	        	$html = $app_strings['LBL_NONE'];	
	        	$gray_style = 'opacity : 0.4; filter: alpha(opacity=40);';
	        }
	        $html = str_replace(array("\rn", "\r", "\n"), array('','','<br />'), $html);
		}

		// for iframes dont want auto popup
		if(!empty($params['iframe']) || !empty($params['clickToOpen'])) { $action = 'onclick'; } else { $action = 'onmouseover'; }
		
        $result = "<span $action=\"overlib('".$html."', CAPTION, '<div style=\'float:left\'>$title</div><div style=\'float: right\'>";

        // add edit link if requested and url passed
        if(empty($params['iframe'])) {
			$linkType = empty($editLinkOnClick) ? "href" : "onclick";
        	if($editAccess) $result .= (!empty($editLink) ? "<a title=\'{$app_strings['LBL_EDIT_BUTTON']}\' {$linkType}=\'{$editLink}\'><img  border=0 src=".SugarThemeRegistry::current()->getImageURL('edit_inline.gif')."></a>" : '');
        }
        	
        // default view link if url passed
        if(empty($params['iframe'])) {
        	$result .= (!empty($params['viewLink']) ? "<a title=\'{$app_strings['LBL_VIEW_BUTTON']}\' href={$params['viewLink']}><img style=\'margin-left: 2px;\' border=0 src=".SugarThemeRegistry::current()->getImageURL('view_inline.gif')."></a>" : '');
        }

        // mouseoff/out is disabled for iframes for better user experience
        $result .= "', {$delay_string} STICKY, ".(empty($params['iframe']) && empty($params['clickToClose']) ? "MOUSEOFF,": "")." 1000, WIDTH, "
            . (empty($params['width']) ? '300' : $params['width'])
            . ", CLOSETEXT, '<img border=0 style=\'margin-left:2px; margin-right: 2px;\' src=".SugarThemeRegistry::current()->getImageURL('close.gif')."></div>', "
            . "CLOSETITLE, '{$app_strings['LBL_ADDITIONAL_DETAILS_CLOSE_TITLE']}', CLOSECLICK, FGCLASS, 'olFgClass', "
            . "CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass'); "
			. $additionalJavascript
			."\" "
            . (empty($params['iframe']) && empty($params['clickToClose']) ? "onmouseout=\"return nd(1000);\"": "")."><img style='padding: 0px 5px 0px 2px; $gray_style' border='0' src='".SugarThemeRegistry::current()->getImageURL($icon)."' ></span>";
                
		return $result;
		
	}
	// END jvink

	// START jvink - create icon with link (if url is empty, we use a grayed out icon)
	// TODO: add grey icons if required !
	public static function createIcon($icon, $url, $new_window = false) {
		$style = '';
		if(! $url) { $style = ' style="opacity : 0.4; filter: alpha(opacity=40);" '; }
		$img ='<img src="'.SugarThemeRegistry::current()->getImageURL($icon).'" border="0" '.$style.' />';
		if($url) { return '<a href="'.$url.'" '.(!empty($new_window) ? 'target="_new"' : '').'>'.$img.'</a>'; }
		return $img;
	}
	// END jvink

	public static function get_business_unit($user_id) {
		$q_user = $GLOBALS['db']->query('SELECT employee_department FROM users WHERE id = "'.$user_id.'" AND deleted = 0');
		if($user = $GLOBALS['db']->fetchByAssoc($q_user)) {
			return $user['employee_department'];
		}
		return false;
	}
	
	public static function get_roadmap_type($user_id){
		$dep = self::get_business_unit($user_id);
		$type = 'Generic';
		switch($dep){
			case 'SWG':
				$type = 'SWG';
				break;
			case 'STG':
				$type = 'STG';
				break;
			case 'SND':
				$type = 'SD';
				break;
			case 'Services':
				$type = 'Services';
				break;
		}
		return $type;
	}

	public static function get_winplan_type($user_id){
		$dep = self::get_business_unit($user_id);
		$type = 'Generic';
		switch($dep){
			case 'SWG':
				$type = 'SWG';
				break;
			case 'STG':
				$type = 'STG';
				break;
			case 'SND':
				$type = 'Generic';
				break;
			case 'Services':
				$type = 'Generic';
				break;
		}
		return $type;
	}

	// START jvink - return timeperiod dropdown and selected label
	public static function get_timeperiods_html_options($user_id, $selected_timeperiod_id) {
		require_once('modules/Forecasts/Common.php');

		$focus = new Common();	
		$focus->set_current_user($user_id);
		$focus->setup();
		$focus->get_my_timeperiods();

		// normally this should not happen, but show it in the UI if so
		// (only if an already use tp is deleted afterwards)
		$selected_label = 'unknown';		
		
		// get all timeperiods and load label
		$all_periods = $focus->get_all_timeperiods();
		if(array_key_exists($selected_timeperiod_id, $all_periods)) {
			$selected_label = $all_periods[$selected_timeperiod_id]['name'];
		}
		
		// verify if selected timeperiod id is present in my timeperiods
		$my_tp = $focus->my_timeperiods;
		if(array_key_exists($selected_timeperiod_id, $my_tp)) {
			$selected_id = $selected_timeperiod_id;
		
		// if not we just select the first one from my timeperiods as they are already ordered
		} else {
			$selected_id = false;
		}
		
		// finally build the html options stuff
		$time_select_list='';
		foreach ($focus->my_timeperiods as $key => $value) {
			if($key === $selected_id || ! $selected_id) { 
				$time_select_list .= "<OPTION VALUE='$key' SELECTED='SELECTED'>$value </OPTION> ";
				$selected_id = true;
			} else {
				$time_select_list .= "<OPTION VALUE='$key'>$value </OPTION>";
			}
		}
		return array('html_options' => $time_select_list, 'selected_label' => $selected_label);
	}
	// END jvink
	
	// STARt jvink -  return all level20 product id's for an oppty
	public static function oppty_get_level20($id) {
		$res = array();
		$sql = "SELECT prod.id, prod.name
				FROM ibm_revenuepportunities_c rli_opp
				INNER JOIN ibm_revenuelineitems rli
					ON rli.id = rli_opp.ibm_revenu04e3neitems_idb
					AND rli.deleted = 0
				INNER JOIN ibm_revenuelineitems_products prod
					ON rli.brand_code = prod.id
				WHERE rli_opp.ibm_revenud375unities_ida = '{$id}'
					AND rli_opp.deleted = 0";
		$q_l20 = $GLOBALS['db']->query($sql);
		while($l20 = $GLOBALS['db']->fetchByAssoc($q_l20)) {
			$res[$l20['id']] = $l20['name'];
		}
		return $res;
	}
	// END jvink

	public static function getReportingUsers($user_id, $recursive = false, $managers_only = false, $user_list = array()){
		$query = "SELECT users.id, users.user_name FROM users ";
		if($managers_only){
			$query .= "INNER JOIN users reportees ON users.id = reportees.reports_to_id ";
		}
		$query .= "WHERE users.reports_to_id = '{$user_id}'";
		$res = $GLOBALS['db']->query($query);
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$user_list[$row['id']] = $row['id'];
			if($recursive){
				$reportees_check_query = "SELECT count(*) count FROM users WHERE reports_to_id = '{$row['id']}'";
				$inner_row = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query($reportees_check_query));
				if($inner_row['count'] > 0){
					$user_list = $user_list + self::getReportingUsers($row['id'], $recursive, $managers_only, $user_list);
				}
			}
		}
		return $user_list;
	}
	
	// START jvink - divide decimal fields by 1000
	public static function decimals_to_display(& $bean) {
		foreach($bean->field_defs as $field => $def) {
			if($def['type'] == 'decimal') {
				if(isset($bean->$field)) {
					$bean->$field = $bean->$field / 1000;	
				}
			}	
		}
	}
	// END jvink
	
	// START jvink - divide decimal fields by 1000
	public static function decimals_to_db(& $bean) {
		foreach($bean->field_defs as $field => $def) {
			if($def['type'] == 'decimal') {
				if(isset($bean->$field)) {
					$bean->$field = $bean->$field * 1000;	
				}
			}	
		}
	}
	// END jvink

	// START jvink - abstract sales_stages and stuff for opportunities
	public static function get_oppty_closed_sales_stages() {
		return array('05');
	}
	
	public static function get_oppty_reason_won($return_str = false) {
		return array('WH','CH','CI','WI','WJ','CJ','WL','CL','WM','CM',
					 'WN','CN','WO','CO','WP','CP','WQ','CQ','WR','CR');
		
	}
	
	public static function get_oppty_default_closed_sales_stage($return_str = false) {
		return '05';
	}
	
	public static function array_to_db_string($array, $quote = "'") {
		$str = '';
		foreach($array as $stage) {
			$str .= $quote.$stage.$quote.',';
		}
		return rtrim($str,',');
	}
	// END jvink

	// START jvink - handler for inline-one-to-many relationships
	public static function inlineOneToMany_to_array($field_name) {
		$rel_data = array();
		if(isset($_POST[$field_name.'_max_rows'])) {
			$max_rows = $_POST[$field_name.'_max_rows'];
			if($max_rows) {
				for($i = 1; $i <= $max_rows; $i++) {
					$related_id = $field_name.'_id_'.$i;
					if(isset($_POST[$related_id]) && $_POST[$related_id] <> "") {
						// use id as a key to capture duplicate entries if any
						$rel_data[$_POST[$related_id]] = $_POST[$related_id];
					}
				}
			}
		}
		return $rel_data;
		
	}
	// END jvink
	
	// START jvink - return timeperiod array for given date
	public static function date_to_timeperiod($date) {
		$res = false;
		$sql = "SELECT *
				FROM timeperiods
				WHERE start_date <= '$date'
					AND end_date >= '$date'
					AND deleted = 0
					AND is_fiscal_year = 0
				LIMIT 0,1";
		$q_tp = $GLOBALS['db']->query($sql);
		if($tp = $GLOBALS['db']->fetchByAssoc($q_tp)) {
			return $tp;
		}
		return $res;
	}
	// END jvink

	// START jvink - get quota for a specific user, timeperiod and type (Direct/Rollup)
    public static function get_quota_unformatted($user_id, $timeperiod_id, $type) {
        $query="select amount_base_currency from quotas where deleted=0 and user_id='$user_id' and quota_type='$type' and timeperiod_id='$timeperiod_id'";
        $result = $GLOBALS['db']->query($query,true,"Error fetching quota");
        $row=$GLOBALS['db']->fetchByAssoc($result);
        if (!empty($row)) {
            return $row['amount_base_currency'];   
        }
        return null;
    }
    // END jvink
    
    // START jvink - return timeperiod details
	public static function get_timeperiod($timeperiod_id) {
		$sql = "SELECT * FROM timeperiods WHERE id = '{$timeperiod_id}' AND deleted = 0";
		$q = $GLOBALS['db']->query($sql);
		if($tp = $GLOBALS['db']->fetchByAssoc($q)) {
			return $tp;
		}
		return false;
	}
	// END jvink
    
	// START jvink - return last commit for rollups 
	public static function get_last_commit($user_id, $timeperiod_id, $forecast_type, $display=true) {
	
		switch($forecast_type) {
			case 'Rollup':
				$fields = array('roadmap', 'best_case',	'worst_case', 'gap_to_plan');
				break;
			case 'Direct':
				$fields = array('solid', 'at_risk', 'stretch', 'not_in_roadmap', 'total');
				break;
		} 
		$result = array();
		
		$fc = new Forecast();
		$fc->retrieve_by_string_fields(array('forecast_type'=>$forecast_type,'user_id'=>$user_id,'timeperiod_id'=>$timeperiod_id));
	
		// divide by 1000 for display
		if($display) {
			IBMHelper::decimals_to_display($fc);
		}
		
		if(!empty($fc->id)) {
			foreach($fields as $field) {
				if(!empty($fc->$field)) {
					$result[$field] = $fc->$field;
				} else {
					$result[$field] = '0';
				}
			}
		} else {
			foreach($fields as $field) {
				$result[$field] = '0';
			}
		}
		return $result;
	}
	// END jvink
	
	// START jvink - get sum of all revenuelineitems which are closed (probability = 100%)
	public static function get_won_column($user_id, $timeperiod_id) {
		
		$res = 0;
		$sql = "SELECT SUM(rli.revenue_amount) AS won
				FROM ibm_revenuelineitems rli
				INNER JOIN ibm_revenuelineitemroadmap rm
					ON rm.revenuelineitem_id_c = rli.id
					AND rm.deleted = 0
					AND rm.forecast_qtr_yr = '{$timeperiod_id}'
					AND rm.probability = '100'
				WHERE rli.deleted = 0
					AND rli.assigned_user_id = '{$user_id}'";
		$q_won = $GLOBALS['db']->query($sql);
		if($won = $GLOBALS['db']->fetchByAssoc($q_won)) {
			$res = $won['won'];
		}
		return $res;
	}
	// END jvink
	
	// $type is 'snd' or 'regular'
	public static function getRoadmapByBrand($user_id, $timeperiod_id){
		$final_return = array();
		
		$user_list = array($user_id) + self::getReportingUsers($user_id, true, false);
		foreach($user_list as $uid){
			$query = "SELECT employee_department FROM users WHERE id = '{$uid}'";
			$row = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query($query));
			$type = 'regular';
			if(empty($row['employee_department'])){
				continue;
			}
			else if($row['employee_department'] == 'SND'){
				$type = 'snd';
			}
			$query =
			"SELECT rli.probability rli_prob, rm.probability rm_prob, rli_cstm.sub_brand_c, rli_prod.name brand_name, rli.revenue_amount, w.solid, w.at_risk, w.stretch \n".
			"FROM worksheet w \n";
			if($type == 'regular'){
				$query .= 
				"INNER JOIN ibm_revenuelineitems rli ON w.related_id = rli.id AND rli.deleted = 0 \n";
			}
			else{
				$query .=
				"INNER JOIN opportunities o ON w.related_id = o.id AND o.deleted = 0 \n".
				"INNER JOIN ibm_revenuepportunities_c rli_join ON rli_join.ibm_revenud375unities_ida = o.id AND rli_join.deleted = 0 \n".
				"INNER JOIN ibm_revenuelineitems rli ON rli_join.ibm_revenu04e3neitems_idb = rli.id AND rli.deleted = 0 \n";
			}
			$query .=
			"INNER JOIN ibm_revenuelineitems_cstm rli_cstm ON rli.id = rli_cstm.id_c \n".
			"INNER JOIN ibm_revenuelineitems_products rli_prod ON rli_cstm.sub_brand_c = rli_prod.id \n".
			"LEFT JOIN ibm_revenuelineitemroadmap rm ON rm.revenuelineitem_id_c = rli.id \n".
            "   AND rm.deleted = 0 \n".
            "WHERE w.user_id = '{$uid}' AND w.timeperiod_id = '{$timeperiod_id}' \n".
			" \n";
			
			$res = $GLOBALS['db']->query($query);
			$final_return[$uid] = array();
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				if($row['rli_prob'] == '100'){
					// per user
					$final_return[$uid][$row['sub_brand_c']]['won'] = empty($final_return[$uid][$row['sub_brand_c']]['won']) ? $row['revenue_amount'] : $final_return[$uid][$row['sub_brand_c']]['won'] + $row['revenue_amount'];
					$final_return[$uid][$row['sub_brand_c']]['brand_name'] = $row['brand_name'];
					// totals
					$final_return['total'][$row['sub_brand_c']]['won'] = empty($final_return['total'][$row['sub_brand_c']]['won']) ? $row['revenue_amount'] : $final_return['total'][$row['sub_brand_c']]['won'] + $row['revenue_amount'];
					$final_return['total'][$row['sub_brand_c']]['brand_name'] = $row['brand_name'];
				}
				else{
					// per user
					$final_return[$uid][$row['sub_brand_c']]['stretch'] = empty($final_return[$uid][$row['sub_brand_c']]['stretch']) ? $row['revenue_amount'] : $final_return[$uid][$row['sub_brand_c']]['stretch'] + $row['stretch'];
					$final_return[$uid][$row['sub_brand_c']]['solid'] = empty($final_return[$uid][$row['sub_brand_c']]['solid']) ? $row['revenue_amount'] : $final_return[$uid][$row['sub_brand_c']]['solid'] + $row['solid'];
					$final_return[$uid][$row['sub_brand_c']]['at_risk'] = empty($final_return[$uid][$row['sub_brand_c']]['at_risk']) ? $row['revenue_amount'] : $final_return[$uid][$row['sub_brand_c']]['at_risk'] + $row['at_risk'];
					$final_return[$uid][$row['sub_brand_c']]['brand_name'] = $row['brand_name'];
					// totals
					$final_return['total'][$row['sub_brand_c']]['stretch'] = empty($final_return['total'][$row['sub_brand_c']]['stretch']) ? $row['revenue_amount'] : $final_return['total'][$row['sub_brand_c']]['stretch'] + $row['stretch'];
					$final_return['total'][$row['sub_brand_c']]['solid'] = empty($final_return['total'][$row['sub_brand_c']]['solid']) ? $row['revenue_amount'] : $final_return['total'][$row['sub_brand_c']]['solid'] + $row['solid'];
					$final_return['total'][$row['sub_brand_c']]['at_risk'] = empty($final_return['total'][$row['sub_brand_c']]['at_risk']) ? $row['revenue_amount'] : $final_return['total'][$row['sub_brand_c']]['at_risk'] + $row['at_risk'];
					$final_return['total'][$row['sub_brand_c']]['brand_name'] = $row['brand_name'];
				}
			}
		}
		
		return $final_return;
	}
}
