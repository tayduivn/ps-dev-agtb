<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


class ReportsApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'recordListCreate' => array(
                'reqType' => 'POST',
                'path' => array('Reports', '?', 'record_list'),
                'pathVars' => array('', 'record', ''),
                'method' => 'createRecordList',
                'shortHelp' => 'An API to create a record list from a saved report',
                'longHelp' => 'modules/Reports/api/help/module_recordlist_post.html',
            ),
            'getReportRecords' => array(
                'reqType' => 'GET',
                'path' => array('Reports', '?', 'records'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'getReportRecords',
                'jsonParams' => array('group_filters'),
                'shortHelp' => 'An API to deliver filtered records from a saved report',
                'longHelp' => 'modules/Reports/clients/base/api/help/report_records_get_help.html',
                'exceptions' => array(
                    // Thrown in getReportRecord
                    'SugarApiExceptionNotFound',
                    // Thrown in getReportRecords
                    'SugarApiExceptionInvalidParameter',
                ),
            ),
            'getSavedReportChartById' => array(
                'reqType' => 'GET',
                'path' => array('Reports', '?', 'chart'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'getSavedReportChartById',
                'shortHelp' => 'An API to get chart data for a saved report',
                'longHelp' => 'modules/Reports/clients/base/api/help/report_chart_get_help.html',
            ),
        );
    }

    /**
     * Creates a record list from a saved report
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and the records
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiException
     * @return array id, module, records
     */
    public function createRecordList(ServiceBase $api, array $args)
    {
        $savedReport = $this->getReportRecord($api, $args);
        $reportDef = json_decode($savedReport->content, true);
        $recordIds = $this->getRecordIdsFromReport($reportDef);
        $id = RecordListFactory::saveRecordList($recordIds, 'Reports');
        $loadedRecordList = RecordListFactory::getRecordList($id);

        return $loadedRecordList;
    }

    /**
     * Check if we need to retrieve the options attribute for field def
     *
     * @param $type String
     * @return bool true if options needed, false otherwise
     */
    protected function needOptions($type)
    {
        $need = false;
        switch ($type) {
            case 'enum':
            case 'radioenum':
                $need = true;
        }
        return $need;
    }

    /**
     * Gets group field def.
     * @param array $reportDef
     * @param string $field
     * @return array|boolean
     */
    protected function getGroupFilterFieldDef($reportDef, $field)
    {
        $pos = strrpos($field, ':');
        if ($pos !== false) {
            $field_name = substr($field, $pos + 1);
            $table_key = substr($field, 0, $pos);
        } else {
            $table_key = 'self';
            $field_name = $field;
        }
        if (is_array($reportDef['group_defs'])) {
            $report = null;
            foreach ($reportDef['group_defs'] as $groupColumn) {
                if ($groupColumn['table_key'] === $table_key && $groupColumn['name'] === $field_name) {
                    if (empty($groupColumn['type']) || $this->needOptions($groupColumn['type'])) {
                        if (!$report) {
                            $report = new Report($reportDef);
                        }
                        if (!empty($report->full_bean_list[$table_key])) {
                            $bean = $report->full_bean_list[$table_key];
                            $fieldDef = $bean->getFieldDefinition($field_name);
                            if (!empty($fieldDef['type']) && empty($groupColumn['type'])) {
                                $groupColumn['type'] = $fieldDef['type'];
                            }
                            if ($this->needOptions($groupColumn['type'])) {
                                $groupColumn['options_array'] = getOptionsFromVardef($fieldDef);
                            }
                        }
                    }
                    return $groupColumn;
                }
            }
        }
        return false;
    }

    /**
     * Given a value, reverse look up the associated list key
     *
     * @param $value String list value
     * @param $fieldDef Array field definition
     * @return mixed list key
     */
    protected function getOptionKeyFromValue($value, $fieldDef)
    {
        if (empty($value)) {
            return $value;
        }
        $key = false;
        if (!empty($fieldDef['options_array'])) {
            $key = array_search($value, $fieldDef['options_array']);
        }
        if ($key === false) {
            $errMsg = 'Failed to reverse lookup for ' . $value . ' in ' . print_r($fieldDef['options_array'], true);
            throw new SugarApiExceptionInvalidParameter($errMsg);
        } else {
            return $key;
        }
    }

    /**
     * Wrapper for global function return_app_list_strings_language
     *
     * @return Array
     */
    protected function getAppListStrings()
    {
        global $current_language;
        return return_app_list_strings_language($current_language);
    }

    /**
     * Wrapper for global function return_application_language
     *
     * @return Array
     */
    protected function getAppStrings()
    {
        global $current_language;
        return return_application_language($current_language);
    }

    /**
     * This function massages the input filter value and converts it to be report-compatible.
     *
     * @param $type String field type
     * @param $value Array original filter value
     * @param $fieldDef Array field definition
     * @return array new filter value
     */
    protected function massageFilterValue($type, $value, $fieldDef)
    {
        $val = $value;
        if (is_array($value)) {
            $val = reset($value);
        }

        // if Undefined, set to empty string and use "Is Empty" filter
        $app_strings = $this->getAppStrings();
        if ($val == $app_strings['LBL_CHART_UNDEFINED']) {
            $val = '';
        }

        switch ($type) {
            case 'bool':
                $app_list_strings = $this->getAppListStrings();
                if (!empty($app_list_strings['dom_switch_bool'])) {
                    if ($val == $app_list_strings['dom_switch_bool']['on']) {
                        $val = '1';
                    } elseif ($val == $app_list_strings['dom_switch_bool']['off']) {
                        $val = '0';
                    }
                }
                break;
            case 'enum':
            case 'radioenum':
                $val = $this->getOptionKeyFromValue($val, $fieldDef);
                break;
        }
        return array($val);
    }

    /**
     * Adds group filters to report def
     * @param Array $reportDef
     * @param Array $groupFilters
     * @return Array
     */
    protected function addGroupFilters($reportDef, $groupFilters)
    {
        if (!is_array($groupFilters)) {
            return $reportDef;
        }

        // Construct a Report module filter from group filters
        $adhocFilter = array();
        foreach ($groupFilters as $filter) {
            foreach ($filter as $field => $value) {
                if (is_string($value)) {
                    $value = array($value);
                }
                $fieldDef = $this->getGroupFilterFieldDef($reportDef, $field);
                if (!empty($fieldDef['type'])) {
                    $value = $this->massageFilterValue($fieldDef['type'], $value, $fieldDef);
                }
                if ($fieldDef && !empty($fieldDef['type'])) {
                    $filterRow = array(
                        'adhoc' => true,
                        'name' => $fieldDef['name'],
                        'table_key' => $fieldDef['table_key'],
                    );
                    switch ($fieldDef['type']) {
                        case 'enum':
                            $filterRow['qualifier_name'] = 'one_of';
                            $filterRow['input_name0'] = $value;
                            break;
                        case 'date':
                        case 'datetime':
                        case 'datetimecombo':
                            if (empty($fieldDef['qualifier'])) {
                                $filterRow['qualifier_name'] = 'on';
                                $filterRow['input_name0'] = reset($value);
                            } else {
                                // TODO: date range
                            }
                            break;
                        // TODO: more types
                        case 'radioenum':
                        case 'id':
                            $filterRow['qualifier_name'] = 'is';
                            $filterRow['input_name0'] = reset($value);
                            break;
                        default:
                            $filterRow['qualifier_name'] = 'equals';
                            $filterRow['input_name0'] = reset($value);
                            break;
                    }
                    // special case when the input value is empty string
                    // create a filter simiar to the "Is Empty" filter
                    if (strlen(reset($value)) == 0) {
                        $filterRow['qualifier_name'] = 'empty';
                        $filterRow['input_name0'] = 'empty';
                        $filterRow['input_name1'] = 'on';
                    }
                    array_push($adhocFilter, $filterRow);
                }
            }
        }

        $adhocFilter['operator'] = 'AND';

        // Make sure Filter_1 is defined
        if (empty($reportDef['filters_def']) || !isset($reportDef['filters_def']['Filter_1'])) {
            $reportDef['filters_def']['Filter_1'] = array();
        }
        $savedReportFilter = $reportDef['filters_def']['Filter_1'];

        // For the conditions [] || {"Filter_1":{"operator":"AND"}}
        if (empty($savedReportFilter) ||
            (sizeof($savedReportFilter) == 1 && isset($savedReportFilter['operator']))
        ) {
            // Just set Filter_1 to adhocFilter
            $newFilter = $adhocFilter;
        } else {
            // Concatenate existing and adhocFilter
            $newFilter = array();
            array_push($newFilter, $savedReportFilter);
            array_push($newFilter, $adhocFilter);
            $newFilter['operator'] = 'AND';
        }

        $reportDef['filters_def']['Filter_1'] = $newFilter;
        return $reportDef;
    }

    /**
     * Retrieves saved runtime filters.
     * @param SugarBean $savedReport
     * @return Array
     */
    protected function getSavedRuntimeFilters($savedReport)
    {
        // TODO
        return array();
    }

    /**
     * Adds runtime filters to report def
     * @param Array $reportDef
     * @param Array $runtimeFilters
     * @return Array
     */
    protected function addSavedRuntimeFilters($reportDef, $runtimeFilters)
    {
        // TODO
        return $reportDef;
    }

    /**
     * Returns the records associated with a saved report
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and the record
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiExceptionInvalidParameter
     * @throws SugarApiException
     * @return array records
     */
    public function getReportRecords($api, $args)
    {
        $savedReport = $this->getReportRecord($api, $args);

        unset($args['record']);
        unset($args['module']);

        $reportDef = json_decode($savedReport->content, true);

        // set target module
        if (!empty($reportDef['module'])) {
            $args['module'] = $reportDef['module'];
        }

        if (!isset($args['module'])) {
            throw new SugarApiExceptionInvalidParameter('Target module not found for report: ' . $savedReport->id);
        }

        if (isset($args['group_filters'])) {
            $reportDef = $this->addGroupFilters($reportDef, $args['group_filters']);
            unset($args['group_filters']);
        }

        if (isset($args['use_saved_filters'])) {
            if ($args['use_saved_filters'] === 'true') {
                $runtimeFilters = $this->getSavedRuntimeFilters($savedReport);
                $reportDef = $this->addSavedRuntimeFilters($reportDef, $runtimeFilters);
            }
            unset($args['saved_runtime_filters']);
        }

        $recordIds = $this->getRecordIdsFromReport($reportDef);

        if (!empty($recordIds)) {
            if (!isset($args['filter'])) {
                $args['filter'] = array();
            }
            $args['filter'][] = array('id' => array('$in' => $recordIds));
        }

        $filterApi = new FilterApi();
        return $filterApi->filterList($api, $args);
    }

    /**
     * Retrieves a saved report and chart data, given a report ID in the args
     *
     * @param $api ServiceBase The API class of the request
     * @param $args array The arguments array passed in from the API
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiException
     * @return array
     */
    public function getSavedReportChartById($api, $args)
    {
        $chartReport = $this->getReportRecord($api, $args);

        $returnData = array();

        $reporter = new Report($chartReport->content);
        $reporter->saved_report_id = $chartReport->id;

        if ($reporter && !$reporter->has_summary_columns()) {
            return '';
        }

        // build report data since it isn't a SugarBean
        $reportData = array();
        $reportData['label'] = $reporter->name; // also report_def.report_name
        $reportData['id'] = $reporter->saved_report_id;
        $reportData['summary_columns'] = $reporter->report_def['summary_columns'];
        $reportData['group_defs'] = $reporter->report_def['group_defs'];
        $reportData['filters_def'] = $reporter->report_def['filters_def'];
        $reportData['base_module'] = $reporter->report_def['module'];

        // add reportData to returnData
        $returnData['reportData'] = $reportData;

        $chartDisplay = new ChartDisplay();
        $chartDisplay->setReporter($reporter);

        $chart = $chartDisplay->getSugarChart();

        $json = json_decode($chart->buildJson($chart->generateXML(), true), true);

        $returnData['chartData'] = $json;

        return $returnData;
    }

    /**
     * Returns a report record
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing a record id
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiException
     * @return SugarBean record
     */
    protected function getReportRecord($api, $args)
    {
        $this->requireArgs($args, array('record'));

        $savedReport = BeanFactory::getBean('Reports', $args['record']);

        if (empty($savedReport) || !$savedReport->ACLAccess('access')) {
            throw new SugarApiExceptionNotFound('Report not found: ' . $args['record']);
        }

        return $savedReport;
    }

    /**
     * Returns the record ids of a saved report
     * @param array $reportDef
     * @return array Array of record ids
     */
    protected function getRecordIdsFromReport($reportDef)
    {
        $recordIds = array();
        // add field 'id' to display_columns so it will be added to 'select' and returned
        $reportDef['display_columns'] = array(
            array (
                'label' => 'Id',
                'name' => 'id',
                'type' => 'id',
                'table_key' => 'self',
            ),
        );
        $report = new Report($reportDef);
        $results = $report->getData();
        foreach ($results as $record) {
            if (isset($record['primaryid'])) {
                $recordIds[] = $record['primaryid'];
            }
        }
        return $recordIds;
    }
}
