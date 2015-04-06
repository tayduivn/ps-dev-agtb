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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;

/**
 *
 * Generic Mapping Handler using multi fields
 *
 */
class CrossModuleAggHandler extends AbstractHandler implements
    MappingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
        //check if it's defined as cross_module
        if ($this->isCrossModuleDefined($defs) === false) {
            return;
        }
        $aggDef = $defs['full_text_search']['aggregation'];

        $field = Mapping::PREFIX_SEP . $field;
        $type = ucfirst($aggDef['type']);
        if ($type == 'Terms') {
            $mapping->addNotAnalyzedField($field, true, true);
        } elseif ($type == 'DateRange') {
            $mHandler = new MultiFieldHandler();
            $mHandler->setCrossModuleEnabled(true);
            $mHandler->buildMapping($mapping, $field, $defs);
        }
    }
}
