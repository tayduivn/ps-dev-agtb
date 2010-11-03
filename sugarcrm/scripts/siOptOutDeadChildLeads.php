<?php

chdir('..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
$GLOBALS['log']->fatal("----->siOptOutDeadChildLeads.php started");
/* 
 * 2009-02-20 jmullan:
 * mysql seemed to really hate indexes when they were in subqueries.
 * After some crazed trial and error, I made use of a couple nicely indexed
 * temporary tables and mysql no longer tries to go through more than sixteen
 * billion rows
 */
$queries[] = "DROP TEMPORARY TABLE IF EXISTS `existing_opt_outs`, `filtered_lead_ids`";
$queries[] = "
CREATE TEMPORARY TABLE `existing_opt_outs` (
    `related_id` varchar(36) NOT NULL,
    PRIMARY KEY (`related_id`)
) ENGINE = MYISAM
SELECT DISTINCT
    `related_id`
    FROM
        `prospect_lists_prospects`
    WHERE
        `prospect_list_id` = '8aa699f9-91e1-387e-14c0-4831e5f783d8'
";
$queries[] = "
CREATE TEMPORARY TABLE `filtered_lead_ids` (
    `lead_id` varchar(36) NOT NULL,
    PRIMARY KEY (`lead_id`)
) ENGINE = MYISAM
SELECT DISTINCT
    `leads`.`id` AS `lead_id`
FROM
    `leads`
LEFT JOIN
    `leads_cstm` ON `leads`.`id` = `leads_cstm`.`id_c`
WHERE
    `leads`.`deleted` = 0
    AND ((`leads`.`status` = 'Dead') OR (`leads_cstm`.`lead_relation_c` = 'Child'))
";
$queries[] = "
INSERT INTO
    `prospect_lists_prospects`
SELECT
    uuid(),
    '8aa699f9-91e1-387e-14c0-4831e5f783d8',
    `filtered_lead_ids`.`lead_id`,
    'Leads',
    now(),
    0
FROM
    `filtered_lead_ids`
LEFT JOIN
    `existing_opt_outs` ON `filtered_lead_ids`.`lead_id` = `existing_opt_outs`.`related_id`
WHERE
    `existing_opt_outs`.`related_id` IS NULL
";
foreach ($queries as $query) {
    $res = $GLOBALS['db']->query($query);
    if (!$res) {
	$GLOBALS['db']->checkError('Error with query');
	$GLOBALS['log']->fatal("----->siOptOutDeadChildLeads.php query failed: " . $query);
	exit;
    }
}
$GLOBALS['log']->fatal("----->siOptOutDeadChildLeads.php finished");
