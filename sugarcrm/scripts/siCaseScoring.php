<?php

chdir('..');
define('sugarEntry', true);
require_once('include/entryPoint.php');

require('custom/si_custom_files/caseScoringFunctions.php');

// This function call will score all open cases
siLogThis('caseScoring.log', 'Start SI Case Scoring');
siCaseScore(array());
siLogThis('caseScoring.log', 'End SI Case Scoring');
