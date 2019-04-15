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

namespace Sugarcrm\SugarcrmTestsUnit\Composer;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class LockFileTest extends TestCase
{
    /**
     * This test enforces the {"packagist.org": false} configuration which Composer itself does not enforce.
     *
     * @dataProvider getPackages()
     */
    public function testPackageDist(array $package) : void
    {
        $this->assertArrayHasKey('dist', $package);

        $this->assertArrayHasKey('url', $package['dist']);
        $this->assertStringStartsWith('https://satis.sugardev.team/', $package['dist']['url']);

        $this->assertArrayHasKey('shasum', $package['dist']);
        $this->assertNotEmpty($package['dist']['shasum']);
    }

    /**
     * @return mixed[][]
     */
    public static function getPackages() : iterable
    {
        $contents = file_get_contents(__DIR__ . '/../../../../composer.lock');
        $data = json_decode($contents, true);

        foreach ($data['packages'] as $package) {
            if ($package['dist']['type'] === 'path') {
                continue;
            }

            yield $package['name'] => [$package];
        }
    }
}
