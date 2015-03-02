<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarFields/Fields/Relate/SugarFieldRelate.php';

/**
 * Class to handle Nested Set field for sugar logic.
 */
class SugarFieldNestedset extends SugarFieldRelate
{

    /**
     * {@inheritDoc}
     */
    public function apiFormatField(
        array &$data,
        SugarBean $bean,
        array $args,
        $fieldName,
        $properties,
        array $fieldList = null,
        ServiceBase $service = null
    ) {
        $this->ensureApiFormatFieldArguments($fieldList, $service);

        /*
         * If we have a related field, use its formatter to format it
         */
        $rbean = BeanFactory::getBean($properties['category_provider'], $bean->$properties['id_name']);
        if (!empty($rbean->id)) {
            if (empty($rbean->field_defs[$properties['rname']])) {
                $data[$fieldName] = '';
                return;
            }
            $rdefs = $rbean->field_defs[$properties['rname']];
            if (!empty($rdefs) && !empty($rdefs['type'])) {
                $sfh = new SugarFieldHandler();
                $field = $sfh->getSugarField($rdefs['type']);
                $rdata = array();
                $field->apiFormatField($rdata, $rbean, $args, $properties['rname'], $rdefs, $fieldList, $service);
                $data[$fieldName] = $rdata[$properties['rname']];
                if (!empty($data[$fieldName])) {
                    return;
                }
            }
        }
        if (empty($bean->$fieldName)) {
            $data[$fieldName] = '';
        } else {
            $data[$fieldName] = $this->formatField($bean->$fieldName, $properties);
        }
    }
}
