<?php
// FILE SUGARCRM flav=ent ONLY

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Import/Importer.php';
require_once 'modules/Import/sources/ImportFile.php';
require_once 'modules/Import/ImportFileSplitter.php';
require_once 'include/export_utils.php';

class TeamBasedACLImportTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TeamSet
     */
    protected $teamSet;

    /**
     * @var Importer
     */
    protected $importer;

    /**
     * @var SugarBean
     */
    protected $beanToExport;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, false));

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamSet = BeanFactory::getBean('TeamSets');
        $this->teamSet->addTeams(array($team->id));

        $this->beanToExport = SugarTestAccountUtilities::createAccount();
        $this->beanToExport->team_set_selected_id = $this->teamSet->id;
        $this->beanToExport->team_selected_name = TeamSetManager::getCommaDelimitedTeams(
            $this->beanToExport->team_set_selected_id,
            $this->beanToExport->team_id
        );
        $this->beanToExport->save();

        $this->importer = $this->getMockBuilder('Importer')
            ->setMethods(array('getImportColumns'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $this->teamSet->mark_deleted($this->teamSet->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

    /**
     * The team_set_selected_id field should be importable.
     */
    public function testImporttTBA()
    {
        $importedRecordId = $this->prepareImporter(array(
            'team_id' => $this->beanToExport->team_id,
            'team_set_selected_id' => $this->beanToExport->team_set_selected_id,
            'team_selected_name' => $this->beanToExport->team_selected_name,
        ));
        $this->importer->import();
        $importedBean = BeanFactory::getBean($this->module, $importedRecordId);

        $this->assertEquals($this->beanToExport->team_set_selected_id, $importedBean->team_set_selected_id);
    }

    /**
     * The team_set_selected_id field should be populated by team_selected_name.
     */
    public function testImportByTeamSelectedName()
    {
        $expectedTeamSet = $this->beanToExport->team_set_selected_id;

        $this->beanToExport->team_set_selected_id = null;
        $this->beanToExport->save();
        $importedRecordId = $this->prepareImporter(array(
            'team_id' => $this->beanToExport->team_id,
            // The "team_set_selected_id" is not specified.
            'team_selected_name' => $this->beanToExport->team_selected_name,
        ));
        $this->importer->import();
        $importedBean = BeanFactory::getBean($this->module, $importedRecordId);

        $this->assertNotEmpty($importedBean->team_set_selected_id);
        $this->assertEquals($expectedTeamSet, $importedBean->team_set_selected_id);
    }

    /**
     * Setup importer with source.
     * @param array $nameValue
     * @return string Id of future record.
     */
    protected function prepareImporter(array $nameValue)
    {
        $id = create_guid();
        $exportStr = '';
        $importColumns = array();

        $importColumns[] = 'id';
        $exportStr .= $this->enclosure . $id . $this->enclosure . $this->delimiter;
        foreach ($nameValue as $key => $val) {
            $importColumns[] = $key;
            $exportStr .= $this->enclosure . $val . $this->enclosure . $this->delimiter;
        }
        SugarTestAccountUtilities::setCreatedAccount(array($id));

        $file = SugarTestImportUtilities::createFile();
        file_put_contents($file, $exportStr);

        $source = new ImportFile($file, $this->delimiter, $this->enclosure);

        $this->importer->expects($this->any())->method('getImportColumns')->will(
            $this->returnValue($importColumns)
        );
        $this->importer->__construct($source, $this->beanToExport);

        return $id;
    }
}
