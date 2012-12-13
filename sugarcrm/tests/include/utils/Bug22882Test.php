<?php
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

require_once 'modules/Calls/metadata/additionalDetails.php';
require_once 'tests/include/utils/AppListStringsTest.php';

/**
 * @ticket 22882
 */
class Bug22882Test extends AppListStringsTest
{
    public function testMultiLanguagesDeletedValue()
    {
        $this->loadFilesDeletedValue();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertTrue( $this->isEqual($resultfr, $resulten) );
    }

    public function testMultiLanguagesDeletedValueFrOnly()
    {
        $this->loadFilesDeletedValueFrOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
    }

    public function testMultiLanguagesDeletedValueEnOnly()
    {
        $this->loadFilesDeletedValueEnOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr),count($resulten));
        $this->assertFalse(in_array('Customer',$resulten));
        $this->assertTrue(in_array('Customer',$resultfr));
    }

    public function testMultiLanguagesAddedValue()
    {
        $this->loadFilesAddedValueEn();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
    }


    /**
     * Bug 57431 : the custom default language overrides the current language
     */
    public function testMultiLanguagesCustomValueEnOnly()
    {
        $this->loadFilesAddedCustomValueEnOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = $resultfr['account_type_dom']['Analyst'];
        $resulten = $resulten['account_type_dom']['Analyst'];
        $this->assertNotEquals($resultfr, $resulten, 'The custom default language overrides french lang.');
        $this->cleanupFiles();
    }


    public function loadFilesDeletedValue(){
            $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyste', Line deleted
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyst', Line deleted
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesDeletedValueFrOnly(){
            $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyste', Line deleted
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesDeletedValueEnOnly(){
            $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyste',
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  //'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesAddedValueEn(){
            $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyste',
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Extra' => 'Extra',
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }


    public function loadFilesAddedCustomValueEnOnly(){
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']['Analyst'] = 'Test';
ENEN;

        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']['Analyst'] = 'Test (French)';
FRFR;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }
}
