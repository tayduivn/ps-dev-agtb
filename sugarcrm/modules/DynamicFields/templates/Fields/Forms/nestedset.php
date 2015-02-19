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

function get_body (&$ss, $vardef)
{
    $nestedBeans = $GLOBALS['mod_strings']['nestedBeans'];
    $beanRoots = array();
    $nestedBeans = SugarACL::filterModuleList($nestedBeans);
    foreach ($nestedBeans as $nestedBean) {
        $nb = BeanFactory::getBean($nestedBean);
        $beanRoots[$nestedBean] = $nb->getRoots();
    }
    $ss->assign('nestedBeans', $nestedBeans);
    $ss->assign('beanRoots', JSON::encode($beanRoots));

    return $ss->fetch('modules/DynamicFields/templates/Fields/Forms/nestedset.tpl');
}
