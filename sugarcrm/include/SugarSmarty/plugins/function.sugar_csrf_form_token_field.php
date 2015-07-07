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

use Sugarcrm\Sugarcrm\Security\Csrf\CsrfAuthenticator;

/**
 * Return the configured CSRF form token field name.
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_sugar_csrf_form_token_field($params, &$smarty)
{
    return CsrfAuthenticator::getInstance()->getFormTokenField();
}

