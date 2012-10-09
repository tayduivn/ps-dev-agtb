<?php
//FILE SUGARCRM flav=free ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/api/ListApi.php';
require_once 'data/BeanFactory.php';

class OpportunitiesSummerApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'influencers' => array(
                'reqType' => 'GET',
                'path' => array('Opportunities','?', 'influencers'),
                'pathVars' => array('module', 'record'),
                'method' => 'influencers',
                'shortHelp' => '',
                'longHelp' => '',
            ),
            'interactions' => array(
                'reqType' => 'GET',
                'path' => array('Opportunities','?', 'interactions'),
                'pathVars' => array('module', 'record'),
                'method' => 'interactions',
                'shortHelp' => '',
                'longHelp' => '',
            ),
            'expert' => array(
                'reqType' => 'GET',
                'path' => array('Opportunities','?', 'expert'),
                'pathVars' => array('module', 'record'),
                'method' => 'recommendExpert',
                'shortHelp' => 'Recommend users to help with a particular record',
                'longHelp' => 'Test',
            ),
            'expertTypeahead' => array(
                'reqType' => 'GET',
                'path' => array('Opportunities','?', 'expertTypeahead'),
                'pathVars' => array('module', 'record'),
                'method' => 'recommendExpertTypeahead',
                'shortHelp' => 'Typeahead provider for recommended users',
                'longHelp' => '',
            ),

        );
    }

    public function interactions($api, $args)
    {
        $record = $this->getBean($api, $args);
        $account = $this->getAccountBean($api, $args);
        $box = BoxOfficeClient::getInstance();
        $data = array('calls' => array(),'meetings' => array(),'emails' => array());

        // Limit here so that we still get the full count for interactions.
        $limit = 5;

        // 'NOP' for emails on an opportunity.
        $emails = array();
        $data['emails'] = array('count' => count($emails), 'data' => array());
        $i = 0;
        while($i < $limit && isset($emails[$i])) {
            $data['emails']['data'][] = $emails[$i];
            $i++;
        }

        $calls = $this->getAccountRelationship($api, $args, $account, 'calls', null);
        $meetings = $this->getAccountRelationship($api, $args, $account, 'meetings', null);

        $data['calls'] = array('count' => count($calls), 'data' => array());
        $i = 0;
        while($i < $limit && isset($calls[$i])) {
            $data['calls']['data'][] = $calls[$i];
            $i++;
        }

        $data['meetings'] = array('count' => count($meetings), 'data' => array());
        $i = 0;
        while($i < $limit && isset($meetings[$i])) {
            $data['meetings']['data'][] = $meetings[$i];
            $i++;
        }

        return $data;
    }

    public function recommendExpert($api, $args)
    {
        $args['title'] = empty($args['title'])? '' : $args['title'];
        $data = $this->getInteractionsByUser($api, $args);
        $sortCallback = function($a, $b) {
            return $a['interaction_count'] - $b['interaction_count'];
        };
        $filterCallback1 = function($a) use($args) {
            return $args['title'] == $a['title'];
        };
        $filtered = array_filter($data, $filterCallback1);
        if(count($filtered) == 0) {
            $filtered = $data;
        }
        $filterCallback2 = function($a) {
            return $a['interaction_count'] !== 0;
        };
        $filtered = array_filter($filtered, $filterCallback2);
        if(count($filtered) == 0) {
            $filtered = $data;
        }
        usort($filtered, $sortCallback);
        return array_shift($filtered);
    }

    public function recommendExpertTypeahead($api, $args)
    {
        // TODO: Use employee_status instead of first name.
        $data = array();
        $seed = BeanFactory::getBean("Users");
        $result = $seed->get_list();
        foreach ($result['list'] as $bean) {
            if(!empty($bean->title) && !empty($bean->first_name)) {
                if(empty($data[$bean->title])) {
                    $data[$bean->title] = array();
                }
                $data[$bean->title][] = $bean;
            }
        }
        return array_keys($data);
    }


    public function influencers($api, $args)
    {
        $data = $this->getInteractionsByUser($api, $args);
        return $data;
    }

    protected function getInteractionsByUser($api, $args) {
        $account = $this->getAccountBean($api, $args);
        $relationships = array('calls' => 0, 'meetings' => 0);
        $data = array();
        foreach($relationships as $relationship => $ignore) {
            // Load up the relationship
            if (!$account->load_relationship($relationship)) {
                // The relationship did not load, I'm guessing it doesn't exist
                throw new SugarApiExceptionNotFound('Could not find a relationship name ' . $relationship);
            }
            // Figure out what is on the other side of this relationship, check permissions
            $linkModuleName = $account->$relationship->getRelatedModuleName();
            $linkSeed = BeanFactory::newBean($linkModuleName);
            if (!$linkSeed->ACLAccess('view')) {
                throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
            }

            $relationshipData = $account->$relationship->query(array());

            foreach ($relationshipData['rows'] as $id => $value) {
                $bean = BeanFactory::getBean(ucfirst($relationship), $id);
                $bean->load_relationship('users');
                $userModuleName = $bean->users->getRelatedModuleName();
                $userSeed = BeanFactory::newBean($userModuleName);
                $userData = $bean->users->query(array());

                foreach($userData['rows'] as $userId => $user) {
                    if(empty($data[$userId])) {
                        $userBean = BeanFactory::getBean('Users', $userId);
                        if($userBean) {
                            $data[$userId] = array_merge($this->formatBean($api, $args, $userBean), $relationships);
                            $data[$userId][$relationship]++;
                            $data[$userId]['interaction_count'] = 1;
                        }
                    } else {
                        $data[$userId][$relationship]++;
                        $data[$userId]['interaction_count']++;
                    }
                }
            }
        }
        return array_values($data);
    }

    protected function getBean($api, $args)
    {
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
        return $record;
    }

    protected function getAccountBean($api, $args)
    {
        $record = $this->getBean($api, $args);
        // Load up the relationship
        if (!$record->load_relationship('accounts')) {
            throw new SugarApiExceptionNotFound('Could not find a relationship name accounts');
        }

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->accounts->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $accounts = $record->accounts->query(array());
        foreach ($accounts['rows'] as $accountId => $value) {
            $account = BeanFactory::getBean('Accounts', $accountId);
            if (empty($account)) {
                throw new SugarApiExceptionNotFound('Could not find parent record '.$accountId.' in module Accounts');
            }
            if (!$account->ACLAccess('view')) {
                throw new SugarApiExceptionNotAuthorized('No access to view records for module: Accounts');
            }

            // Only one account, so we can return inside the loop.
            return $account;
        }
    }

    protected function getAccountRelationship($api, $args, $account, $relationship, $limit = 5, $query = array())
    {
        // Load up the relationship
        if (!$account->load_relationship($relationship)) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship name ' . $relationship);
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $account->$relationship->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $relationshipData = $account->$relationship->query($query);
        $rowCount = 1;

        $data = array();
        foreach ($relationshipData['rows'] as $id => $value) {
            $rowCount++;
            $bean = BeanFactory::getBean(ucfirst($relationship), $id);
            $data[] = $this->formatBean($api, $args, $bean);
            if (!is_null($limit) && $rowCount == $limit) {
                // We have hit our limit.
                break;
            }
        }
        return $data;
    }


}
