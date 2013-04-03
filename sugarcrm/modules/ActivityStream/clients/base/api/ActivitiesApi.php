<?php

require_once 'clients/base/api/FilterApi.php';

class ActivitiesApi extends FilterApi
{

    public function registerApiRest()
    {
        return array(
            // TODO: Look into removing this method. We shouldn't need this, but
            // it's here to prevent breaking stuff before SugarCon 2013.
            'record_activities' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?', 'link', 'activities'),
                'pathVars' => array('module','record', ''),
                'method' => 'getRecordActivities',
                'shortHelp' => 'This method retrieves a record\'s activities',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordActivities.html',
            ),
            'module_activities' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'Activities'),
                'pathVars' => array('module', ''),
                'method' => 'getModuleActivities',
                'shortHelp' => 'This method retrieves a module\'s activities',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/moduleActivities.html',
            ),
            'home_activities' => array(
                'reqType' => 'GET',
                'path' => array('Activities'),
                'pathVars' => array(''),
                'method' => 'getHomeActivities',
                'shortHelp' => 'This method gets homepage activities for a user',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/homeActivities.html',
            ),
            'record_activities_filter' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?', 'link', 'activities', 'filter'),
                'pathVars' => array('module','record', ''),
                'method' => 'getRecordActivities',
                'shortHelp' => 'This method retrieves a filtered list of a record\'s activities',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordActivities.html',
            ),
            'module_activities_filter' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'Activities', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'getModuleActivities',
                'shortHelp' => 'This method retrieves a filtered list of a module\'s activities',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/moduleActivities.html',
            ),
            'home_activities_filter' => array(
                'reqType' => 'GET',
                'path' => array('Activities', 'filter'),
                'pathVars' => array(''),
                'method' => 'getHomeActivities',
                'shortHelp' => 'This method gets a filtered list of homepage activities for a user',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/homeActivities.html',
            ),
        );
    }

    public function getRecordActivities(ServiceBase $api, array $args)
    {
        $params = $this->parseArguments($api, $args);
        $record = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        $query = self::getQueryObject($api, $params, $record);
        return $this->formatResult($api, $args, $query, $record);
    }

    public function getModuleActivities(ServiceBase $api, array $args)
    {
        $params = $this->parseArguments($api, $args);
        $record = BeanFactory::getBean($args['module']);
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        $query = self::getQueryObject($api, $params, $record);
        return $this->formatResult($api, $args, $query, $record);
    }

    public function getHomeActivities(ServiceBase $api, array $args)
    {
        $params = $this->parseArguments($api, $args);
        $query = self::getQueryObject($api, $params);
        return $this->formatResult($api, $args, $query);
    }

    public function parseArguments(ServiceBase $api, array $args)
    {
        $params = parent::parseArguments($api, $args);
        if (isset($args['filter'])) {
            $params['filter'] = $args['filter'];
        }
        return $params;
    }

    protected function formatResult(ServiceBase $api, array $args, SugarQuery $query, SugarBean $bean = null)
    {
        $response = array();
        $response['records'] = $query->execute('array', false);
        // We add one to it when setting it, so we subtract one now for the true
        // limit.
        $limit = $query->limit - 1;
        $count = count($response['records']);
        if ($count > $limit) {
            $nextOffset = $query->offset + $limit;
            array_pop($response['records']);
        } else {
            $nextOffset = -1;
        }
        $timedate = TimeDate::getInstance();

        // Emulate going through SugarBean, without the extra DB hits.
        foreach ($response['records'] as &$record) {
            $record['comment_count'] = (int)$record['comment_count'];
            $record['data'] = json_decode($record['data'], true);
            $record['last_comment'] = json_decode($record['last_comment'], true);
            $date_modified = $timedate->fromDbType($record['date_modified'], 'datetime');
            $record['date_modified'] = $timedate->asIso($date_modified);
            $date_entered = $timedate->fromDbType($record['date_entered'], 'datetime');
            $record['date_entered'] = $timedate->asIso($date_entered);

            if ($record['activity_type'] == 'update') {
                if (is_null($bean) || empty($bean->id)) {
                    $record['fields'] = json_decode($record['fields'], true);
                    if (!empty($record['fields'])) {
                        foreach ($record['data']['changes'] as &$change) {
                            if (!in_array($change['field_name'], $record['fields'])) {
                                unset($record['data']['changes'][$change['field_name']]);
                            }
                        }
                        unset($record['fields']);
                    }
                } else {
                    $context = array('user' => $api->user);
                    $bean->ACLFilterFieldList($record['data']['changes'], $context);
                }
            }

            // Format the name of the user.
            $name = array($record['first_name'], $record['last_name']);
            if ($api->user->showLastNameFirst()) {
                $name = array_reverse($name);
            }
            $record['created_by_name'] = implode(' ', $name);

            if (!isset($record['created_by_name']) && isset($record['data']['created_by_name'])) {
                $record['created_by_name'] = $record['data']['created_by_name'];
            }
        }
        $response['next_offset'] = $nextOffset;
        $response['args'] = $args;
        $response['sequel'] = $query->compileSql();
        return $response;
    }

    public static function getQueryObject(ServiceBase $api, array $params, SugarBean $record = null)
    {
        $seed = BeanFactory::getBean('Activities');
        $query = new SugarQuery();
        $query->from($seed);

        // Always order the activity stream by date modified DESC.
        $query->orderBy('date_modified', 'DESC');

        // +1 used to determine if we have more records to show.
        $query->limit($params['limit'] + 1)->offset($params['offset']);

        $columns = array('activities.*', 'users.first_name', 'users.last_name', 'users.picture');


        // Join with user names.
        $query->joinTable('users', array('joinType' => 'INNER'))
            ->on()->equalsField('activities.created_by', 'users.id');

        $join = $query->joinTable('activities_users', array('joinType' => 'INNER', 'linkName' => 'activities_users'))
            ->on()->equalsField("activities_users.activity_id", 'activities.id')
            ->equals("activities_users.deleted", 0);

        if (!$record || !$record->id) {
            // Join with cached list of activities to show.
            $columns[] = 'activities_users.fields';
            $join = $join->queryOr();
            $join->queryAnd()->equals('activities_users.parent_type', 'Teams')
                ->equals('activities_users.parent_id', 1);
            $join->queryAnd()->equals('activities_users.parent_type', 'Users')
                ->equals('activities_users.parent_id', $api->user->id);
            if ($record) {
                $query->where()->equals('activities.parent_type', $record->module_name);
            }
        } else {
            // If we have a relevant bean, we add our where condition.
            $query->where()->equals('activities_users.parent_type', $record->module_name);
            if ($record->id) {
                $query->where()->equals('activities_users.parent_id', $record->id);
            }
        }

        // We only support filtering on activity_type.
        if (!empty($params['filter'])) {
            self::addFilters($params['filter'], $query->where(), $query);
        }

        $query->where()->equals('deleted', 0);
        $query->select($columns);

        return $query;
    }
}
