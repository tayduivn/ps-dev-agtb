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

class SugarFieldWorklog extends SugarFieldBase
{
    /**
     * @inheritdoc
     */
    public function __construct($type)
    {
        // see SugarFieldTag.php for reason to do this
        $this->needsSecondaryQuery = true;

        parent::__construct($type);
    }

    /**
     * Makes the data to send through api to show in record view, all entry formating on the front-end should be done
     * within this step.
     * {@inheritdoc}
     */
    public function apiFormatField(array &$data, SugarBean $bean, array $args, $fieldName, $properties, array $fieldList = null, ServiceBase $service = null)
    {
        $timeFormat = new TimeDate();
        $bean->load_relationship('worklog_link');

        if (!$bean->worklog_link) {
            return;
        }

        // Although docs of getBeans said to use 'order_by', it is actually 'orderby'
        $msg_beans = $bean->worklog_link->getBeans(array('orderby' => 'date_entered'));
        foreach ($msg_beans as $msg_bean) {
            $author_name = $this->getAuthorName($msg_bean->modified_user_id);

            $data[$fieldName][] = array(
                'author_name' => $author_name,
                'author_link' => $author_name == '' ? '' :
                    '#bwc/index.php?action=DetailView&module=Employees&record=' . $msg_bean->modified_user_id,
                'date_entered' => $timeFormat->to_display_date_time($msg_bean->date_entered),
                'entry'=> $this->toDisplayFormat($msg_bean->entry),
            );
        }
    }

    /**
     * Turns $entry to user display format
     * @param string $entry
     * @return The formatted $entry
     * NOTE: Serving as a space for further expansion for different display option,
     *       returning same string entry now
     */
    private function toDisplayFormat(string $entry)
    {
        return $entry;
    }

    /**
     * @param string $user_id The id of the user
     * @return string The full name of the user, in the format what user has setup.
     *                If the user has been deleted, returns empty string.
     */
    private function getAuthorName(string $user_id)
    {
        $userBean = BeanFactory::retrieveBean('Users', $user_id);
        return $userBean && $userBean->id === $user_id ? $userBean->full_name : '';
    }

    /**
     * Override of parent apiSave to force the custom save to be run from API
     * @param SugarBean $bean
     * @param array     $params
     * @param string    $field
     * @param array     $properties
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties)
    {
        if (!isset($params['worklog']) || !is_string($params['worklog'])) {
            return; // don't do anything it nothing is entered
        }

        $worklog_bean = BeanFactory::newBean('Worklog');
        $worklog_bean->setEntry($params['worklog']);
        $worklog_bean->setModule($bean->getModuleName());
        $worklog_bean->save();

        $bean->load_relationship('worklog_link');
        if (!$bean->worklog_link->add($worklog_bean)) {
            LoggerManager::getLogger()->fatal("Failed to add worklog");
        }
    }
}
