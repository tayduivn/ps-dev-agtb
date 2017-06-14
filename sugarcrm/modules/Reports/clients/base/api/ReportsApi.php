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
            )
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
        }
        else {
            $table_key = 'self';
            $field_name = $field;
        }
        if (is_array($reportDef['group_defs'])) {
            foreach ($reportDef['group_defs'] as $groupColumn) {
                if ($groupColumn['table_key'] === $table_key && $groupColumn['name'] === $field_name) {
                    return $groupColumn;
                }
            }
        }
        return false;
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
                if ($fieldDef) {
                    $filterRow = array(
                        'adhoc' => true,
                        'name' => $fieldDef['name'],
                        'table_key' => $fieldDef['table_key']
                    );
                    switch ($fieldDef['type']) {
                        case 'enum':
                            $filterRow['qualifier_name'] = 'one_of';
                            $filterRow['input_name0'] = $value;
                            break;
                        case 'datetime':
                            if (empty($fieldDef['qualifier'])) {
                                $filterRow['qualifier_name'] = 'on';
                                $filterRow['input_name0'] = current($value);
                            }
                            else {
                                // TODO: date range
                            }
                            break;
                        // TODO: more types
                        default:
                            $filterRow['qualifier_name'] = 'equals';
                            $filterRow['input_name0'] = current($value);
                            break;
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

        // set target module
        if (!empty($savedReport->module)) {
            $args['module'] = $savedReport->module;
        } else if (!empty($savedReport->content)) {
            $content = json_decode($savedReport->content, true);
            if (!empty($content['module'])) {
                $args['module'] = $content['module'];
            }
        }

        if (!isset($args['module'])) {
            throw new SugarApiExceptionInvalidParameter('Target module not found for report: ' . $savedReport->id);
        }

        $reportDef = json_decode($savedReport->content, true);

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
        $add = true;
        if (!empty($reportDef['display_columns'])) {
            foreach ($reportDef['display_columns'] as $column) {
                if ($column['table_key'] === 'self' && $column['name'] === 'id') {
                    $add = false;
                    break;
                }
            }
        }
        else {
            $reportDef['display_columns'] = array();
        }
        if ($add) {
            $reportDef['display_columns'][] = array (
                'label' => 'Id',
                'name' => 'id',
                'type' => 'id',
                'table_key' => 'self'
            );
        }
        $report = new Report(json_encode($reportDef));
        $results = $report->getData();
        foreach ($results as $record) {
            if (isset($record['primaryid'])) {
                $recordIds[] = $record['primaryid'];
            }
        }
        return $recordIds;
    }
}
