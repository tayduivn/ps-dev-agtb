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


class Users extends Base
{
    protected $moduleName = 'Users';

    /**
     * @inheritdoc
     */
    protected function formatPrincipalString(\SugarBean $bean)
    {
        return $this->prefixPath . $bean->user_name;
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
}
