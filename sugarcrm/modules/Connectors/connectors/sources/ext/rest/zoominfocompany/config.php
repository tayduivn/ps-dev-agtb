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
$config = array (
  'name' => 'Zoominfo - Company',
  'order' => 3,
  'properties' => 
  array (
    'company_search_url' => 'http://partners.zoominfo.com/PartnerAPI/XmlOutput.aspx?query_type=company_search_query&pc=',
    'company_detail_url' => 'http://partners.zoominfo.com/PartnerAPI/XmlOutput.aspx?query_type=company_detail&pc=',
    'partner_code' => '',
    'api_key' => '',
  ),
);

//BEGIN SUGARCRM flav=int ONLY
$config['properties']['partner_code'] = 'Sugarcrm';
$config['properties']['api_key'] = 'zihel20n9';
//END SUGARCRM flav=int ONLY

?>
