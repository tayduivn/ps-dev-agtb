<?php
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

// This class is used for the Manager Views
require_once('include/SugarForecasting/AbstractForecast.php');
require_once('include/SugarForecasting/Exception.php');
class SugarForecasting_Manager extends SugarForecasting_AbstractForecast implements SugarForecasting_ForecastSaveInterface
{

    /**
     * Class Constructor
     *
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        // set the isManager Flag just incase we need it
        $this->isManager = true;

        parent::__construct($args);

        // set the default data timeperiod to the set timeperiod
        $this->defaultData['timeperiod_id'] = $this->getArg('timeperiod_id');
    }

    /**
     * Run all the tasks we need to process get the data back
     *
     * @deprecated @see ForecastManagerWorksheetsFilterApi
     * @return array
     */
    public function process()
    {
        return array();
    }

    /**
     * Save the Manager Worksheet
     *
     * @return string
     * @throws SugarApiExceptionNotAuthorized
     */
    public function save()
    {
        require_once('include/SugarFields/SugarFieldHandler.php');
        $id = $this->getArg('id');
        if (empty($id)) {
            $id = null;
        }
        /* @var $seed ForecastManagerWorksheet */
        $seed = BeanFactory::getBean('ForecastManagerWorksheets', $id);
        // team name comes in as an array and blows up the cleaner, just ignore it for the worksheet as we don't want
        // it set from there right now, it's always controlled by the parent row.
        unset($this->args['team_name']);
        //$seed->loadFromRow($this->getArgs());
        $seed->setWorksheetArgs($this->getArgs());
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties) {
            $fieldName = $properties['name'];

            if (!isset($args[$fieldName])) {
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName, 'save')) {
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field ' . $fieldName . ' in module: ' . $args['module']);
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if ($field != null) {
                $field->save($seed, $args, $fieldName, $properties);
            }
        }

        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        if (!isset($settings['has_commits']) || !$settings['has_commits']) {
            $admin->saveSetting('Forecasts', 'has_commits', true, 'base');
            MetaDataManager::clearAPICache();
        }

        $seed->save();
    }
}
