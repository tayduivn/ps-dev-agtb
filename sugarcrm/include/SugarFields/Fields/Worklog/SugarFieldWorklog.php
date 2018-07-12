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
    public function __construct($type) {
        // see SugarFieldTag.php for reason to do this
        $this->needsSecondaryQuery = true;

        parent::__construct($type);
    }

    /**
     * Makes the data to send through api to show in record view, all entry formating on the front-end should be done
     * within this step.
     * {@inheritdoc}
     */
    public function apiFormatField(array &$data, SugarBean $bean, array $args, $fieldName, $properties, array $fieldList = null, ServiceBase $service = null) {
    }

    /**
     * Override of parent apiSave to force the custom save to be run from API
     * @param SugarBean $bean
     * @param array     $params
     * @param string    $field
     * @param array     $properties
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties) {
    }
}
