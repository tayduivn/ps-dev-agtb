<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class SugarJobAddActivitySubscriptions implements RunnableSchedulerJob
{
    protected $job;

    /**
     * This method implements setJob from RunnableSchedulerJob. It sets the
     * SchedulersJob instance for the class.
     *
     * @param SchedulersJob $job the SchedulersJob instance set by the job queue
     */
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    public function run($data)
    {
        $data = unserialize($data);
        $act = BeanFactory::retrieveBean('Activities', $data['act_id']);
        $ignoreDeleted = ($act->activity_type !== 'deleted'); //retrieve deleted bean if activity was a deletion
        $bean = BeanFactory::retrieveBean($data['bean_module'], $data['bean_id'], array(), $ignoreDeleted);
        $subs = BeanFactory::getBeanName('Subscriptions');
        if (!$act->load_relationship("activities_users")) {
            $this->job->failJob("Could not load the relationship.");
        }

        foreach ($data['user_partials'] as $user_partial) {
            $user = BeanFactory::retrieveBean('Users', $user_partial['created_by']);

            if ($user && $bean) {
                $context = array('user' => $user);

                if ($bean->ACLAccess('view', $context)) {
                    // If we have access to the bean, we allow the user to see
                    // the activity on the home page and the records list page.
                    $fields = array();

                    if ($act->activity_type == 'update') {
                        foreach ($data['args']['dataChanges'] as $field) {
                            $fields[$field['field_name']] = 1;
                        }
                        $bean->ACLFilterFieldList($fields, $context);
                        $fields = array_keys($fields);
                    }

                    $act->activities_users->add($user, array('fields' => json_encode($fields)));
                } else {
                    // If we don't have access to the bean, we remove the user's
                    // subscription to the bean.
                    $subs::unsubscribeUserFromRecord($user, $bean);
                }
            }
        }
        $this->job->succeedJob();
        return true;
    }
}
