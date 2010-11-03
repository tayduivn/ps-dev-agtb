<?php
/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #14114:
** Description: keeping references to leadcontacts and leadaccounts instead of leads.
*/
if (isset($dictionary['ProspectList']['fields']) && isset($dictionary['ProspectList']['fields']['leads']))
unset($dictionary['ProspectList']['fields']['leads']);


$dictionary['ProspectList']['fields']['leadcontacts'] =
          array (
                'name' => 'leadcontacts',
                'type' => 'link',
                'relationship' => 'prospect_list_lead_contacts',
                'source'=>'non-db',
                );
?>
