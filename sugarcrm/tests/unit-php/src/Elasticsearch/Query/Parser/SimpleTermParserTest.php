<?php
/*
 * Your installation OR use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do NOT agree to all of the applicable terms OR do NOT have the
 * authority to bind the entity as an authorized representative, then do not
 * install OR use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Parser;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser
 */
class SimpleTermParserTest extends TestCase
{
    /**
     * @covers ::parse
     * @covers ::preProcess
     * @covers ::compressTerms
     * @covers ::createAndNotTerms
     * @covers ::setDefaultOperator
     * @covers ::getDefaultOperator
     * @covers ::setUseShortcutOperator
     *
     * @dataProvider providerParseTest
     */
    public function testParse(string $terms, string $defaultOperator, bool $useShortcut, array $expectecd)
    {
        $parser = new SimpleTermParser();
        $parser->setDefaultOperator($defaultOperator);
        $parser->setUseShortcutOperator($useShortcut);
        if (TermParserHelper::getOperator($defaultOperator)) {
            $this->assertSame(TermParserHelper::getOperator($defaultOperator), $parser->getDefaultOperator());
        }

        $this->assertSame($expectecd, $parser->parse($terms));
    }

    public function providerParseTest()
    {
        return array(
            // empty term always returns BasicTerm with 'OR'
            array('', 'OR', true, array('OR' => array(' '))),
            array('', '&', true, array('OR' => array(' '))),
            // to treat '-' as char if it is NOT leading by space
            array('a b-c d', 'OR', true, array('OR' => array('a b-c d'))),
            // leading 'NOT' operator
            array('-a b c', 'OR', true, array('OR' => array(array('NOT' => array('a')), 'b c'))),
            // combined cases without bracket
            array('a b OR c', 'OR', true, array('OR' => array('a b c'))),
            array('a b|c', 'OR', true, array('OR' => array('a b c'))),
            array('a b AND c', '&', true, array('AND' => array('a', 'b', 'c'))),
            array('a b&c', '&', true, array('AND' => array('a', 'b', 'c'))),
            array(
                'a b NOT c',
                'OR',
                true,
                array(
                    'OR' => array(array('AND' => array(array('AND' => array('b')), array('NOT' => array('c')))), 'a'),
                ),
            ),
            array(
                'a b -c',
                'OR',
                true,
                array(
                    'OR' => array(array('AND' => array(array('AND' => array('b')), array('NOT' => array('c')))), 'a'),
                ),
            ),
            array(
                'a b OR c d',
                '&',
                true,
                array('OR' => array(array('AND' => array('a', 'b')), array('AND' => array('c', 'd')))),
            ),
            array(
                'a AND b OR c & d',
                '|',
                true,
                array('OR' => array(array('AND' => array('a', 'b')), array('AND' => array('c', 'd')))),
            ),
            array(
                'a b NOT c',
                'AND',
                true,
                array('AND' => array(array('AND' => array('a', 'b')), array('NOT' => array('c')))),
            ),
            // expression with brackets
            array('(a b OR c)', 'OR', true, array('OR' => array('a b c'))),
            array(
                'a (b OR c) AND d',
                'OR',
                true,
                array('OR' => array(array('AND' => array(array('OR' => array('b c')), 'd')), 'a')),
            ),
            array(
                'a (b OR c) AND d',
                '&',
                true,
                array('AND' => array('a', array('OR' => array('b c')), 'd')),
            ),
            array(
                '(a OR b) AND (c OR d)',
                'OR',
                true,
                array('AND' => array(array('OR' => array('a b')), array('OR' => array('c d')))),
            ),
            // nested structure
            array(
                'a OR (b AND (c OR d))',
                'OR',
                true,
                array('OR' => array(array('AND' => array('b', array('OR' => array('c d')))), 'a')),
            ),
            // Not terms as a group
            array(
                'a -(b c)',
                'OR',
                true,
                array(
                    'AND' => array(
                        array('AND' => array('a')),
                        array('NOT' => array(array('OR' => array('b c')))),
                    ),
                ),
            ),
            // unbalanced braces, make a best guess
            array(
                'a OR (b AND (c OR d)',
                'OR',
                true,
                array('OR' => array(
                    array('OR' => array('a')),
                    array('AND' => array('b', array('OR' => array('c d'))))),
                ),
            ),
            // wrong parentheses
            array('a (b', 'OR', true, array('OR' => array(array('OR' => array('a')), 'b'))),
            array('a b) c', 'OR', true, array('OR' => array(array('OR' => array('a b')), 'c'))),
            array('a b &) c', 'OR', true, array('OR' => array(array('OR' => array('a b')), 'c'))),
            array('Johnson &) johnson', 'AND', true, array('AND' => array(array('OR' => array('Johnson')), 'johnson'))),
            array('Johnson & (johnson', 'AND', true, array('AND' => array(array('OR' => array('Johnson')), 'johnson'))),
            // end with operators
            array('a b c & AND OR | ', 'OR', true, array('OR' => array('a b c'))),
            // duplicated operators
            array('a b OR AND OR c &', 'OR', true, array('OR' => array('a b c'))),
            // phone number
            array('(128) 123-7944 text', 'OR', true, array('OR' => array(array('OR' => array('128')), '123-7944 text'))),
            array('(128) 123-7944 text', 'AND', true, array('AND' => array(array('OR' => array('128')), '123-7944', 'text'))),
            // using default operator
            array('(128) 123-7944 text', 'nt', true, array('AND' => array(array('OR' => array('128')), '123-7944', 'text'))),
            array(
                'gmail.com (-kate OR -smith OR dean)',
                '&',
                true,
                array('AND' =>
                    array(
                        'gmail.com',
                        array(
                            'OR' => array(
                                array('NOT' => array('kate')),
                                array('NOT' => array('smith')),
                                'dean',
                            ),
                        ),
                    ),
                ),
            ),
            // test lower case 'and', 'or' and 'or', which are not operators
            array('a and b or c not d', 'OR', true, array('OR' => array('a and b or c not d'))),
            array('a Or AND and', '&', true, array('AND' => array('a', 'Or', 'and'))),
            // terms with spaces
            array('a   b   AND    and', '&', true, array('AND' => array('a', 'b', 'and'))),
            array('        ', '&', true, array('OR' => array(' '))),
            array('   AND     ', '&', true, array('OR' => array(' '))),
            // large terms, 'AND'
            array(
                '111111111111111222222222222222333333333 AND 4444444444444445555555555555551',
                'AND',
                true,
                array(
                    'AND' => array(
                        array(
                            'AND' => array(
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ),
                        ),
                        array(
                            'AND' => array(
                                '444444444444444',
                                '555555555555555',
                                '1',
                            ),
                        ),
                    ),
                ),
            ),
            // large terms, 'OR'
            array(
                '111111111111111222222222222222333333333 444',
                'OR',
                true,
                array(
                    'OR' => array(
                        array(
                            'AND' => array(
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ),
                        ),
                        '444',
                    ),
                ),
            ),
            array(
                '111111111111111222222222222222333333333 NOT 444',
                'NOT',
                true,
                array(
                    'AND' => array(
                        array(
                            'AND' => array(
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ),
                        ),
                        array(
                            'NOT' => array('444'),
                        ),
                    ),
                ),
            ),

            // don't use shortcut
            // empty term always returns BasicTerm with 'OR'
            array('', 'OR', false, array('OR' => array(' '))),
            array('', '&', false, array('OR' => array(' '))),
            // to treat '-' as char if it is NOT leading by space
            array('a b-c d', 'OR', false, array('OR' => array('a b-c d'))),
            // leading 'NOT' operator
            array('-a b c', 'OR', false, array('OR' => array('-a b c'))),
            // combined cases without bracket
            array('a b OR c', 'OR', false, array('OR' => array('a b c'))),
            array('a b|c', 'OR', false, array('OR' => array('a b|c'))),
            array('a b AND c', '&', false, array('AND' => array('a', 'b', 'c'))),
            array('a b&c', '&', false, array('AND' => array('a', 'b&c'))),
            array(
                'a b NOT c',
                'OR',
                false,
                array(
                    'OR' => array(array('AND' => array(array('AND' => array('b')), array('NOT' => array('c')))), 'a'),
                ),
            ),
            array(
                'a b -c',
                'OR',
                false,
                array(
                    'OR' => array('a b -c'),
                ),
            ),
            array(
                'a b OR c d',
                '&',
                false,
                array('OR' => array(array('AND' => array('a', 'b')), array('AND' => array('c', 'd')))),
            ),
            // special chars
            array('a !@#$ d', 'OR', true, array('OR' => array('a d'))),
            array('a !@#$ d', 'AND', true, array('AND' => array('a', 'd'))),
            array('a !@#$ d', 'OR', false, array('OR' => array('a d'))),
            array('a !@#$ d', 'AND', false, array('AND' => array('a', 'd'))),
            array("a ' d", 'AND', true, array('AND' => array('a', 'd'))),
        );
    }
}
