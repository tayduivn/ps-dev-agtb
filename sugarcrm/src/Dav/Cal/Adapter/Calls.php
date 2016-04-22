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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

/**
 * Class for processing Calls by iCal protocol
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Calls extends AdapterAbstract
{
    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     * @param \Call|\SugarBean $bean
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false)
    {
        $data = parent::prepareForExport($bean, $previousData);
        if ($data) {
            foreach ($data as &$item) {
                if (isset($item[1]['location'])) {
                    unset($item[1]['location']);
                    if (!$item[1] && !$item[2]) {
                        $item = null;
                    }
                }
            }
            unset($item);
            $data = array_filter($data);
        }
        return $data;
    }

    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     */
    public function prepareForRebuild(\SugarBean $bean, $previousData = false)
    {
        $data = parent::prepareForRebuild($bean, $previousData);
        if ($data) {
            foreach ($data as &$item) {
                if (isset($item[1]['location'])) {
                    unset($item[1]['location']);
                    if (!$item[1] && !$item[2]) {
                        $item = null;
                    }
                }
            }
            unset($item);
            $data = array_filter($data);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function export(&$data, \CalDavEventCollection $collection)
    {
        if (isset($data[1]['location'])) {
            unset($data[1]['location']);
        }
        return parent::export($data, $collection);
    }

    /**
     * Location should be ignored for Calls.
     * @inheritDoc
     * @param \Call|\SugarBean $bean
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false)
    {
        $data = parent::prepareForImport($collection, $previousData);
        if ($data) {
            foreach ($data as &$item) {
                if (isset($item[1]['location'])) {
                    unset($item[1]['location']);
                    if (!$item[1] && !$item[2]) {
                        $item = null;
                    }
                }
            }
            unset($item);
            $data = array_filter($data);
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function import(&$data, \SugarBean $bean)
    {
        if (isset($data[1]['location'])) {
            unset($data[1]['location']);
        }
        return parent::import($data, $bean);
    }
}
