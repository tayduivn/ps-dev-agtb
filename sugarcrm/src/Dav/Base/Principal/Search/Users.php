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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

use Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format;

class Users extends Base
{
    protected $moduleName = 'Users';

    /**
     * @param string $prefixPath
     * @param Format\StrategyInterface|null $formatStrategy
     */
    public function __construct($prefixPath = '', Format\StrategyInterface $formatStrategy = null)
    {
        parent::__construct(
            $prefixPath,
            $formatStrategy ? $formatStrategy : new Format\UserPrincipalStrategy($prefixPath)
        );
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalByIdentify($identify)
    {
        $bean = $this->getBean();
        $userID = $bean->retrieve_user_id($identify);
        if (!$userID) {
            return array();
        }

        return parent::getPrincipalByIdentify($userID);
    }

    /**
     * @inheritdoc
     */
    public static function getOrder()
    {
        return 300;
    }
}
