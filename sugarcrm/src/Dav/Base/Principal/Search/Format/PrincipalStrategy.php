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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format;

/**
 * Format sugar bean to module principal path
 * Class PrincipalStrategy
 * @package Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format
 */
class PrincipalStrategy implements StrategyInterface
{
    /**
     * Principal prefix path
     * @var string
     */
    protected $prefixPath;

    /**
     * @param $prefixPath
     */
    public function __construct($prefixPath = '')
    {
        if ($prefixPath && strrpos($prefixPath, '/') !== strlen($prefixPath) - 1) {
            $prefixPath .= '/';
        }
        $this->prefixPath = $prefixPath;
    }

    /**
     * Format SugarBean info in needed format
     * @param \SugarBean $bean
     * @return string
     */
    public function formatUri(\SugarBean $bean)
    {
        return $this->prefixPath . $bean->id;
    }

    /**
     * Format SugarBean info in needed extended format such as Module/id/username/full_name/email
     * @param \SugarBean $bean
     * @return array
     */
    public function formatBody(\SugarBean $bean)
    {
        return array(
            'id' => $bean->id,
            'uri' => $this->formatUri($bean),
            '{DAV:}displayname' => $bean->full_name,
            '{http://sabredav.org/ns}email-address' => $bean->email1,
        );
    }
}
