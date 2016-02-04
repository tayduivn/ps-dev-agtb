<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class ReminderApi receiver request from trigger server,
 * checks and extracts sent data and puts it to @see \Sugarcrm\Sugarcrm\Trigger\Reminder class
 */
class ReminderApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'reminder' => array(
                'reqType' => 'POST',
                'path' => array('reminder'),
                'pathVars' => array(''),
                'method' => 'remind',
                'shortHelp' => "Reminds the user subscribed to the call or the meeting",
                'longHelp' => 'sugarcrm/include/api/help/reminder.html',
                'noLoginRequired' => true,
                'noEtag' => true,
                'ignoreMetaHash' => true,
                'ignoreSystemStatusError' => true,
            )
        );
    }

    /**
     * Reminds the user subscribed to the call or the meeting.
     *
     * @param ServiceBase $api
     * @param array $args
     * @return string
     * @throws SugarApiExceptionInvalidParameter when module isn't correct
     */
    public function remind(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'beanId', 'userId'));

        if (!in_array($args['module'], array('Calls', 'Meetings'))) {
            throw new SugarApiExceptionInvalidParameter();
        }

        $this->getReminder()->remind($args['module'], $args['beanId'], $args['userId']);
    }

    /**
     * Factory method for Reminder class.
     *
     * @return \Sugarcrm\Sugarcrm\Trigger\Reminder
     * @codeCoverageIgnore
     */
    protected function getReminder()
    {
        $class = SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Trigger\Reminder');
        return new $class();
    }
}
