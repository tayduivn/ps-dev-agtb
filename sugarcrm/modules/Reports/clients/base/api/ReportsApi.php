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
            'reportData' => array(
                'reqType' => 'POST',
                'path' => array('Reports', '?', 'records'),
                'pathVars' => array('', 'record', ''),
                'method' => 'getReportData',
                'shortHelp' => 'An API to deliver an array of records from a saved report',
                'longHelp' => 'modules/Reports/api/help/module_reportrecords_post.html',
            ),
        );
    }

    /**
     * Creates a record list from a saved report
     * @param ServiceBase $api The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param array $args The arguments array passed in from the API containing the module and the records
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiException
     * @return array id, module, records
     */
    public function createRecordList(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('record'));

        $savedReport = $this->getReportRecord($api, $args);
        $recordIds = $this->getRecordIdsFromReport($savedReport);
        $id = RecordListFactory::saveRecordList($recordIds, 'Reports');
        $loadedRecordList = RecordListFactory::getRecordList($id);

        return $loadedRecordList;
    }

    /**
     * Returns the records associated with a saved report
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and the records
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiException
     * @return array data
     */
    public function getReportData($api, $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $data = array();

        $_args = array();
        $_args['record'] = $args['record'];
        $_args['module'] = 'Reports';

        $report = $this->retrieveRecord($api, $_args);
        $records = $this->getReportRecords($api, $args);

        $data['saved_report'] = $report;
        $data['records'] = $records;

        return $data;
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
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiException
     * @return array records
     */
    public function getReportRecords($api, $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $records = array();
        $savedReport = $this->getReportRecord($api, $args);

        // If a user filter argument is passed
        if (isset($args['filterDef']) && !empty($args['filterDef'])) {
            $clickFilter = $args['filterDef'];
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
            foreach ($recordIds as $recordId) {
                $args['record'] = $recordId;
                $records[] = $this->retrieveRecord($api, $args);
            }
        }

        return $records;
    }

    /**
     * Returns a report record
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing a record id
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiException
     * @return array record
     */
    public function getReportRecord($api, $args)
    {
        $this->requireArgs($args, array('record'));

        $savedReport = BeanFactory::newBean('Reports');
        if (!$savedReport->ACLAccess('access')) {
            throw new SugarApiExceptionNotAuthorized();
        }
        $savedReport = $this->getSavedReportById($args['record']);

        return $savedReport;
    }

    /**
     * Retrieves a saved Report by Report Id
     * @param $reportId
     *
     * @return SugarBean
     */
    protected function getSavedReportById($reportId)
    {
        return BeanFactory::getBean('Reports', $reportId);
    }

    /**
     * Returns the record ids of a saved report
     * @param $reportId
     *
     * @return array Array of record ids
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiException
     */
    protected function getRecordIdsFromReport($savedReport)
    {
        $recordIds = array();

        if (!$savedReport->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: Reports');
        }
        if (empty($savedReport->id)) {
            throw new SugarApiExceptionNotAuthorized('Unable to retrieve report');
        }
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
}
