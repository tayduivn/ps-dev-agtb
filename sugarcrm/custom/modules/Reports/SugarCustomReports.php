<?php

if(!defined('sugarEntry')) die("Not a valid entry point");

echo "<h2>Sugar Custom Reports</h2>\n<BR>\n";

$reportsMeta = array(
	'Executive' => array(
	),
	'Sales' => array(
		'LeadPassReport' => array(
			'name' => 'Lead Pass Report',
			'module' => 'Leads',
			'action' => 'LeadPassReport',
			'description' => 'This report will calculate the lead passes with summary information for all members of the lead qual team.',
			'additionalParams' => '',
		),
		'LeadScrubReport' => array(
			'name' => 'Lead Scrub Report',
			'module' => 'Leads',
			'action' => 'LeadScrubReport',
			'description' => 'This report will calculate the leads scrubbed with summary information for all members of the lead qual team.',
			'additionalParams' => '',
		),
		'LeadFunnelReport' => array(
			'name' => 'Lead Funnel Report',
			'module' => 'Leads',
			'action' => 'LeadFunnelReport',
			'description' => 'This is a report that does analysis / gives stats on the Leads that entered our system and how they made it through the pipeline.',
			'additionalParams' => '',
		),
		'RoundRobinToggle' => array(
			'name' => 'Round Robin Toggle',
			'module' => 'Leads',
			'action' => 'RoundRobinToggle',
			'description' => 'This is a script that allows the sales managers to enable and disable users from being in the round robin queue.',
			'additionalParams' => '',
		),
		'LN_Subscriber_Info' => array(
			'name' => 'Subscriber Info',
			'module' => 'ReportMaker',
			'action' => 'ReportView',
			'description' => 'This is an Enterprise Report providing a summary of all our subscriptions, including new, additional, renewal, and lost.',
			'additionalParams' => '&record=8205ee24-02fb-ce43-07c2-4429f705ec6b',
		),
	),
	'Sales Operations' => array(
		'MassCloseOpportunities' => array(
			'name' => 'Mass Close Opportunities (Sales Ops)',
			'module' => 'Opportunities',
			'action' => 'MassCloseOpportunitiesSalesOps',
			'description' => 'This will allow Sales Ops to close and/or reject all pending Sales Rep Closed Opportunities.',
			'additionalParams' => '',
		),
	),
	'Accounting' => array(
		'MassCloseOpportunities' => array(
			'name' => 'Mass Close Opportunities (Accounting)',
			'module' => 'Opportunities',
			'action' => 'MassCloseOpportunitiesFinance',
			'description' => 'This will allow Finance to close and/or reject all pending Sales Ops Closed Opportunities.',
			'additionalParams' => '',
		),
	),
	'Finance' => array(
		'FinancePipeline' => array(
			'name' => 'Finance Pipeline (Summary)',
			'module' => 'Opportunities',
			'action' => 'FinancePipeline',
			'description' => 'This is a report outlining a summary of the pipeline for a given quarter.',
			'additionalParams' => '',
		),
		'FinancePipelineDetails' => array(
			'name' => 'Finance Pipeline (Details)',
			'module' => 'Opportunities',
			'action' => 'FinancePipelineDetails',
			'description' => 'This is a report outlining the details of the pipeline for a given quarter.',
			'additionalParams' => '',
		),
	),
	'Engineering' => array(
		'BugOpenClosed' => array(
			'name' => 'Bug Open Closed Report',
			'module' => 'Bugs',
			'action' => 'bugsopenedclosed',
			'description' => 'This gives various details about open and closed bugs for any given timeframe.',
			'additionalParams' => '',
		),
	),
	'Internal Systems' => array(
		'ITRequestsOpenClosed' => array(
			'name' => 'IT Request Open Closed Report',
			'module' => 'ITRequests',
			'action' => 'itrequestsopenedclosed',
			'description' => 'This gives various details about open and closed IT Requests for any given timeframe.',
			'additionalParams' => '',
		),
	),
);

$output =<<<EOQ
<table border="0" cellpadding="0" cellspacing="0" width="50%">
EOQ;
foreach($reportsMeta as $category => $reports){
	if(empty($reports))
		continue;
	
	$output .= "    <tr>\n    <th class='tabDetailViewDL'><b>$category</b></th>\n    <th class='tabDetailViewDF'>&nbsp;</th>    </tr>\n";
	
	foreach($reports as $metaInfo){
			$additional = empty($metaInfo['additionalParams']) ? '' : $metaInfo['additionalParams'];
			$output .= "    <tr>\n";
			$output .= "    <td class='tabDetailViewDL'>".
							"<a href=index.php?module={$metaInfo['module']}&action={$metaInfo['action']}$additional>{$metaInfo['name']}</a>".
							"</td>\n";
			$output .= "    <td class='tabDetailViewDF'>{$metaInfo['description']}</td>\n";
			$output .= "    </tr>\n";
	}
	
	$output .= "    <tr>\n    <td class='tabDetailViewDL'><b>&nbsp;</b></td>\n    <td class='tabDetailViewDF'>&nbsp;</td>    </tr>\n";
}
$output .= "</table>\n";

echo $output;
