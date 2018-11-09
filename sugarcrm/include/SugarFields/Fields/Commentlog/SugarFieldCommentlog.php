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

class SugarFieldCommentlog extends SugarFieldBase
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
        $bean->load_relationship('commentlog_link');

        if (!$bean->commentlog_link) {
            return;
        }

        // Although docs of getBeans said to use 'order_by', it is actually 'orderby'
        $msg_beans = $bean->commentlog_link->getBeans(array('orderby' => 'date_entered'));
        $helper = new SugarBeanApiHelper($service);
        foreach ($msg_beans as $msg_bean) {
            if (!$msg_bean->created_by_name) {
                // when something is missing, force reload new bean
                $msg_bean = BeanFactory::retrieveBean('CommentLog', $msg_bean->id, array('use_cache' => false));
            }

            // newly created commentlog tends to not like to have created_by_link,
            // forcing everyone to load it
            $msg_bean->load_relationship('created_by_link');

            if (!$msg_bean->created_by_link) {
                continue;
            }

            $data[$fieldName][] = $helper->formatForApi($msg_bean, ['entry', 'created_by', 'date_entered', 'created_by_link', 'created_by_name']);
        }
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
        if (!isset($params['commentlog']) || !is_string($params['commentlog'])) {
            return; // don't do anything it nothing is entered
        }

        $commentlog_bean = BeanFactory::newBean('CommentLog');
        $commentlog_bean->setEntry($params['commentlog']);
        $commentlog_bean->setModule($bean->getModuleName());
        $commentlog_bean->save();

        $bean->load_relationship('commentlog_link');
        if (!$bean->commentlog_link->add($commentlog_bean)) {
            LoggerManager::getLogger()->fatal("Failed to add commentlog");
        }
    }
}
