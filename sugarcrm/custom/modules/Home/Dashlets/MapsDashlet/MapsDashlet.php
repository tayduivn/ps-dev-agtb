<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 * 
 * $Id: MapsDashlet.php,v 1.1 2006/10/11 00:53:31 clint Exp $
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');

class MapsDashlet extends Dashlet {
    var $height = '300'; // height of the pad
    var $displayOnStartup = true; // height of the pad

    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function MapsDashlet($id, $def) {
        $this->loadLanguage('MapsDashlet', 'custom/modules/Home/Dashlets/'); // load the language strings here
            
        if(!empty($def['height'])) // set a default height if none is set
            $this->height = $def['height'];
       if(!empty($def['displayOnStartup'])) // set a default height if none is set
            $this->displayOnStartup = $def['displayOnStartup'];
        else
             $this->displayOnStartup = false;
        parent::Dashlet($id); // call parent constructor
         
        $this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = true;  // dashlet has javascript attached to it
                
        // if no custom title, use default
        if(empty($def['title'])) $this->title = $this->dashletStrings['LBL_TITLE'];
        else $this->title = $def['title'];        
    }

    /**
     * Displays the dashlet
     * 
     * @return string html to display dashlet
     */
    function display() {
     
        $ss = new Sugar_Smarty();
        $ss->assign('saving', $this->dashletStrings['LBL_SAVING']);
        $ss->assign('saved', $this->dashletStrings['LBL_SAVED']);
        $ss->assign('id', $this->id);
        $ss->assign('height', $this->height);
        $ss->assign('nameLbl', $this->dashletStrings['LBL_NAME']);
        $ss->assign('zipLbl', $this->dashletStrings['LBL_ZIP']);
        $ss->assign('radiusLbl', $this->dashletStrings['LBL_RADIUS']);
        $ss->assign('displayOnStartup', $this->displayOnStartup);
        
        $str = $ss->fetch('custom/modules/Home/Dashlets/MapsDashlet/MapsDashlet.tpl');     
        return parent::display($this->dashletStrings['LBL_DBLCLICK_HELP']) . $str; // return parent::display for title and such
    }
    
    /**
     * Displays the javascript for the dashlet
     * 
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
         global $app_strings, $sugar_version, $sugar_config;
           require_once('include/json_config.php');
        $json_config = new json_config();
        $json = getJSONobj();
        
        $ss = new Sugar_Smarty();
        $ss->assign('saving', $this->dashletStrings['LBL_SAVING']);
        $ss->assign('saved', $this->dashletStrings['LBL_SAVED']);
        $ss->assign('id', $this->id);
        
        $input_name = 'maps_input_'.$this->id;
        $input_id = 'maps_input_id_'.$this->id;
        $input_address1 = 'maps_input_primary_address_street_'.$this->id;
        $input_address_city = 'maps_input_primary_address_city_'.$this->id;
        $input_address_state = 'maps_input_primary_address_state_'.$this->id;
        $input_address_postalcode = 'maps_input_primary_address_postalcode_'.$this->id;
        $input_address_country = 'maps_input_primary_address_country_'.$this->id;
        $input_phone_work = 'maps_input_phone_work_'.$this->id;
         
        $popup_request_data = array ('call_back_function' => 'set_return', 'form_name' => 'EditView', 'field_to_name_array' => array ('id' => $input_id, 'name' => $input_name,'primary_address_street' => $input_address1, 'primary_address_city' => $input_address_city, 'primary_address_state' => $input_address_state, 'primary_address_postalcode' => $input_address_postalcode, 'primary_address_country' => $input_address_country, 'phone_work' => $input_phone_work),);
        // must urlencode to put into the filter request string
        // because IE gets an out of memory error when it is passed
        // as the usual object literal
        $encoded_popup_request_data = urlencode($json->encode($popup_request_data));
        $ss->assign('encoded_popup_request_data_contacts', $encoded_popup_request_data);
        
        $popup_request_data = array ('call_back_function' => 'set_return', 'form_name' => 'EditView', 'field_to_name_array' => array ('id' => $input_id, 'name' => $input_name,'billing_address_street' => $input_address1, 'billing_address_city' => $input_address_city, 'billing_address_state' => $input_address_state, 'billing_address_postalcode' => $input_address_postalcode, 'billing_address_country' => $input_address_country, 'phone_office' => $input_phone_work),);
        // must urlencode to put into the filter request string
        // because IE gets an out of memory error when it is passed
        // as the usual object literal
        $encoded_popup_request_data = urlencode($json->encode($popup_request_data));
        $ss->assign('encoded_popup_request_data_other', $encoded_popup_request_data);
        
        $ss->assign('sugar_version', $sugar_version);
        $ss->assign('js_custom_version', $sugar_config['js_custom_version']);
                $ss->assign('site_url', $sugar_config['site_url']);
        
        $str = $ss->fetch('custom/modules/Home/Dashlets/MapsDashlet/MapsDashletScript.tpl');     
        return $str; // return parent::display for title and such
    }
        
    /**
     * Displays the configuration form for the dashlet
     * 
     * @return string html to display form
     */
    function displayOptions() {
        global $app_strings;
        
        $ss = new Sugar_Smarty();
        $ss->assign('titleLbl', $this->dashletStrings['LBL_CONFIGURE_TITLE']);
        $ss->assign('saveLbl', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('displayOnStartupLbl', $this->dashletStrings['LBL_DISPLAY_ON_STARTUP']);
        $ss->assign('title', $this->title);
        $ss->assign('id', $this->id);
        $ss->assign('display_on_startup', $this->displayOnStartup);
        

        return parent::displayOptions() . $ss->fetch('custom/modules/Home/Dashlets/MapsDashlet/MapsDashletOptions.tpl');
    }  

    /**
     * called to filter out $_REQUEST object when the user submits the configure dropdown
     * 
     * @param array $req $_REQUEST
     * @return array filtered options to save
     */  
    function saveOptions($req) {
        global $sugar_config, $timedate, $current_user, $theme;
        $options = array();
        $options['title'] = $_REQUEST['title'];
         if(!empty($_REQUEST['display_on_startup'])) {
            $options['displayOnStartup'] = $_REQUEST['display_on_startup'];
        }
        else {
           $options['displayOnStartup'] = false;
        }

        return $options;
    }

    /**
     * Used to save text on textarea blur. Accessed via Home/CallMethodDashlet.php
     * This is an example of how to to call a custom method via ajax
     */    
    function getClosest() {
        $zip = "";
        $distance = 0;
        $type = "Account";
       
        if(isset($_REQUEST['zip'])) {
            $optionsArray = $this->loadOptions();
            $zip = nl2br($_REQUEST['zip']);
            $distance = nl2br($_REQUEST['distance']);
            $this->storeOptions($optionsArray);
            //get result
            
        }
        
        $json = getJSONobj();
        $closest_zips = $this->getZipCodesByDistance($zip, $distance);
        $output_zips = $this->getRecordsByZip($closest_zips, $type);
        echo 'result = ' . $json->encode(array('id' => $_REQUEST['id'], 
                                       'records_found' => $output_zips,
                                       'center_zip' => $zip));
    }
    
    function getZipCodesByDistance($zip, $distance){
           $req = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><ListInRange xmlns="http://www.tilisoft.com/ws/LocInfo/literalTypes"><ZipCode>'.$zip.'</ZipCode><Miles>'.$distance.'</Miles></ListInRange></soap:Body></soap:Envelope>';
            $header[] = "Content-Type: text/xml";
            $header[] = "SOAPAction: http://www.tilisoft.com/ws/LocInfo/ListInRange";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://www.tilisoft.com/ws/LocInfo/ZipCode.asmx');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);               
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            
            $data = curl_exec($ch);   
            require_once('include/domit/xml_domit_parser.php');
            require_once('include/nusoap/nusoap.php');
            $xmldoc =& new DOMIT_Document();
            $xmldoc->parseXML($data, true);
            $arr = $xmldoc->toArray();
            $index = 0;
            $closest_zips = array();
            while(isset($arr['#document'][1]['soap:Envelope'][0]['soap:Body'][0]['ListInRangeResponse'][0]['ListInRangeResult'][1]['diffgr:diffgram'][0]['ZipCodeData'][$index])){
               $closest_zips[] = $arr['#document'][1]['soap:Envelope'][0]['soap:Body'][0]['ListInRangeResponse'][0]['ListInRangeResult'][1]['diffgr:diffgram'][0]['ZipCodeData'][$index]['ZipCodeInfo'][0]['ZIPCODE'][0];
                $index++;
            }
            return $closest_zips;
    }
    
    function getRecordsByZip($closest_zips, $class_name){
        global  $beanList, $beanFiles;
        require_once($beanFiles[$class_name]);
        $seed = new $class_name();
        /*$zip_string = "";
        $index = 1;
        foreach($closest_zips as $key => $value){
         $zip_string .= $key;
         if($index < count($closest_zips)){
            $zip_string .= ',';  
         }
         $index ++;
        }*/
        $query = "billing_address_postalcode IN (".implode(",", $closest_zips).")";
        $response = $seed->get_list('', $query);
        $list = $response['list'];
        $all_zips = array();
        foreach($list as $record){
            if(!empty($record->billing_address_postalcode)){
                $all_zips[] = array('name' => $record->name, 'address_street' => $record->billing_address_street, 'address_city' => $record->billing_address_city, 'address_state' => $record->billing_address_state, 'address_postalcode' => $record->billing_address_postalcode, 'address_country' => $record->billing_address_country, 'phone_office' => $record->phone_office);  
            } 
        }
        return $all_zips;
    }
}

?>