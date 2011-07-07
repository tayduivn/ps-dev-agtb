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

/**
 * @ticket 22882
 */
class Bug22882Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $file = '';
    private $file_fr_tmp = '';
    private $file_en_tmp = '';
    
    public function setUp()
    {
        if ( !is_dir('custom/include/language') )
            @mkdir('custom/include/language', 0777, true);
        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
    }
    
    public function testMultiLanguagesDeletedValue()
    {
        $this->loadFilesDeletedValue();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        if($this->isSameSize($resultfr, $resulten)){
            $this->isEqual($resultfr, $resulten);
        }
        $this->cleanupFiles();
    }
    
    public function testMultiLanguagesDeletedValueFrOnly()
    {
        $this->loadFilesDeletedValueFrOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
        $this->cleanupFiles();
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
        $this->cleanupFiles();
    }
    
    public function testMultiLanguagesAddedValue()
    {
        $this->loadFilesAddedValueEn();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
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
        if(!file_exists('include/language/fr_test.lang.php')){
            $this->file = file_get_contents('include/language/en_us.lang.php');
            file_put_contents('include/language/fr_test.lang.php', $this->file);
        }
        if(!file_exists('custom/include/language/fr_test.lang.php')){
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }else{
            $this->file_fr_tmp = file_get_contents('custom/include/language/fr_test.lang.php');
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }
        if(!file_exists('custom/include/language/en_us.lang.php')){
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }else{
            $this->file_en_tmp = file_get_contents('custom/include/language/en_us.lang.php');
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }
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
        if(!file_exists('include/language/fr_test.lang.php')){
            $this->file = file_get_contents('include/language/en_us.lang.php');
            file_put_contents('include/language/fr_test.lang.php', $this->file);
        }
        if(!file_exists('custom/include/language/fr_test.lang.php')){
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }else{
            $this->file_fr_tmp = file_get_contents('custom/include/language/fr_test.lang.php');
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }
        if(!file_exists('custom/include/language/en_us.lang.php')){
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }else{
            $this->file_en_tmp = file_get_contents('custom/include/language/en_us.lang.php');
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }
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
        if(!file_exists('include/language/fr_test.lang.php')){
            $this->file = file_get_contents('include/language/en_us.lang.php');
            file_put_contents('include/language/fr_test.lang.php', $this->file);
        }
        if(!file_exists('custom/include/language/fr_test.lang.php')){
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }else{
            $this->file_fr_tmp = file_get_contents('custom/include/language/fr_test.lang.php');
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }
        if(!file_exists('custom/include/language/en_us.lang.php')){
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }else{
            $this->file_en_tmp = file_get_contents('custom/include/language/en_us.lang.php');
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }
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
        if(!file_exists('include/language/fr_test.lang.php')){
            $this->file = file_get_contents('include/language/en_us.lang.php');
            file_put_contents('include/language/fr_test.lang.php', $this->file);
        }
        if(!file_exists('custom/include/language/fr_test.lang.php')){
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }else{
            $this->file_fr_tmp = file_get_contents('custom/include/language/fr_test.lang.php');
            file_put_contents('custom/include/language/fr_test.lang.php', $file_fr);
        }
        if(!file_exists('custom/include/language/en_us.lang.php')){
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }else{
            $this->file_en_tmp = file_get_contents('custom/include/language/en_us.lang.php');
            file_put_contents('custom/include/language/en_us.lang.php', $file_en);
        }
    }
    
    public function cleanupFiles(){
        if(!empty($this->file)){
            $this->file = '';
            unlink('include/language/fr_test.lang.php');
        }
        if(!empty($this->file_fr_tmp)){
            file_put_contents('custom/include/language/fr_test.lang.php', $this->file_fr_tmp);
            $this->file_fr_tmp = '';
        }else{
            unlink('custom/include/language/fr_test.lang.php');
        }
        if(!empty($this->file_en_tmp)){
            file_put_contents('custom/include/language/en_us.lang.php', $this->file_en_tmp);
            $this->file_en_tmp = '';
        }else{
            unlink('custom/include/language/en_us.lang.php');
        }
    }
    
    public function isSameSize($result1, $result2)
    {
        if(count($result1) != count($result2)){
            $this->assertTrue(false, 'The 2 drop down list didn\'t have the same size.');
            return false;
        }
        return true;
    }
    
    public function isEqual($result1, $result2)
    {
        foreach($result1 as $k=>$v){
            $this->assertTrue(in_array($v,$result2));
        }
        foreach($result2 as $k=>$v){
            $this->assertTrue(in_array($v,$result1));
        }
    }
}
