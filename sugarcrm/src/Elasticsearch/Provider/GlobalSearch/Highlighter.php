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
 * Plain Highlighter implementation
 *
 */
class PlainHighlighter extends AbstractHighlighter
{
    /**
     * Ctor
     * @param array $fields
     */
    public function __construct(array $fields = array())
    {
        $this->setFields($fields);
        $this->setRequiredFieldMatch(true);
        $this->setDefaultFieldArgs(array(
            'type' => 'plain',
            'force_source' => true,
            'require_field_match' => true,
        ));
    }
}
