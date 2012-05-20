<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once("include/utils.php");

/**
 * 
 * Testing function getReportNameTranslations in utils.php
 * If test fails, update data provider with new language strings 
 *
 */
class Bug51909Test extends Sugar_PHPUnit_Framework_TestCase {

	var $oldLanguage;
	
    public function setUp() {

        $this->markTestIncomplete("Disabling broken test on CI and working with Andrija to fix");

    	global $current_language;
        $this->oldLanguage = $current_language;
    }

    public function tearDown() {
        global $current_language;
        $current_language = $this->oldLanguage;
    }
	
    /**
     * @dataProvider bug51909DataProvider
     */
    public function testTranslation($reportName, $labelName, $language) {
    	global $current_language;
    	$current_language = $language; 
    	
		$title = getReportNameTranslation($reportName);
        $this->assertEquals($labelName, $title); 
    }

    /**
     * Data provider for translationTest()
     * @return string reportName, labelName, language
     */
    public function bug51909DataProvider() {
        return array(
            '0' => array('Calls By Team By User', 'Anrufe nach Team und Benutzer', 'de_DE'),
            '1' => array('My Module Usage (Last 30 Days)', 'Echelle pour l&#39;utilisation de Mon Module (30 Derniers Jours)', 'fr_FR'),
        	'2' => array('Open Cases By Month By User', 'Reclami Aperti per Mese per Utente', 'it_it'),
        	'3' => array('All Open Opportunities', 'Wszystkie otwarte okazje', 'pl_PL'),
        );
    }
}


?>