<?php

if(!defined('sugarEntry') && !sugarEntry) die('Not A Valid Entry Point');

if(!isset($_REQUEST['case_id'])){
	sugar_die('Please specify a case id');
}

require_once('modules/Cases/Case.php');
require_once('custom/si_custom_files/caseScoringFunctions.php');

$final_score_arr = siCaseScore($_REQUEST['case_id'], 'return', true);
$final_score_arr[$_REQUEST['case_id']] = floor($final_score_arr[$_REQUEST['case_id']]);

echo "<PRE>
final_score = ( (case_age + note_sum) * case_priority_level * case_support_level * account_type )
            + closed_defects
            + cases_in_x_days
            + account_open_cases
            + opportunity_sum
            + total_subscriptions
            + subscriptions_perpetual
            + case_survey_total

</PRE>";

global $caseScoreMessages;
echo "<table border=1>\n";
echo "<tr>\n";
echo "<td>Final Score</td>\n";
echo "<td>{$final_score_arr[$_REQUEST['case_id']]}</td>\n";
echo "</tr>";
foreach($caseScoreMessages[$_REQUEST['case_id']] as $index => $message){
	echo "<tr>\n";
	echo "<td>$index</td>\n";
	echo "<td>$message</td>\n";
	echo "</tr>\n";
}
echo "</table>\n";
