<?php

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
        $bean = BeanFactory::retrieveBean($data['bean_module'], $data['bean_id']);
        $subs = BeanFactory::getBeanName('Subscriptions');
        if (!$act->load_relationship("activities_users")) {
            $this->job->failJob("Could not load the relationship.");
        }

        foreach ($data['user_partials'] as $user_partial) {
            $user = BeanFactory::retrieveBean('Users', $user_partial['created_by']);

            if ($user) {
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
