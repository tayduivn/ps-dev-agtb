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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\AbstractHighlighter;

/**
 *
 * GlobalSearch Highlighter
 *
 */
class Highlighter extends AbstractHighlighter
{
    /**
     * Ctor
     */
    public function __construct()
    {
        // always require a field match by default
        $this->setRequiredFieldMatch(true);

        // default fragments
        $this->setNumberOfFrags(3);
        $this->setFragSize(20);

        // use _source and plain highlighter
        $this->setDefaultFieldArgs(array(
            'type' => 'plain',
            'force_source' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeFieldName($field)
    {
        // Strip of the module name and keep the main field only. If no match
        // is found we continue with the field value as is.
        if (preg_match('/^.*' . SearchFields::PREFIX_SEP . '([^.]*).*$/', $field, $matches)) {
            $field = $matches[1];
        }
        return parent::normalizeFieldName($field);
    }
}
