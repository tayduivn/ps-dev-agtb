<?php

require_once('include/api/SugarApi.php');

class SubscriptionsApi extends SugarApi
{

    public function registerApiRest()
    {
        return array(
            'subscribeToRecord' => array(
                'reqType' => 'POST',
                'path' => array('<module>','?', 'subscribe'),
                'pathVars' => array('module','record'),
                'method' => 'subscribeToRecord',
                'shortHelp' => 'This method subscribes the user to the current record, for activity stream updates.',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordSubscribe.html',
            ),
            'unsubscribeFromRecord' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','?', 'unsubscribe'),
                'pathVars' => array('module','record'),
                'method' => 'unsubscribeFromRecord',
                'shortHelp' => 'This method unsubscribes the user from the current record, for activity stream updates.',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordUnsubscribe.html',
            )
        );
    }

    public function subscribeToRecord(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (!empty($bean)) {
            if ($bean->ACLAccess('view')) {
                return Subscription::subscribeUserToRecord($api->user, $bean);
            }
        }
        return false;
    }

    public function unsubscribeFromRecord(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (!empty($bean)) {
            if ($bean->ACLAccess('view')) {
                return Subscription::unsubscribeUserFromRecord($api->user, $bean);
            }
        }
        return false;
    }
}
