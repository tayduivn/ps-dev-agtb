<?php
$dictionary['Email']['fields']['meetings'] = array(
                        'name'                  => 'meetings',
                        'vname'                 => 'LBL_EMAILS_MEETINGS_REL',
                        'type'                  => 'link',
                        'relationship'  => 'emails_meetings_rel',
                        'module'                => 'Meetings',
                        'bean_name'             => 'Meeting',
                        'source'                => 'non-db',
);
$dictionary['Email']['relationships']['emails_meetings_rel'] = array(
                       'lhs_module'    		 => 'Emails',
                        'lhs_table'          => 'emails',
                        'lhs_key'            => 'id',
                        'rhs_module'         => 'Meetings',
                        'rhs_table'          => 'meetings',
                        'rhs_key'            => 'parent_id',
                        'relationship_type'  => 'one-to-many',
);