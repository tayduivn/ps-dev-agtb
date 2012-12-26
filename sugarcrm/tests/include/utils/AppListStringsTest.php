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

/**
 * See Bug22882Test.php for other tests on app_list_strings_language
 */
class AppListStringsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $temp_files = array();

    public function setUp()
    {
        if (!is_dir('custom/include/language'))
            @mkdir('custom/include/language', 0777, true);

        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
    }

    public function tearDown()
    {
        $this->restore_or_delete('include/language/fr_test.lang.php');
        $this->restore_or_delete('custom/include/language/en_us.lang.php');
        $this->restore_or_delete('custom/include/language/fr_test.lang.php');
    }

    public function testAppListStringsLanguage()
    {
        //Here we load french language
        $this->loadFrench();
        //Here we delete some items in account_type_dom
        $this->loadCustomEnglish();
        //Here we delete some items in case_type_dom
        $this->loadCustomFrench();

        $result = return_app_list_strings_language('fr_test');
        $expected = array(
            "account_type_dom" => array(
                'Partner' => 'Partenaire',
                'Press' => 'Presse',
                'Prospect' => 'Prospect',
                'School' => 'School',
                'Other' => 'Autre'
            ),
            "case_type_dom" => array(
                'Product' => 'Produit',
                'User' => 'Utilisateur',
                '' => ''
            )
        );
        $this->assertTrue(
            $this->isEqual($expected["account_type_dom"], $result["account_type_dom"]),
            'The english custom list string is not correctly loaded.'
        );
        $this->assertTrue(
            $this->isEqual($expected["case_type_dom"], $result["case_type_dom"]),
            'The french custom list string is not correctly loaded.'
        );
    }

    public function testIsEqual()
    {
        $arr1 = array(
            "a" => array(
                "aa" => array(
                    "aaa",
                    "aab",
                ),
                "ab" => array(
                    "aba",
                    "abb",
                ),
            ),
            "b" => array(
                "ba" => array(
                    "baa",
                    "bab",
                ),
                "bb" => array(
                    "bba",
                    "bbb",
                ),
            ),
        );
        $arr2 = array(
            "a" => array(
                "aa" => array(
                    "aaa",
                    "aab",
                ),
                "ab" => array(
                    "aba",
                    "abb",
                ),
            ),
            "b" => array(
                "ba" => array(
                    "baa",
                    "bab",
                ),
                "bb" => array(
                    "bbb", // CHANGE ORDER
                    "bba",
                ),
            ),
        );

        $this->assertFalse(
            $this->isEqual($arr1, $arr2),
            'isEqual does not make the job.'
        );
        $this->assertFalse(
            $this->isEqual($arr2, $arr1),
            'isEqual does not make the job.'
        );
    }

    /**
     * Creates a file saving the previous version if exists
     * @param string $filename
     * @param string $contents
     */
    protected function safe_create($filename, $contents)
    {
        if (file_exists($filename)) {
            $this->temp_files[$filename] = file_get_contents($filename);
        }
        file_put_contents($filename, $contents);
        SugarAutoLoader::addToMap($filename, false);
    }

    /**
     * Deletes a file or restore the previous version if exists
     * @param string $filename
     * @param string $contents
     */
    protected function restore_or_delete($filename)
    {
        if (!isset($this->temp_files[$filename]) && !empty($this->temp_files[$filename])) {
            file_put_contents($filename, $this->temp_files[$filename]);
            $this->temp_files[$filename] = '';
        } else if (file_exists($filename)) {
            unlink($filename);
            SugarAutoLoader::delFromMap($filename);
        }
    }

    /**
     * TRUE if $gimp and $dom have the same key/value pairs in the same order and of the same types.
     * @param $gimp
     * @param $dom
     * @return bool
     */
    protected function isEqual($gimp, $dom)
    {
        return $gimp === $dom;
    }

    private function loadFrench()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings=array(
    'account_type_dom'=> array (
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
    ),
);
FRFR;
        $this->safe_create('include/language/fr_test.lang.php', $file_fr);
    }

    private function loadCustomEnglish()
    {
        $file_custom_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyst', Line deleted
  //'Competitor' => 'Competitor', Line deleted
  //'Customer' => 'Customer', Line deleted
  //'Integrator' => 'Integrator', Line deleted
  //'Investor' => 'Investor', Line deleted
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'School' => 'School', // Line added
  'Other' => 'Other',
  //'' => '', Line deleted
);
ENEN;
        $this->safe_create('custom/include/language/en_us.lang.php', $file_custom_en);
    }

    private function loadCustomFrench()
    {
        $file_custom_fr = <<<FRFR
<?php
\$app_list_strings['case_type_dom']=array (
//'Administration' => 'Administration', Line deleted
'Product' => 'Produit',
'User' => 'Utilisateur',
'' => '',
);
FRFR;
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_custom_fr);
    }
}
