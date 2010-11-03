<?php

$bugsAndITRMeta[] = array(
	'comment' => '[module_display] opened during this timeframe',
	'query' => "
SELECT [table_name].id
FROM [table_name]
WHERE [table_name].deleted = 0
  AND [table_name].date_entered > '[start_date]'
  AND [table_name].date_entered < '[end_date]'",
);

$bugsAndITRMeta[] = array(
	'comment' => '[module_display] closed ONLY during this timeframe',
	'query' => "
SELECT [table_name].id
FROM [table_name] inner join [table_name]_audit on [table_name].id = [table_name]_audit.parent_id
WHERE [table_name].deleted = 0
  AND [table_name].status [closed_statuses]
  AND [table_name]_audit.after_value_string [closed_statuses]
  AND [table_name]_audit.field_name = [status_field]
GROUP BY [table_name].id
HAVING min([table_name]_audit.date_created) > '[start_date]'
   AND max([table_name]_audit.date_created) < '[end_date]'",
);

$bugsAndITRMeta[] = array(
	'comment' => '[module_display] that were closed in the past, reopened and the last close was within in this timeframe',
	'query' => "
SELECT [table_name].id
FROM [table_name] inner join [table_name]_audit on [table_name].id = [table_name]_audit.parent_id
WHERE [table_name].deleted = 0
  AND [table_name].status [closed_statuses]
  AND [table_name]_audit.after_value_string [closed_statuses]
  AND [table_name]_audit.field_name = [status_field]
GROUP BY [table_name].id
HAVING min([table_name]_audit.date_created) < '[start_date]'
   AND max([table_name]_audit.date_created) > '[start_date]'
   AND max([table_name]_audit.date_created) < '[end_date]'",
);

$bugsAndITRMeta[] = array(
	'comment' => '[module_display] that were first closed in this timeframe, and then closed again in the future',
	'query' => "
SELECT [table_name].id
FROM [table_name] inner join [table_name]_audit on [table_name].id = [table_name]_audit.parent_id
WHERE [table_name].deleted = 0
  AND [table_name].status [closed_statuses]
  AND [table_name]_audit.after_value_string [closed_statuses]
  AND [table_name]_audit.field_name = [status_field]
GROUP BY [table_name].id
HAVING min([table_name]_audit.date_created) > '[start_date]'
   AND min([table_name]_audit.date_created) < '[end_date]'
   AND max([table_name]_audit.date_created) > '[end_date]'",
);


