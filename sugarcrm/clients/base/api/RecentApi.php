<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'data/BeanFactory.php';
require_once 'include/api/SugarApi.php';

class RecentApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'retrieveRecents' => array(
                'reqType' => 'GET',
                'path' => array('recent'),
                'pathVars' => array('',''),
                'method' => 'retrieveRecents',
                'shortHelp' => 'This method retrieves recents beans for the user.',
                'longHelp' => 'include/api/help/me_recents_help.html',
            ),
        );
    }

    /**
     * Gets the user bean for the user of the api
     *
     * @return User
     */
    protected function getUserBean()
    {
        global $current_user;
        return $current_user;
    }

    /**
     * Set up options from args and default values.
     *
     * @param arrat $args Arguments from request.
     * @return array options after setup.
     */
    protected function parseArguments($args)
    {
        $options = array();
        $options['limit'] = !empty($args['limit']) ? (int) $args['limit'] : 20;
        if (!empty($args['max_num'])) {
            $options['limit'] = (int) $args['max_num'];
        }

        $options['offset'] = 0;

        if (!empty($args['offset'])) {
            if ($args['offset'] == 'end') {
                $options['offset'] = 'end';
            } else {
                $options['offset'] = (int) $args['offset'];
            }
        }

        $options['select'] = !empty($args['fields']) ? explode(",", $args['fields']) : null;
        $options['module'] = !empty($args['module']) ? $args['module'] : null;
        $options['date'] = !empty($args['date']) ? $args['date'] : null;

        return $options;
    }

    /**
     * Returns list of modules that can be used as recents.
     *
     * @param string $acl (optional) ACL action to check, default is `list`.
     * @return array list of modules.
     */
    protected function getAllowedModulesForRecents($acl = 'list')
    {
        $query = new SugarQuery();
        $query->select('module_name');
        $query->from(BeanFactory::newBean('Trackers'))
            ->distinct(true)
            ->groupBy('module_name');
        $query->where()->notIn('module_name', array('Dashboards'));

        $result = $query->execute();
        if (empty($result)) {
            return array();
        }

        $allowedModules = array();
        foreach ($result as $module) {
            $seed = BeanFactory::newBean($module['module_name']);
            if ($seed->ACLAccess($acl)) {
                $allowedModules[] = $module['module_name'];
            }
        }

        return $allowedModules;
    }

    /**
     * Gets list of recents beans.
     *
     * @param ServiceBase $api Current api.
     * @param array $args Arguments from request.
     * @param string $acl (optional) ACL action to check, default is `list`.
     * @return array List of recents.
     * @throws SugarApiExceptionNotAuthorized if no access to module.
     */
    public function retrieveRecents($api, $args, $acl = 'list')
    {
        $options = $this->parseArguments($args);

        if (!empty($options['module'])) {
            $seed = BeanFactory::newBean($options['module']);
            if (!$seed->ACLAccess($acl)) {
                throw new SugarApiExceptionNotAuthorized('No access to view recents for module: ' . $options['module']);
            }
            $modulesList = array($options['module']);
        } else {
            $modulesList = $this->getAllowedModulesForRecents();
        }

        if (empty($modulesList)) {
            return array('next_offset' => -1 , 'records' => array());
        }

        if (sizeof($modulesList) == 1) {
            $moduleName = $modulesList[0];
            $seed = BeanFactory::newBean($moduleName);
            $mainQuery = $this->getRecentsQueryObject($seed, $options);
            $mainQuery->orderByRaw('MAX(tracker.date_modified)', 'DESC');
        } else {
            $mainQuery = new SugarQuery();
            foreach ($modulesList as $moduleName) {
                $seed = BeanFactory::newBean($moduleName);
                $mainQuery->union($this->getRecentsQueryObject($seed, $options), true);
            }
            $mainQuery->orderByRaw('max_date_modified', 'DESC');
        }

        // Add an extra record to the limit so we can detect if there are more records to be found.
        $mainQuery->limit($options['limit'] + 1);
        $mainQuery->offset($options['offset']);

        $data = $beans = array();
        $data['next_offset'] = -1;

        $recents = $mainQuery->execute();
        foreach ($recents as $idx => $recent) {
            if ($idx == $options['limit']) {
                $data['next_offset'] = (int) ($options['limit'] + $options['offset']);
                break;
            }
            $seed = BeanFactory::getBean($recent['module_name'], $recent['id']);
            $beans[$seed->id] = $seed;
        }

        $data['records'] = $this->formatBeans($api, $args, $beans);

        return $data;
    }

    /**
     * Returns query object to retrieve list of recents by seed (module).
     *
     * @param SugarBean $seed Instance of current bean.
     * @param array $options Prepared options.
     * @return SugarQuery query to execute.
     */
    protected function getRecentsQueryObject($seed, $options)
    {
        $currentUser = $this->getUserBean();

        $query = new SugarQuery();
        $query->from($seed);

        // FIXME: FRM-226, logic for these needs to be moved to SugarQuery

        // Since tracker relationships don't actually exist, we're gonna have to add a direct join
        $query->joinRaw(
            sprintf(
                " JOIN tracker ON tracker.item_id=%s.id AND tracker.module_name='%s' AND tracker.user_id='%s' ",
                $query->from->getTableName(),
                $query->from->module_name,
                $currentUser->id
            ),
            array('alias' => 'tracker')
        );

        // we need to set the linkName to hack around tracker not having real relationships
        /* TODO think about how to fix this so we can be less restrictive to raw joins that don't have a relationship */
        $query->join['tracker']->linkName = 'tracker';

        $query->select(array('id', array('tracker.module_name', 'module_name')));

        if (!empty($options['date'])) {
            $td = new SugarDateTime();
            $td->modify($options['date']);
            $query->where()->queryAnd()->gte("tracker.date_modified", $td->asDb());
        }

        foreach ($query->select()->select as $v) {
            $query->groupBy($v->table . '.' . $v->field);
        }

        $query->select()->fieldRaw('MAX(tracker.date_modified)', 'max_date_modified');

        return $query;
    }
}
