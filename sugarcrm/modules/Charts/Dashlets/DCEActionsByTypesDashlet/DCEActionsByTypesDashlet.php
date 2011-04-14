<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: DCEActionsByTypesDashlet.php 24275 2007-07-13 04:26:44Z awu $

require_once('include/Dashlets/DashletGenericChart.php');
require_once('include/generic/LayoutManager.php');


class DCEActionsByTypesDashlet extends DashletGenericChart 
{
    public $abt_date_start = 'TP_this_month';
    
    protected $_seedName = 'DCEActions';
    
    /**
     * @see DashletGenericChart::display()
     */
    public function display() 
    {
        require("modules/Charts/chartdefs.php");
        $chartDef = $chartDefs['dceactions_by_types'];
		
        require_once('include/SugarCharts/SugarChart.php');
		$sugarChart = new SugarChart();
		$sugarChart->is_currency = false;
		$sugarChart->setProperties('', $chartDef['chartUnits'], $chartDef['chartType']);
		$sugarChart->base_url = $chartDef['base_url'];
		$sugarChart->group_by = $chartDef['groupBy'];
		$sugarChart->url_params = array();		
		$sugarChart->setData($this->getDataFromQueries());
	
		$xmlFile = $sugarChart->getXMLFileName($this->id);
		$sugarChart->saveXMLFile($xmlFile, $sugarChart->generateXML());
	
		$returnStr = $sugarChart->display($this->id, $xmlFile, '100%', '480', false);
        return $this->getTitle('<div align="center"></div>') . '<div align="center">' . $returnStr . '</div><br />';
	}
    
    private function getDataFromQueries()
    {
        global $app_list_strings, $db;
        $widgetDef = $this->_searchFields['abt_date_start'];
        $widgetDef['name'] = 'start_date';
        $widgetClass = $this->layoutManager->getClassFromWidgetDef($widgetDef, true);
        $filter = 'queryFilter' . $this->abt_date_start;
        $time_range=$widgetClass->$filter($widgetDef, true);
		$where = $time_range." AND dceactions.deleted=0 AND dceactions.status!='failed'";
		$query = "SELECT type, count(type) as count_type FROM dceactions ";
		//BEGIN SUGARCRM flav=pro ONLY
		$this->getSeedBean()->add_team_security_where_clause($query);
		//END SUGARCRM flav=pro ONLY
		$query .= "WHERE ".$where;
		$query .= " GROUP BY type";
		$result = $db->query($query);
		
        $data_set=array();
        while ($row = $db->fetchByAssoc($result))
            $data_set[$app_list_strings['action_type_list'][$row['type']]][$this->dashletStrings['LBL_SUCCEEDED']]=$row['count_type'];

        foreach($data_set as $k=>$v){
            $where = $time_range." AND dceactions.deleted=0 AND dceactions.type='$k' AND dceactions.status='failed'";
            $query = "SELECT count(status) as count_status FROM dceactions ";
            //BEGIN SUGARCRM flav=pro ONLY
            $this->getSeedBean()->add_team_security_where_clause($query);
            //END SUGARCRM flav=pro ONLY
            $query .= "WHERE ".$where;
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result))
                $data_set[$k][$app_list_strings['action_status_list']['failed']]=$row['count_status'];
        }
        
        return $data_set;
    }
    
    public function saveOptions($req) {
        $options = parent::saveOptions($req);
	    $options['abt_date_start'] =  $_REQUEST['type_abt_date_start'];

        return $options;
    }
}

?>