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
            'getRecentlyViewed' => array(
                'reqType' => 'GET',
                'path' => array('recent'),
                'pathVars' => array('',''),
                'method' => 'getRecentlyViewed',
                'shortHelp' => 'This method retrieves recently viewed records for the user.',
                'longHelp' => 'include/api/help/me_recently_viewed_help.html',
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

        $options['moduleList'] = array();
        if (!empty($args['module_list'])) {
            $options['moduleList'] = array_filter(explode(',', $args['module_list']));
        }

        return $options;
    }

    /**
     * Filters the list of modules to the ones that the user has access to and
     * that exist on the moduleList.
     *
     * @param array $modules Modules list.
     * @param string $acl (optional) ACL action to check, default is `list`.
     * @return array Filtered modules list.
     */
    private function filterModules(array $modules, $acl = 'list')
    {
        foreach ($modules as $key => $module) {
            $seed = BeanFactory::newBean($module);
            if (!is_subclass_of($seed, 'SugarBean') || !$seed->ACLAccess($acl) || !in_array($module, $GLOBALS['moduleList'])) {
                unset($modules[$key]);
            }
        }

        return $modules;
    }

    /**
     * Gets recently viewed records.
     *
     * @param ServiceBase $api Current api.
     * @param array $args Arguments from request.
     * @param string $acl (optional) ACL action to check, default is `list`.
     * @return array List of recently viewed records.
     */
    public function getRecentlyViewed($api, $args, $acl = 'list')
    {
        $this->requireArgs($args, array('module_list'));

        $options = $this->parseArguments($args);

        $moduleList = $this->filterModules($options['moduleList'], $acl);
        if (empty($moduleList)) {
            return array('next_offset' => -1 , 'records' => array());
        }

        if (count($moduleList) === 1) {
            $moduleName = $moduleList[0];
            $seed = BeanFactory::newBean($moduleName);
            $mainQuery = $this->getRecentlyViewedQueryObject($seed, $options);
            $mainQuery->orderByRaw('MAX(tracker.date_modified)', 'DESC');

        } else {
            $mainQuery = new SugarQuery();
            foreach ($moduleList as $moduleName) {
                $seed = BeanFactory::newBean($moduleName);
                $mainQuery->union($this->getRecentlyViewedQueryObject($seed, $options), true);
            }
            $mainQuery->orderByRaw('max_date_modified', 'DESC');
        }

        // Add an extra record to the limit so we can detect if there are more records to be found.
        $mainQuery->limit($options['limit'] + 1);
        $mainQuery->offset($options['offset']);

        $data = $beans = array();
        $data['next_offset'] = -1;

        $results = $mainQuery->execute();
        foreach ($results as $idx => $recent) {
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
     * Returns query object to retrieve list of recently viewed records by
     * module.
     *
     * @param SugarBean $seed Instance of current bean.
     * @param array $options Prepared options.
     * @return SugarQuery query to execute.
     */
    protected function getRecentlyViewedQueryObject($seed, $options)
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
            $query->where()->queryAnd()->gte('tracker.date_modified', $td->asDb());
        }

        foreach ($query->select()->select as $v) {
            $query->groupBy($v->table . '.' . $v->field);
        }

        $query->select()->fieldRaw('MAX(tracker.date_modified)', 'max_date_modified');

        return $query;
    }
}
