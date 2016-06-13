<?php
require_once 'tests/upgrade/UpgradeConvertPopupListViewTest.php';

/**
 * Class UpgradeConvertKBPopupListViewTest
 * Test that custom KB "popupdefs.php" converts to sidecar.
 */
class UpgradeConvertKBPopupListViewTest extends UpgradeConvertPopupListViewTest
{
    /**
     * @inheritdoc
     */
    public $module = 'KBContents';


    /**
     * @inheritdoc
     * @dataProvider defsDataProvider
     */
    public function testConvertPopupListFieldsToSidecarFormat($defs, $field)
    {
        $this->initDefs($defs);

        $script = $this->upgrader->getScript('post', '7_ConvertKBPopupListView');
        $script->from_version = 7.5;
        $script->to_version = 7.7;
        $script->run();

        $this->assertFileExists($this->selectionListPath);
        $sidecarParser = new SidecarListLayoutMetaDataParser(MB_SIDECARPOPUPVIEW, $this->module, null, 'base');

        $fieldDefs = $sidecarParser->panelGetField($field);
        $this->assertTrue($fieldDefs['field']['default']);
    }
}
