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
                'shortHelp' => 'An API to deliver filtered records from a saved report',
                'longHelp' => 'modules/Reports/clients/base/api/help/report_records_get_help.html',
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
        $recordIds = $this->getRecordIdsFromReport($savedReport);
        $id = RecordListFactory::saveRecordList($recordIds, 'Reports');
        $loadedRecordList = RecordListFactory::getRecordList($id);

        return $loadedRecordList;
    }

    private function translateAdhocFilterOperator($opp)
    {
        switch ($opp) {
            case '$in':
                return 'one_of';
                break;
                // case '$not_in':
                //     return 'not_one_of';
                //     break;
                // case '$not_equals':
                //     return 'not_equals_str';
                //     break;
                // case '$starts':
                //     return 'starts_with';
                //     break;
            case '$on':
                return 'on';
                break;
            case '$is':
                return 'is';
                break;
            case '$dateBetween':
                return 'between_dates';
                break;
            case '$gt':
                return 'after';
                break;
            case '$lt':
                return 'before';
                break;
            default:
                return 'equals';
                break;
        }
    }

    /**
     * Returns the records associated with a saved report
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and the records
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiException
     * @return array records
     */
    public function getReportRecords($api, $args)
    {
        $savedReport = $this->getReportRecord($api, $args);

        $data = array();
        $records = array();

        if (isset($args['filter'])) {
            $clickFilter = $args['filter'];
            $json = getJSONobj();
            $tmpContent = $json->decode($savedReport->content, false);

            //[{"industry":{"$in":["Chemicals"]}},{"account_type":{"$in":["Customer"]}}]

            // Construct a Report module filter from user filterDef
            $adhocFilter = array();
            foreach ($clickFilter as $filter) {
                foreach ($filter as $field => $def) {
                    //$field: "industry"
                    //$def: {"$in":["Chemicals"]}

                    //TODO: replace with array get last
                    foreach ($def as $operator => $values) {
                        //$operator: $in
                        //$values: ["Chemicals"]
                        $input = $values;
                        $qualifier_name = $this->translateAdhocFilterOperator($operator);
                    }
                    $filterRow = array(
                        'adhoc' => true,
                        'name' => $field,
                        'table_key' => 'self',
                        'qualifier_name' => $qualifier_name,
                    );

                    if ($qualifier_name === 'one_of' || $qualifier_name === 'is') {
                        $filterRow['input_name0'] = $input;
                    } elseif ($qualifier_name === 'between_dates') {
                        $filterRow['input_name0'] = current($input);
                        $filterRow['input_name1'] = end($input);
                    } elseif ($qualifier_name === 'on') {
                        $filterRow['input_name0'] = current($input);
                        $filterRow['input_name1'] = 'on';
                    }
                    array_push($adhocFilter, $filterRow);
                }
            }

            $adhocFilter['operator'] = 'AND';

            // Make sure Filter_1 is defined
            if (empty($tmpContent['filters_def']) || !isset($tmpContent['filters_def']['Filter_1'])) {
                $tmpContent['filters_def']['Filter_1'] = array();
            }
            $savedReportFilter = $tmpContent['filters_def']['Filter_1'];

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

            //TODO: is this not passed by reference and if so do we need this
            $tmpContent['filters_def']['Filter_1'] = $newFilter;

            $savedReport->content = $json->encode($tmpContent);
        }

        $recordIds = $this->getRecordIdsFromReport($savedReport);

        if (!empty($recordIds)) {
            // set target module
            if (!empty($savedReport->module)) {
                $args['module'] = $savedReport->module;
            } else if (!empty($savedReport->content)) {
                $content = json_decode($savedReport->content, true);
                if (!empty($content['module'])) {
                    $args['module'] = $content['module'];
                }
            }
            foreach ($recordIds as $recordId) {
                $args['record'] = $recordId;
                $records[] = $this->retrieveRecord($api, $args);
            }
        }

        $data['records'] = $records;
        return $data;
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

        if (!isset($args['ignore_datacheck'])) {
            $args['ignore_datacheck'] = false;
        }

        $json = json_decode($chart->buildJson($chart->generateXML(), $args['ignore_datacheck']), true);

        $returnData['chartData'] = $json;

        return $returnData;
    }

    /**
     * Returns the record ids of a saved report
     * @param SugarBean $savedReport
     *
     * @return array Array of record ids
     */
    protected function getRecordIdsFromReport($savedReport)
    {
        $recordIds = array();

        if (!empty($savedReport->content)) {
            $results = $savedReport->runReportQuery();

            foreach ($results as $record) {
                if(isset($record['primaryid'])){
                    $recordIds[] = $record['primaryid'];
                }
            }
        }

        return $recordIds;
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
}
