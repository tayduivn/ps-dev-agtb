<?php

global $leadQueryDictionary;

$leadQueryDictionary = array(
	
	'raw_leads' => "
SELECT count(*) count
FROM touchpoints
WHERE touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__' AND touchpoints.deleted=0 
    ",
	
	'junk_filter' => "
SELECT count(*) count
FROM touchpoints
WHERE touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__' AND touchpoints.scrub_relation_type = 'touchpoint' AND touchpoints.deleted=0 
    ",
	
	'child_filter' => "
SELECT count(*) count
FROM touchpoints
WHERE touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__' AND touchpoints.scrub_relation_type = 'interaction' AND touchpoints.deleted=0 
    ",
	
	'channel_filter' => array(
		"select count(*) count from touchpoints
where scrubbed = 0 and assigned_user_id='2c780a1f-1f07-23fd-3a49-434d94d78ae5' and deleted=0
and touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__'
        ",
		"select count(*) count from leadcontacts
where assigned_user_id='2c780a1f-1f07-23fd-3a49-434d94d78ae5' and deleted=0
and leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__'
        ",
	),
	
	'installer_no_scrub' => array(
		"select count(*) count from touchpoints
where scrubbed=0 and assigned_user_id='cef7c0a7-4ab0-ae95-2200-4342a4f55812'
and touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__'
        ",
		"select count(*) count from leadcontacts
where assigned_user_id='cef7c0a7-4ab0-ae95-2200-4342a4f55812' and deleted=0
and leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__'
        ",
	),
	
	'emea' => array(
		"select count(*) count from touchpoints
where scrubbed=0 and assigned_user_id='6219c047-1547-89a4-cf86-488921e95887' and department like '%EMEA%'
and touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__'
        ",
		"select count(*) count from leadcontacts
where assigned_user_id='6219c047-1547-89a4-cf86-488921e95887' and department like '%EMEA%'
and leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__'
        ",
    ),
	
	'converted_existing_new' => array(
		"select count(*) count from leadcontacts LEFT JOIN leadaccounts_cstm on leadaccounts_cstm.id_c = leadaccount_id
where (leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__') AND (leadcontacts.status IN ('New','Converted - Existing')) AND leadcontacts.deleted=0 AND (
(
  leadcontacts.assigned_user_id in 
     ('c68f98a9-7030-b563-1633-4755ff7d9ac5','6219c047-1547-89a4-cf86-488921e95887','5f796a6d-608de690-33ff-47ec07a283e4','8717f228-5bce-378d-8bef-4705fae265af','912da741-09eb-bcf8-9329-45d9f7520350')
  AND leadaccounts_cstm.lead_pass_c in ('on','1')
)
OR
(
(leadaccounts_cstm.lead_pass_c is null OR leadaccounts_cstm.lead_pass_c='0')
AND (leadcontacts.assigned_user_id not in ('cef7c0a7-4ab0-ae95-2200-4342a4f55812','21030676-7f66-df76-8afb-44adcda44c25','2c780a1f-1f07-23fd-3a49-434d94d78ae5'))
)
)",
		"select count(*) count from touchpoints where scrubbed=0 and assigned_user_id not in ('cef7c0a7-4ab0-ae95-2200-4342a4f55812','21030676-7f66-df76-8afb-44adcda44c25','2c780a1f-1f07-23fd-3a49-434d94d78ae5') AND (touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__')",
    ),
	
	'nurture' => array(
		"select count(*) count from leadcontacts LEFT JOIN leadaccounts_cstm on leadaccounts_cstm.id_c = leadaccount_id where (leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__') AND (leadcontacts.status IN ('Nurture','Recycled')) AND leadcontacts.deleted=0 AND
(
  ( leadcontacts.assigned_user_id in ('c68f98a9-7030-b563-1633-4755ff7d9ac5','6219c047-1547-89a4-cf86-488921e95887','5f796a6d-608de690-33ff-47ec07a283e4','8717f228-5bce-378d-8bef-4705fae265af','912da741-09eb-bcf8-9329-45d9f7520350') AND leadaccounts_cstm.lead_pass_c in ('on','1')
)
OR
(
(leadaccounts_cstm.lead_pass_c is null OR leadaccounts_cstm.lead_pass_c='0')
AND (leadcontacts.assigned_user_id not in ('cef7c0a7-4ab0-ae95-2200-4342a4f55812','21030676-7f66-df76-8afb-44adcda44c25','2c780a1f-1f07-23fd-3a49-434d94d78ae5'))
)
)",
		"select count(*) count from
touchpoints
where touchpoints.scrubbed=0
and touchpoints.assigned_user_id not in ('cef7c0a7-4ab0-ae95-2200-4342a4f55812','21030676-7f66-df76-8afb-44adcda44c25','2c780a1f-1f07-23fd-3a49-434d94d78ae5')
AND (touchpoints.date_entered>='__start_date__' AND touchpoints.date_entered<='__end_date__')
        ",
    ),
	
	'leads_active' => "select count(*) count
from leadcontacts
LEFT JOIN leadaccounts_cstm on leadaccounts_cstm.id_c = leadcontacts.leadaccount_id
where
leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__'
AND (leadcontacts.status IN ('Auto Welcome','Qualifying','Assigned','Converted','In Process','Assigned - Existing','Assigned - Overflow','Requalify','Dupe','Dead'))
AND leadcontacts.assigned_user_id not in ('6219c047-1547-89a4-cf86-488921e95887', '5f796a6d-608d-e690-33ff-47ec07a283e4', '8717f228-5bce-378d-8bef-4705fae265af', '912da741-09eb-bcf8-9329-45d9f7520350', 'cef7c0a7-4ab0-ae95-2200-4342a4f55812', '21030676-7f66-df76-8afb-44adcda44c25', '2c780a1f-1f07-23fd-3a49-434d94d78ae5')
AND leadcontacts.deleted=0
AND (leadaccounts_cstm.lead_pass_c is null or leadaccounts_cstm.lead_pass_c ='0')
    ",
	
	'funnel_backlog' => array(
		"select count(*) count
from touchpoints
WHERE touchpoints.scrubbed=0
AND touchpoints.assigned_user_id in ('ebdd06a4-6794-f03a-c0f8-4460e9bde0d8','b73f0af6-c9b7-f485-32f7-4782e5af0c62','c15afb6d-a403-b92a-f388-4342a492003e')
AND touchpoints.deleted=0
        ",
		"select count(*) count
from leadcontacts
WHERE
leadcontacts.assigned_user_id in ('ebdd06a4-6794-f03a-c0f8-4460e9bde0d8','b73f0af6-c9b7-f485-32f7-4782e5af0c62','c15afb6d-a403-b92a-f388-4342a492003e')
AND leadcontacts.deleted=0
AND leadcontacts.status='New'
        ",
    ),
	
	'funnel_validation' => "
    select count(*) count
from leadcontacts
LEFT JOIN leadaccounts_cstm on leadaccounts_cstm.id_c = leadcontacts.leadaccount_id
WHERE
leadcontacts.date_entered>='__start_date__' AND leadcontacts.date_entered<='__end_date__'
AND leadaccounts_cstm.lead_pass_c in ('on','1')
AND leadcontacts.deleted=0
AND leadcontacts.assigned_user_id not in ('cef7c0a7-4ab0-ae95-2200-4342a4f55812', '21030676-7f66-df76-8afb-44adcda44c25', '2c780a1f-1f07-23fd-3a49-434d94d78ae5')
    ",
	
	'lead_pass_report' => "
    ",
);
