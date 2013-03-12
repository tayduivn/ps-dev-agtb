<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'clients/base/api/ListApi.php';
require_once 'data/BeanFactory.php';

class AccountsInteractionsApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'interactions' => array(
                'reqType' => 'GET',
                'path' => array('Accounts', '?', 'interactions'),
                'pathVars' => array('module', 'record'),
                'method' => 'interactions',
                'shortHelp' => 'Get interactions for current record',
                'longHelp' => 'modules/Accounts/clients/base/api/help/AccountsInteractionsApi.html',
            ),
        );
    }
 
    /**
     * Load interaction data.
     * 
     * @param ServiceBase $api
     * @param array $args
     * @param SugarBean $bean
     * @param string $relationshipName
     * @param array $params
     * @return array
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiExceptionNotAuthorized
     */
    protected function loadInteractionData(ServiceBase $api, array $args, SugarBean $bean, $relationshipName, array $params = array()) 
    {
        // Load up the relationship
        if (!$bean->load_relationship($relationshipName)) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship name ' . $relationshipName);
        }
        
        // pre-formatted response data.
        $data = array('count' => 0, 'data' => array());

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $bean->$relationshipName->getRelatedModuleName();
        $seed = BeanFactory::newBean($linkModuleName);

        if (!$seed->ACLAccess('view')) {
            // no access to view records.
            return $data;
        }

        $options = $this->parseArguments($api, $args, $seed);
        $options['filter'] = (!empty($args['filter']))? $args['filter'] : null;
        $options['list'] = (!empty($args['list']) && in_array($args['list'], array('all', 'my'))) ? $args['list'] : 'all';

        $queryParams = array(
            'deleted' => !empty($options['deleted']) ? $options['deleted'] : false,
             // ordering. Default by modify date.
            'orderby' => "date_modified DESC",
        );

        $whereParts = !empty($options['whereParts']) ? (array) $options['whereParts'] : array();

        // filter for my/all data.
        if (!empty($options['list']) && 'my' == $options['list']) {
            $whereParts[] = "{$seed->table_name}.assigned_user_id =  '{$api->user->id}'";
        }
        
        // main filter: last date/favorites/etc.
        if ('favorites' == $options['filter']) {
             $whereParts[] = "{$seed->table_name}.id IN (
                SELECT sugarfavorites.record_id
                FROM sugarfavorites
                WHERE sugarfavorites.deleted=0
                AND sugarfavorites.module = '{$linkModuleName}'
                AND sugarfavorites.assigned_user_id='{$api->user->id}')";
        } else {
            // other filter values used as integer values as "last days"
            $days = (!is_numeric($options['filter']) || 0 >= (int) $options['filter'])?  7 : (int) $options['filter'];
            $db = DBManagerFactory::getInstance();

            $convertedModifiedDate = $db->convert("{$seed->table_name}.date_modified", 'add_date', array($days, 'DAY'));
            $currentDate = $db->convert('', 'today');

            $whereParts[] = "$convertedModifiedDate >= $currentDate";
        }

        // prepearing where condition.
        if (!empty($params['where'])) {
            if (is_array($params['where'])) {
                $whereParts = array_merge($whereParts, $params['where']);
            } else {
                $whereParts[] = $params['where'];
            }
        }
        $queryParams['where'] =  (count($whereParts) > 0)? '(' . implode(") AND (", $whereParts) . ')' : '';

        // fetching...
        $result = $bean->$relationshipName->query($queryParams);
        
        // prepare data/counters with ACL checking.
        foreach ($result['rows'] as $rowId => $row) {
            $seed = BeanFactory::retrieveBean($linkModuleName, $rowId);
            // if bean is empty then the bean is not allowed by ACL and we skip it in counters/data.
            if ($seed) {
                if (!isset($params['limit']) || $data['count'] < $params['limit']) {              
                    $data['data'][] = $this->formatBean($api, $args, $seed);
                }
                $data['count']++;
            }
        }
        return $data;
    }

    /**
     * Load account bean.
     * 
     * @param ServiceBase $api
     * @param array $args
     * @return Account
     */
    protected function loadAccountBean(ServiceBase $api, array $args)
    {
        return $this->loadBean($api, $args);
    }
    
    /**
     * Interactions API method.
     * 
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function interactions(ServiceBase $api, array $args) 
    {
        $bean = $this->loadAccountBean($api, $args);
        $data = array();
        $defaultLimit = 5;
        $limits = array(
            'calls' => $defaultLimit,
            'meetings' => $defaultLimit,
            'emailsRecv' => $defaultLimit,
            'emailsSent' => $defaultLimit,
        );

        if (!empty($args['view']) && in_array($args['view'], array('calls', 'meetings', 'emailsRecv', 'emailsSent')) && isset($args['limit'])) {
            $limits[$args['view']] = (int) $args['limit'];
        }

        // Calls
        $data['calls'] = $this->loadInteractionData($api, $args, $bean, 'calls', array(
            'limit' => $limits['calls'],
            'where' => "status='Held'",
        ));
        // Meetings
        $data['meetings'] = $this->loadInteractionData($api, $args, $bean, 'meetings', array(
            'limit' => $limits['meetings'],
            'where' => "status='Held'",
        ));
        // Recived Emails
        $data['emailsRecv'] = $this->loadInteractionData($api, $args, $bean, 'emails', array(
            'limit' => $limits['emailsRecv'],
            'where' => "type='inbound'"
        ));
        // Sent Emails
        $data['emailsSent'] = $this->loadInteractionData($api, $args, $bean, 'emails', array(
            'limit' => $limits['emailsSent'],
            'where' => "type='out'"
        ));
        return $data;
    }
}
