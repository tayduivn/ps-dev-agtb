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
  'name' => 'Zoominfo - Person',
  'order' => 4,
  'properties' => 
  array (
    'person_search_url' => 'http://partners.zoominfo.com/PartnerAPI/XmlOutput.aspx?query_type=people_search_query&pc=',
    'person_detail_url' => 'http://partners.zoominfo.com/PartnerAPI/XmlOutput.aspx?query_type=person_detail&pc=',
    'partner_code' => '',
    'api_key' => '',
  ),
);

//BEGIN SUGARCRM flav=int ONLY
$config['properties']['partner_code'] = 'Sugarcrm';
$config['properties']['api_key'] = 'zihel20n9';
//END SUGARCRM flav=int ONLY
?>
