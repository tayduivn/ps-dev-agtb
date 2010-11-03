<?php

global $leadQueryDictionary;

$leadQueryDictionary = array(
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=5ae63a08-8518-096a-9b87-483f249258db
	'raw_leads' => "SELECT COUNT(*) count
FROM leads
WHERE leads.date_entered>='__start_date__' AND 
      leads.date_entered<='__end_date__' AND
      leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=129b59c6-57b2-3332-c305-48458cdb56ea
	'events' => "SELECT COUNT(*) count
FROM leads
 INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
 INNER JOIN  users l2 ON l2.id= leads.modified_user_id AND l2.deleted=0

WHERE leads.date_entered>'__start_date__'
 AND  leads.date_entered<'__end_date__'
 AND l1.id IN ('21030676-7f66-df76-8afb-44adcda44c25')
 AND l2.id IN ('17f31d92-20ba-487b-3741-44a88c4017aa')
 AND  leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=c1c749ba-2f24-e51e-efed-483f257ef7b3
	'junk_filter_grouped' => "SELECT l2.department l2_department
,l2.user_name l2_user_name
,COUNT(*) count
FROM leads
 INNER JOIN  users l1 ON
l1.id= leads.assigned_user_id AND l1.deleted=0
LEFT JOIN  users l2 ON
l2.id= leads.modified_user_id AND l2.deleted=0

WHERE leads.date_entered>'__start_date__'
 AND  leads.date_entered<'__end_date__'
 AND l1.id IN ('21030676-7f66-df76-8afb-44adcda44c25')
 
AND  leads.deleted=0 
 GROUP BY  IFNULL(l2.department 
,''), IFNULL(l2.user_name
,'') ORDER BY l2_department 
 ASC,l2_user_name 
 ASC",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=c1c749ba-2f24-e51e-efed-483f257ef7b3
	'junk_filter_total' => "SELECT COUNT(*) count
FROM leads
 INNER JOIN  users l1 ON
l1.id= leads.assigned_user_id AND l1.deleted=0
LEFT JOIN  users l2 ON
l2.id= leads.modified_user_id AND l2.deleted=0

WHERE leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND l1.id IN ('21030676-7f66-df76-8afb-44adcda44c25')
 AND  leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=f08c016c-e692-323b-267f-483f261792b0
	'child_filter' => "SELECT COUNT(*) count
FROM leads
LEFT JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c

 WHERE leads.date_entered>'__start_date__' AND
       leads.date_entered<'__end_date__' AND
       leads_cstm.lead_relation_c = 'Child' AND
       leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=282ceb5d-01b0-afeb-9600-483f26e6dcb4
	'installer' => "SELECT COUNT(*) count, leads.status
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
WHERE leads.date_entered>'__start_date__' AND
      leads.date_entered<'__end_date__' AND
       l1.id IN ('cef7c0a7-4ab0-ae95-2200-4342a4f55812') AND
       leads.status in ('Recycled', 'New', 'Converted - Existing') AND
       leads.deleted=0 
group by leads.status",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=7ecbf4d1-b6ba-fbc2-d7be-484428578b8f
	'partner_filter' => "SELECT COUNT(*) count
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
           INNER JOIN leads_cstm on leads.id = leads_cstm.id_c
WHERE leads.date_entered>'__start_date__' AND
      leads.date_entered<'__end_date__' AND
       l1.id IN ('2c780a1f-1f07-23fd-3a49-434d94d78ae5') AND
       leads_cstm.lead_relation_c != 'Child' AND
       leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=574e6234-b6cf-37c2-598b-483f02f61f13
	'unscrubbed' => "SELECT l1.user_name l1_user_name, COUNT(*) count
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
           INNER JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c
WHERE l1.id IN ('ebdd06a4-6794-f03a-c0f8-4460e9bde0d8','b73f0af6-c9b7-f485-32f7-4782e5af0c62','c15afb6d-a403-b92a-f388-4342a492003e')
 AND leads.status IN ('New','Auto Welcome')
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND leads_cstm.lead_relation_c IN ('Parent','Unknown')
 AND  leads.deleted=0 
GROUP BY  IFNULL(l1.user_name,'')
ORDER BY l1_user_name ASC",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=570779a2-522e-d75a-41d1-483f23663223
	'recycled' => "SELECT COUNT(*) count
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
WHERE leads.status IN ('Nurture','Recycled')
 AND l1.id IN ('ebdd06a4-6794-f03a-c0f8-4460e9bde0d8','b73f0af6-c9b7-f485-32f7-4782e5af0c62','c15afb6d-a403-b92a-f388-4342a492003e','21030676-7f66-df76-8afb-44adcda44c25')
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND  leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=a82a310c-1b21-261c-156d-4845889fa816
	'nurture' => "SELECT COUNT(*) count
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0 LEFT JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c
WHERE leads.status IN ('Nurture')
 AND l1.id IN ('a487d475-e85a-254d-b0ad-46ae82b3df2a','bbd47eb0-9b94-4c9b-0d83-4693ba697909','4636add8-95c2-322e-9db3-47df040851df','42e72b96-a75f-5aaa-7500-46113ddea84e','32a66602-0b79-671b-2d9e-473e274f8d42','d723297f-3589-09b0-f47e-47b228b75385','9c41d3f4-4933-2095-8d2b-470161c8df96','2bcc4d66-e5f0-9b27-39b6-47c86bfb14e4','584fde86-82c4-a3be-7ba4-4824bf21d2a9','bef39034-2c14-3c53-5e32-47f274d7598c')
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND leads_cstm.lead_relation_c IN ('Child')
 AND leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=8842f83b-afca-be52-7e15-48458082e953
	'dead_junk' => "SELECT COUNT(*) count
FROM leads
 INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
 INNER JOIN  users l2 ON l2.id= leads.modified_user_id AND l2.deleted=0
WHERE leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND l1.id IN ('21030676-7f66-df76-8afb-44adcda44c25')
 AND leads.status IN ('New','Auto Welcome','Qualifying','Assigned','Converted','Nurture','In Process','Recycled','Assigned - Existing','Assigned - Overflow','Converted - Existing','Requalify','Dupe','Dead')
 AND l2.department LIKE '%Qual%'
 AND  leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=505b47eb-0d51-fd11-2d17-483f27323552
	'scrubbed_to_emea' => "SELECT COUNT(*) count
FROM leads INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
           INNER JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c
WHERE l1.id IN ('5f796a6d-608d-e690-33ff-47ec07a283e4','8717f228-5bce-378d-8bef-4705fae265af')
 AND leads_cstm.lead_relation_c IN ('Parent','Unknown')
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND leads.deleted=0 ",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=25e4319a-5221-52f1-4491-482b5a319f7e
	'qual_backlog' => "SELECT COUNT(*) count, l1.user_name
FROM leads
 INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
 INNER JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c
WHERE l1.id IN ('a487d475-e85a-254d-b0ad-46ae82b3df2a','bbd47eb0-9b94-4c9b-0d83-4693ba697909','4636add8-95c2-322e-9db3-47df040851df','42e72b96-a75f-5aaa-7500-46113ddea84e','32a66602-0b79-671b-2d9e-473e274f8d42','d723297f-3589-09b0-f47e-47b228b75385','9c41d3f4-4933-2095-8d2b-470161c8df96','2bcc4d66-e5f0-9b27-39b6-47c86bfb14e4','584fde86-82c4-a3be-7ba4-4824bf21d2a9','bef39034-2c14-3c53-5e32-47f274d7598c')
 AND leads.status IN ('New','Auto Welcome','Qualifying','In Process','Assigned - Existing','Requalify','Assigned')
 AND leads_cstm.lead_relation_c = 'Parent'
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND  leads.deleted=0 
GROUP BY l1.user_name",
	
	// https://sugarinternal.sugarondemand.com/index.php?action=ReportCriteriaResults&module=Reports&page=report&id=d719dee8-e665-f2d5-762d-483f216d0f1c
	'recycled_junk' => "SELECT COUNT(*) count, leads.status
FROM leads
 INNER JOIN  users l1 ON l1.id= leads.assigned_user_id AND l1.deleted=0
 INNER JOIN leads_cstm leads_cstm ON leads.id = leads_cstm.id_c
WHERE leads.status IN ('Converted','Nurture','Recycled','Converted - Existing','Dupe','Dead')
 AND l1.id IN ('a487d475-e85a-254d-b0ad-46ae82b3df2a','bbd47eb0-9b94-4c9b-0d83-4693ba697909','4636add8-95c2-322e-9db3-47df040851df','42e72b96-a75f-5aaa-7500-46113ddea84e','32a66602-0b79-671b-2d9e-473e274f8d42','d723297f-3589-09b0-f47e-47b228b75385','9c41d3f4-4933-2095-8d2b-470161c8df96','2bcc4d66-e5f0-9b27-39b6-47c86bfb14e4','584fde86-82c4-a3be-7ba4-4824bf21d2a9','bef39034-2c14-3c53-5e32-47f274d7598c')
 AND leads.date_entered>'__start_date__'
 AND leads.date_entered<'__end_date__'
 AND leads_cstm.lead_relation_c IN ('Parent','Unknown')
 AND leads.deleted=0 
GROUP BY leads.status",
	
	// https://sugarinternal.sugarondemand.com/index.php?module=Leads&action=LeadPassReport
	'lead_pass_report' => "select count(*) count, foo.lead_status lead_status from (
  select count(*) count,
         leads.status lead_status
  from leads inner join leads_cstm on leads.id = leads_cstm.id_c
             inner join leads_audit on leads.id = leads_audit.parent_id
             inner join users leads_audit_user on leads_audit.created_by = leads_audit_user.id
             inner join users leads_user on leads.assigned_user_id = leads_user.id
  where
    leads_audit.field_name = 'lead_pass_c' AND
    leads_audit.before_value_string = 0 AND
    leads_audit.after_value_string = 1 AND
    leads_cstm.lead_pass_c = 1 AND
    leads_cstm.lead_relation_c = 'Parent' AND
    leads.deleted = 0 AND
    leads_cstm.lead_group_c in ('Inside', 'Corporate', 'Enterprise') AND
    leads_audit.date_created between '__start_date__' and '__end_date__'
  group by leads_audit.parent_id, leads_audit.created_by, leads_audit.before_value_string, leads_audit.after_value_string
) foo group by foo.lead_status",
);
