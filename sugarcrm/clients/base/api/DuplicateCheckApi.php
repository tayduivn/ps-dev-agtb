<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class DuplicateCheckApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'duplicateCheck' => array(
                'reqType' => 'POST',
                'path' => array('<module>','duplicateCheck'),
                'pathVars' => array('module',''),
                'method' => 'checkForDuplicates',
                'shortHelp' => 'Check for duplicate records within a module',
                'longHelp' => 'include/api/help/duplicateCheck.html',
            ),
        );
    }

    /**
     * Using the appropriate duplicate check service, search for duplicates in the system
     * TODO: we should refactor some of the bean loading in SugarApi so we can move some of this logic there
     *
     * @param ServiceBase $api
     * @param array $args
     */
    function checkForDuplicates(ServiceBase $api, array $args)
    {
        //create a new bean & check ACLs
        $bean = BeanFactory::newBean($args['module']);

        $this->handleEmptyBean($bean);

        $args=$this->trimArgs($args);

        if (!$bean->ACLAccess('read')) {
            throw new SugarApiExceptionNotAuthorized('No access to read records for module: '.$args['module']);
        }

        //populate bean
        $errors = $this->populateFromApi($api, $bean, $args);
        if ($errors !== true) {
            $displayErrors = print_r($errors, true);
            throw new SugarApiExceptionInvalidParameter("Unable to run duplicate check. There were validation errors on the submitted data: $displayErrors");
        }

        //retrieve possible duplicates
        ob_start();
        $results = $bean->findDuplicates();
        $res = ob_get_contents();
        ob_end_clean();

        if (strlen($res) > 0) {
            $GLOBALS['log']->debug("PHP Compiler Errors Issued: ". $res);
            throw new SugarApiExceptionRequestMethodFailure("PHP Compiler Errors Issued: ". $res);
        }

        if ($results) {
            return $results;
        } else {
            return array();
        }

    }

    protected function handleEmptyBean($bean)
    {
        if (empty($bean)) {
            throw new SugarApiExceptionInvalidParameter('Unable to run duplicate check. Bean was empty after attempting to populate from API');
        }
    }

    protected function trimArgs($args)
    {
        $args2 = array();
        foreach($args as $key => $value) {
            $args2[trim($key)] = (is_string($value)) ? trim($value) : $value;
        }
        return $args2;
    }

    protected function populateFromApi($api, $bean, $args)
    {
        return ApiHelper::getHelper($api,$bean)->populateFromApi($bean,$args);
    }
}
