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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once('clients/base/api/ModuleApi.php');

class KBSContentsUsefulnessApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'useful' => array(
                'reqType' => 'PUT',
                'path' => array('KBSContents', '?', 'useful'),
                'pathVars' => array('module', 'record', 'useful'),
                'method' => 'voteUseful',
                'shortHelp' => 'This method votes a record of the specified type as a useful',
                'longHelp' => 'include/api/help/module_get_help.html',
            ),
            'notuseful' => array(
                'reqType' => 'PUT',
                'path' => array('KBSContents', '?', 'notuseful'),
                'pathVars' => array('module', 'record', 'notuseful'),
                'method' => 'voteNotUseful',
                'shortHelp' => 'This method votes a record of the specified type as a not useful',
                'longHelp' => 'include/api/help/module_get_help.html',
            ),
        );
    }

    /**
     * This method votes a record of the specified type as a useful or not useful.
     *
     * @param ServiceBase $api
     * @param array $args
     * @param bool $isUseful
     *
     * @throws SugarApiExceptionNotAuthorized
     *
     * @return array An array version of the SugarBean with only the requested fields (also filtered by ACL)
     */
    protected function vote(ServiceBase $api, $args, $isUseful)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = $this->loadBean($api, $args, 'view');

        if (!$bean->ACLAccess('view')) {
            // No create access so we construct an error message and throw the exception
            $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
            $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            $exceptionArgs = null;
            if (!empty($moduleName)) {
                $exceptionArgs = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized(
                'EXCEPTION_VOTE_USEFULNESS_NOT_AUTHORIZED',
                $exceptionArgs,
                $args['module']
            );
        }

        if ($isUseful) {
            $bean->useful++;
        } else {
            $bean->notuseful++;
        }
        $bean->save();

        $bean = BeanFactory::getBean($bean->module_dir, $bean->id, array('use_cache' => false));
        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);

        return $data;
    }

    /**
     * This method votes a record of the specified type as a useful.
     *
     * @param ServiceBase $api
     * @param array $args
     *
     * @return array An array version of the SugarBean with only the requested fields (also filtered by ACL)
     */
    public function voteUseful($api, $args)
    {
        return $this->vote($api, $args, true);
    }

    /**
     * This method votes a record of the specified type as a not useful.
     *
     * @param ServiceBase $api
     * @param array $args
     *
     * @return array An array version of the SugarBean with only the requested fields (also filtered by ACL)
     */
    public function voteNotUseful($api, $args)
    {
        return $this->vote($api, $args, false);
    }
}
