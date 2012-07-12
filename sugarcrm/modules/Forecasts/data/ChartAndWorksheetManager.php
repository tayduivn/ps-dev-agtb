<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/**
 *
 */
class ChartAndWorksheetManager
{
    /**
     * @var array
     */
    protected $dataMapping = array();

    /**
     * @var array
     */
    protected $dataInstances = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        //first check if a custom file exists
        //there should be an Array named $chartAndWorksheetMapping if you decide on this course
        if (file_exists('custom/modules/Forecasts/data/config.php')) {
            $chartAndWorksheetMapping = array();
            require('custom/modules/Forecasts/data/config.php');
            $this->dataMapping = $chartAndWorksheetMapping;
        } else {
            //Default settings
            $this->dataMapping = array(
                'manager' => 'modules/Forecasts/data/manager/Manager.php',
                'individual' => 'modules/Forecasts/data/individual/Individual.php',
            );
        }
    }

    /**
     * @param $type
     * @param string $id
     * @return mixed
     */
    public function getChartDefinition($type, $id = '')
    {
        try {
            $instance = $this->getDataInstance($type);
            return $instance->getChartDefinition($id);
        } catch (Exception $ex) {
            return null;
        }
    }


    /**
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function getWorksheetDefinition($type, $id = '')
    {
        try {
            $instance = $this->getDataInstance($type);
            return $instance->getWorksheetDefinition($id);
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @param string $type
     * @param array $args
     * @return null
     */
    public function getWorksheetFilters($type, $args = array())
    {
        try {
            $instance = $this->getDataInstance($type);
            return $instance->getFilters($args);
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @param string $type
     * @param Report $report
     * @return null
     */
    public function getWorksheetGridData($type, $report)
    {
        try {
            $instance = $this->getDataInstance($type);
            return $instance->getGridData($report);
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     *
     * @param $type String value of the data type to retrieve (ex: 'manager', 'individual')
     * @return IChartAndWorksheet Interface instance
     *
     * @throws Exception
     */
    protected function getDataInstance($type)
    {
        if (isset($this->dataInstances[$type])) {
            return $this->dataInstances[$type];
        }

        if (!isset($this->dataMapping[$type])) {
            throw new Exception("Undefined type: [{$type}] in data mapping.");
        }

        $klass = ucfirst($type);

        require_once($this->dataMapping[$type]);
        $this->dataInstances[$type] = new $klass();
        return $this->dataInstances[$type];
    }

}
