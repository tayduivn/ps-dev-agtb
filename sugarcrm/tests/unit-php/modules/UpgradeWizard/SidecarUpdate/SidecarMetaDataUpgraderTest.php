<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
namespace Sugarcrm\SugarcrmTestUnit\modules\UpgradeWizard\SidecarUpdate;

/**
 * @coversDefaultClass \SidecarMetaDataUpgrader
 */
class SidecarMetaDataUpgraderTest extends \PHPUnit_Framework_TestCase
{

    protected $beanlist;
    protected $bwcModules;

    public function setup()
    {
        global $bwcModules;
        \SugarAutoLoader::load('../../modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php');
        \SugarAutoLoader::load('../../include/utils.php');
        $this->beanlist = array_key_exists('beanlist', $GLOBALS)? $GLOBALS['beanlist'] : null;
        $this->bwcModules = $bwcModules;
        parent::setup();
    }

    public function teardown()
    {
        global $bwcModules;

        if ($this->beanlist != null) {
            $GLOBALS['beanlist'] = $this->beanlist;
        }

        $bwcModules = $this->bwcModules;

        parent::teardown();
    }

    /**
     * @covers ::upgradeRelatedModuleSubpanel
     */
    public function testUpgradeRelatedModuleSubpanel()
    {
        $GLOBALS['beanList']['Quotes'] = true;
        $mock = $this->getMockBuilder('\SidecarMetaDataUpgrader')
            ->setMethods(['getUpgradeableFilesInPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getUpgradeableFilesInPath')
            ->with(
                'custom/modules/Documents/metadata/subpanels/',
                'Documents',
                'base',
                'base',
                null,
                true,
                true
            )
            ->will($this->returnValue(array()));

        $mock->upgradeRelatedModuleSubpanel('_overrideQuote_subpanel_documents');
    }

    /**
     * @covers ::getUpgradeFileParams
     */
    public function testGetUpgradeFileParams_subpanel()
    {
        global $bwcModules;
        $bwcModules = array();
        $mock = $this->getMockBuilder('\SidecarMetaDataUpgrader')
            ->setMethods([
                'logUpgradeStatus',
                'addUpgradeModule',
                'getViewTypeFromFilename',
                'upgradeRelatedModuleSubpanel'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getViewTypeFromFilename')
            ->will($this->returnValue('layoutdef'));

        $mock->expects($this->once())
            ->method('upgradeRelatedModuleSubpanel');

        $mock->getUpgradeFileParams(
            'Quote_subpanel_documents',
            'Quotes',
            'base',
            'base',
            'null',
            true,
            false,
            true
        );
    }
}
