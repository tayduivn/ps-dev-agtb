<?php
//FILE SUGARCRM flav=ent ONLY
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
function blowfishGetKey($param)
{
    if ($param == 'ProcessMaker') {
        return 'remote_51c8ade20cd0e';
    }
}

function blowfishDecode($key, $cypherText)
{
    $array = array("lic_status" => "VALID",
        "lic_activation_code" => "CSCI-ATAT-ESCD",
        "lic_type" => "COMMERCIAL",
        "lic_license_name" => "HD Supply",
        "lic_product_expiration_date" => "2013-06-30 00:00:00",
        "lic_support_expiration_date" => "2013-07-31 00:00:00",
        "lic_activations" => "2",
        "lic_max_activations" => "3",
        "lic_create_date" => "2013-06-11 16:41:18",
        "lic_revoke_date" => "0000-00-00 00:00:00",
        "lic_max_admins" => "1",
        "lic_max_users" => "10",
        "lic_max_cases" => "1000",
        "lic_enabled_br" => "1");
    return ($array);
}

