<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('clients/base/api/FilterApi.php');
require_once('include/MetaDataManager/MetaDataManager.php');

class DuplicateCheckApi extends FilterApi
{
    const DUPE_CHECK_RANK = 'duplicate_check_rank';
    const FIELD_PLACEHOLDER = '||FIELD_DATA||';

    public function registerApiRest()
    {
        return array(
            'duplicateCheck' => array(
                'reqType' => 'POST',
                'path' => array('<module>','duplicateCheck'),
                'pathVars' => array('module',''),
                'method' => 'checkForDuplicates',
                'jsonParams' => array('field_data'),
                'shortHelp' => 'Check for duplicate records within a module',
                'longHelp' => 'include/api/help/duplicateCheck.html',
            ),
        );
    }

    /**
     * Using the field data passed in combined with the filter & ranking metadata in vardef
     * Search for duplicates in the system
     *
     * @param ServiceBase $api
     * @param array $args
     */
    function checkForDuplicates(ServiceBase $api, array $args)
    {
        //build filter to hand off to the FilterApi
        $dupeCheckMetadata = $this->retrieveDupeCheckMetadata($args['module']);
        $dupeCheckFilterTemplate = $dupeCheckMetadata['filter_template'];
        $args['filter'] = $this->buildDupeCheckFilter($dupeCheckFilterTemplate, $args['field_data']);

        //invoke the FilterApi
        $duplicates = $this->filterList($api, $args);

        //rank the duplicates found
        $rankingFields = $dupeCheckMetadata['ranking_fields'];
        $duplicates = $this->rankAndSortDuplicates($duplicates, $rankingFields, $args['field_data']);

        return $duplicates;
    }

    /**
     * Retrieve the filter and ranking metadata from the vardef
     *
     * @param $moduleName
     * @return array
     */
    function retrieveDupeCheckMetadata($moduleName)
    {
        global $current_user;
        $mm = new MetaDataManager($current_user);
        $varDef = $mm->getVarDef($moduleName);
        return $varDef['duplicate_check'];
    }

    /**
     * Build the filter array to hand off the the Filter API
     * Based on the filter template in the vardef
     *
     * @param array $dupeCheckFilterTemplate
     * @param array $fieldData
     * @return array
     */
    function buildDupeCheckFilter($dupeCheckFilterTemplate, $fieldData)
    {
        foreach ($dupeCheckFilterTemplate as &$filterDef) {
            foreach ($filterDef as $field => &$filter) {
                if ($field == '$or' || $field == '$and') {
                    $filter = $this->buildDupeCheckFilter($filter,$fieldData);
                } else {
                    foreach ($filter as $op => &$value) {
                        $inField = $this->getIncomingFieldFromPlaceholder($value);
                        if ($inField !== false) {
                            if (isset($fieldData[$inField]) && !empty($fieldData[$inField])) {
                                $value = $fieldData[$inField];
                            } else {
                                unset($filterDef[$inField]);
                            }
                        }
                    }
                }
            }
        }
        return $dupeCheckFilterTemplate;
    }

    /**
     * If filter value starts with the field placeholder, returns the name of the incoming field
     * otherwise, returns false
     *
     * @param $filterValue
     * @return bool|mixed
     */
    protected function getIncomingFieldFromPlaceholder($filterValue)
    {
        if (strpos($filterValue, self::FIELD_PLACEHOLDER) === 0) {
            return str_replace(self::FIELD_PLACEHOLDER, '', $filterValue);
        }
        return false;
    }

    /**
     * Rank the duplicates returned from the Filter API based on the ranking field metadata from the vardef
     *
     * @param array $duplicates
     * @param array $rankingFields
     * @param array $fieldData
     * @return array
     */
    function rankAndSortDuplicates($duplicates, $rankingFields, $fieldData)
    {
        //calculate rank of each duplicate based on rank field metadata
        $startingFieldWeight = count($rankingFields);
        foreach ($duplicates as &$duplicate) {
            $rank = 0;
            $fieldWeight = $startingFieldWeight;
            foreach ($rankingFields as $rankingField) {
                $inFieldName = $rankingField['in_field_name'];
                $dupeFieldName = $rankingField['dupe_field_name'];
                //if ranking field is on the dupe and on the field data passed to the api...
                if (isset($fieldData[$inFieldName]) && isset($duplicate[$dupeFieldName])) {
                    $rank += $this->calculateFieldMatchQuality($fieldData[$inFieldName], $duplicate[$dupeFieldName], $fieldWeight);
                }
                $fieldWeight--;
            }
            $duplicate[self::DUPE_CHECK_RANK] = $rank;
        }

        //sort the duplicates based on rank
        usort($duplicates, array($this, 'compareDuplicateRanks'));

        return $duplicates;
    }

    /**
     * Calculates quality of a field match
     *
     * @param $incomingFieldValue
     * @param $dupeFieldValue
     * @param $fieldWeight
     * @return int|number
     */
    protected function calculateFieldMatchQuality($incomingFieldValue, $dupeFieldValue, $fieldWeight)
    {
        $incomingFieldValue = trim($incomingFieldValue);
        $dupeFieldValue = trim($dupeFieldValue);
        if ($incomingFieldValue === $dupeFieldValue) {
            return pow(2, $fieldWeight);
        }
        return 0;
    }

    /**
     * Compare function for use in sorting the duplicates
     *
     * @param array $dupe1
     * @param array $dupe2
     * @return int
     */
    protected function compareDuplicateRanks($dupe1, $dupe2)
    {
        $dupe1Rank = $dupe1[self::DUPE_CHECK_RANK];
        $dupe2Rank = $dupe2[self::DUPE_CHECK_RANK];
        if ($dupe1Rank == $dupe2Rank) {
            return 0;
        }
        return ($dupe1Rank < $dupe2Rank) ? 1 : -1;
    }
}
