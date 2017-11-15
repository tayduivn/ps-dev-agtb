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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\State\Storage;

use BeanFactory;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage\AdminSettingsStorage as Storage;
use Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\State\StorageTest;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage\AdminSettingsStorage
 */
class AdminSettingsStorageTest extends StorageTest
{
    protected function createStorage()
    {
        return new Storage();
    }

    /**
     * @test
     */
    public function unexpectedValueStored()
    {
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting(Storage::CATEGORY, Storage::NAME, 'garbage');

        $this->assertNull($this->storage->get('whatever'));
    }
}
