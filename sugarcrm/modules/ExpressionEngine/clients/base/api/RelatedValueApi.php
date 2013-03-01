<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 ********************************************************************************/

require_once('include/api/SugarApi.php');
require_once('modules/ExpressionEngine/formulaHelper.php');

class RelatedValueApi extends SugarApi
{
    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
    {
        $parentApi = array(
            'related_value' => array(
                'reqType' => 'GET',
                'path' => array('ExpressionEngine', '?', 'related'),
                'pathVars' => array('', 'record', ''),
                'method' => 'getRelatedValues',
                'shortHelp' => 'Retrieve the Chart data for the given data in the Forecast Module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastChartApi.html',
            ),
        );
        return $parentApi;
    }

     /**
     * Used by the dependency manager to pre-load all the related fields required
     * to load an entire view.
     */
    public function getRelatedValues($api, $args){
        if (empty($args['module']) || empty($args['fields']))
            return;
        $fields = json_decode(html_entity_decode($args['fields']), true);
        $focus = $this->loadBean($api, $args);
        $ret = array();
        foreach($fields as $rfDef)
        {
            $link = $rfDef['link'];
            $type = $rfDef['type'];
            if (!isset($ret[$link]))
                $ret[$link] = array();
            if (empty($ret[$link][$type]))
                $ret[$link][$type] = array();

            switch($type){
                //The Related function is used for pulling a sing field from a related record
                case "related":
                    //Default it to a blank value
                    $ret[$link]['related'][$rfDef['relate']] = "";

                    //If we have neither a focus id nor a related record id, we can't retrieve anything
                    if (!empty($id) || !empty($rfDef['relId']))
                    {
                        $relBean = null;
                        if (empty($rfDef['relId']) || empty($rfDef['relModule']))
                        {
                            //If the relationship is invalid, just move onto another field
                            if (!$focus->load_relationship($link))
                                break;
                            $beans = $focus->$link->getBeans(array("enforce_teams" => true));
                            //No related beans means no value
                            if (empty($beans))
                                break;
                            //Grab the first bean on the list
                            reset($beans);
                            $relBean = current($beans);
                        } else
                        {
                            $relBean = BeanFactory::getBean($rfDef['relModule'], $rfDef['relId']);
                        }
                        //If we found a bean and the current user has access to the related field, grab a value from it
                        if (!empty($relBean) && ACLField::hasAccess($rfDef['relate'], $relBean->module_dir, $GLOBALS['current_user']->id, true))
                        {
                            $validFields = FormulaHelper::cleanFields($relBean->field_defs, false, true, true);
                            if (isset($validFields[$rfDef['relate']]))
                            {
                                $ret[$link]['relId'] = $relBean->id;
                                $ret[$link]['related'][$rfDef['relate']] =
                                    FormulaHelper::getFieldValue($relBean, $rfDef['relate']);
                            }
                        }
                    }
                    break;
                case "count":
                    if(!empty($id) && $focus->load_relationship($link))
                    {
                        $ret[$link][$type] = count($focus->$link->get());
                    } else
                    {
                        $ret[$link][$type] = 0;
                    }
                    break;
                case "rollupSum":
                case "rollupAve":
                case "rollupMin":
                case "rollupMax":
                //If we are going to calculate one rollup, calculate all the rollups since there is so little cost
                $rField = $rfDef['relate'];
                if($focus->load_relationship($link))
                {
                    $relBeans = $focus->$link->getBeans(array("enforce_teams" => true));
                    $sum = 0;
                    $count = 0;
                    $min = false;
                    $max = false;
                    if (!empty($relBeans))
                    {
                        //Check if the related record vardef has banned this field from formulas
                        $relBean = reset($relBeans);
                        $validFields = FormulaHelper::cleanFields($relBean->field_defs, false, true, true);
                        if (!isset($validFields[$rField])) {
                            break;
                        }
                    }
                    foreach($relBeans as $bean)
                    {
                        if (isset($bean->$rField) && is_numeric($bean->$rField) &&
                            //ensure the user can access the fields we are using.
                            ACLField::hasAccess($rField, $bean->module_dir, $GLOBALS['current_user']->id, true)
                        ) {
                            $count++;
                            $sum += floatval($bean->$rField);
                            if ($min === false || $bean->$rField < $min)
                                $min = floatval($bean->$rField);
                            if ($max === false || $bean->$rField > $max)
                                $max = floatval($bean->$rField);
                        }
                    }
                    if ($type == "rollupSum")
                        $ret[$link][$type][$rField] = $sum;
                    if ($type == "rollupAve")
                        $ret[$link][$type][$rField] = $count == 0 ? 0 : $sum / $count;
                    if ($type == "rollupMin")
                        $ret[$link][$type][$rField] = $min;
                    if ($type == "rollupMax")
                        $ret[$link][$type][$rField] = $max;
                } else
                {
                    $ret[$link][$type][$rField] = 0;
                }
                break;
            }
        }
        return $ret;
    }
}
