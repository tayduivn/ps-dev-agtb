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

use Sugarcrm\Sugarcrm\MetaData\ViewdefManager;
use Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions;

class QuotesConfigApi extends ConfigModuleApi
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function registerApiRest()
    {
        return
            array(
                'quotesConfigCreate' => array(
                    'reqType' => 'POST',
                    'path' => array('Quotes', 'config'),
                    'pathVars' => array('module', ''),
                    'minVersion' => '11.2',
                    'method' => 'configSave',
                    'shortHelp' => 'Save the config settings for the Quotes Module',
                    'longHelp' => 'modules/Quotes/clients/base/api/help/config_post_help.html',
                ),
            );
    }

    /**
     * Quotes Override since we have custom logic that needs to be ran
     *
     * {@inheritdoc}
     */
    public function configSave(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('worksheet_columns', 'worksheet_columns_related_fields'));
        $settings = parent::configSave($api, $args);
        $this->applyConfig();
                
        return $settings;
    }

    /**
     * Applies the saved Quotes config.
     * This might be necessary to run independently of the config api for cases like updating the
     * quote record view in studio -- that would remove any related_fields updates we made here.
     *
     * @throws SugarApiExceptionInvalidParameter
     */
    public function applyConfig()
    {
        $viewdefManager = $this->getViewdefManager();
        $settings = $this->getSettings();

        if (!array_key_exists('worksheet_columns', $settings) || !is_array($settings['worksheet_columns'])) {
            throw new \SugarApiExceptionInvalidParameter($GLOBALS['app_strings']['EXCEPTION_MISSING_WORKSHEET_COLUMNS']);
        }

        if (!array_key_exists('worksheet_columns_related_fields', $settings) ||
            !is_array($settings['worksheet_columns_related_fields'])) {
            throw new \SugarApiExceptionInvalidParameter($GLOBALS['app_strings']['EXCEPTION_MISSING_WORKSHEET_COLUMNS_RELATED_FIELDS']);
        }

        //update products c/b/v/quote-data-group-list with new fields for worksheet_columns
        //load viewdefs
        $qlidatagrouplistdef = $viewdefManager->loadViewdef('base', 'Products', 'quote-data-group-list');

        //check to see if the key we need to update exists in the loaded viewdef, if not, load the base.
        $path = array('panels',0,'fields');
        if (!ArrayFunctions::keyExistsInPath($path, $qlidatagrouplistdef)) {
            $qlidatagrouplistdef = $viewdefManager->loadViewdef('base', 'Products', 'quote-data-group-list', true);
        }

        $qlidatagrouplistdef['panels'][0]['fields'] = $settings['worksheet_columns'];
        $viewdefManager->saveViewdef($qlidatagrouplistdef, 'Products', 'base', 'quote-data-group-list');

        //update quotes c/b/v/record.php name:related_fields, bundles and product_bundle_items with everything added
        //and anything needed for calculating fields -- include any new dependent fields
        //load viewdefs
        $quoteRecordViewdef = $viewdefManager->loadViewdef('base', 'Quotes', 'record');

        //check to see if the key we need to update exists in the loaded viewdef, if not, load the base.
        $path = array('panels',0,'fields',1,'related_fields', 'fields', 0);
        if (!ArrayFunctions::keyExistsInPath($path, $quoteRecordViewdef)) {
            $quoteRecordViewdef = $viewdefManager->loadViewdef('base', 'Quotes', 'record');
        }

        //now that we know the related_fields['fields'] exists, we need to search that array for the array def
        //for the product bundle items
        $fieldsIndex = 0;
        foreach ($quoteRecordViewdef['panels'][0]['fields'][1]['related_fields'][0]['fields'] as $field) {
            if (!is_array($field)) {
                $fieldsIndex++;
                continue;
            } else {
                if (array_key_exists('name', $field) && $field['name'] == 'product_bundle_items' && array_key_exists('fields', $field)) {
                    $quoteRecordViewdef['panels'][0]['fields'][1]['related_fields'][0]['fields'][$fieldsIndex]['fields'] = $settings['worksheet_columns_related_fields'];
                }
                break;
            }
        }

        //do the same as above for bundles when we're ready for that

        //write out new quotes record.php
        $viewdefManager->saveViewdef($quoteRecordViewdef, 'Quotes', 'base', 'record');
    }

    /**
     * abstraction for getting a new instance of ViewdefManager
     *
     * @return Sugarcrm\Sugarcrm\MetaData\ViewdefManager
     */
    protected function getViewdefManager()
    {
        return new ViewdefManager();
    }

    /**
     * abstraction of retreiving settings for the quotes module.
     *
     * @return array settings
     */
    protected function getSettings()
    {
        $admin = BeanFactory::newBean('Administration');
        return $admin->getConfigForModule('Quotes');
    }
}
