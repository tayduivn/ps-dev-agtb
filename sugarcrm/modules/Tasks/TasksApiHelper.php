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

require_once('data/SugarBeanApiHelper.php');

class TasksApiHelper extends SugarBeanApiHelper
{
    /**
     * Formats the bean so it is ready to be handed back to the API's client.
     *
     * @param $bean SugarBean The Task you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        $data = parent::formatForApi($bean, $fieldList, $options);
        if (isset($bean->contact_id)) {
            $contact = BeanFactory::getBean('Contacts', $bean->contact_id);

            if (!empty($contact) && $contact->id != "") {
                if (isset($data['contact_name'])) {
                    $data['contact_name'] = empty($contact->full_name) ? '' : $contact->full_name;
                }
                if (isset($data['contact_phone'])) {
                    $data['contact_phone'] = empty($contact->phone_work) ? '' : $contact->phone_work;
                }
            }
        }
        return $data;
    }
}

