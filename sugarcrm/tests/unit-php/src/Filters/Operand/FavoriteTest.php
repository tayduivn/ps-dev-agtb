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

namespace Sugarcrm\SugarcrmTests\Filters\Operand;

use ServiceBase;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Operand\Favorite;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Operand\Favorite
 */
class FavoriteTest extends TestCase
{
    public function filterProvider()
    {
        return [
            'link not specified' => [
                '',
            ],
            'link is _this' => [
                '_this',
            ],
            'link is specified' => [
                'opportunities',
            ],
        ];
    }

    /**
     * @covers ::format
     * @dataProvider filterProvider
     */
    public function testFormat($filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $operand = new Favorite($api, $filter);

        $actual = $operand->format();

        $this->assertSame($filter, $actual);
    }

    /**
     * @covers ::unformat
     * @dataProvider filterProvider
     */
    public function testUnformat($filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $operand = new Favorite($api, $filter);

        $actual = $operand->unformat();

        $this->assertSame($filter, $actual);
    }
}
