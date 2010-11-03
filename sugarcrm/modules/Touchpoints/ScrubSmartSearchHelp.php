<?php

require_once('custom/si_custom_files/LeadScrubSearcher.php');

$searcher = new LeadScrubSearcher();
$smartsearch_defs = $searcher->getSmartSearchDefinitions();

echo "Smart Search does searches against leads, contacts and accounts using the following information and returns a list of all the matches from the searches below.<BR>";
echo "<font size=2>* Note: Email Address/Domain means it will search against the full email address if it is from gmail, hotmail, aol, etc (see full list below). If it is not from a free email address provider, it will search against the email address domain only.</font>";
echo "<BR><BR>";
echo "Rules:<BR>\n";
echo "<ul>\n";
foreach($smartsearch_defs as $index => $definition){
	echo "<li>$definition\n";
}
echo "</ul>";
echo "<BR>Current List of Free Email Domains:<BR>\n";
require_once('custom/si_custom_files/custom_functions.php');
$domains = getDomainExclusionList();
echo "<ul>\n";
foreach($domains as $domain){
	echo "<li>$domain\n";
}
echo "</ul>\n";
