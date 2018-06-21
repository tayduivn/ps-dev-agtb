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

namespace Sugarcrm\SugarcrmTestsBehat;

use PHPUnit\Framework\Assert;
use Sugarcrm\Sugarcrm\Cache\Exception;
use Symfony\Bridge\Twig\Extension\AssetExtension;

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

set_include_path(dirname(__FILE__) . '/../{old}' . PATH_SEPARATOR . get_include_path());

// using existing Sugar Test framework
//require_once "SugarTestHelper.php";

class BehatTestHelper
{
    private static $inst;

    private $records = [];

    /**
     * @return \BehatTestHelper
     */
    public static function getHelper()
    {
        if (!static::$inst) {
            static::$inst = new BehatTestHelper();
        }

        return static::$inst;
    }

    /**
     * @param string $module
     * @param array $props
     *
     * @return SugarBean
     */
    public function createRecord($module, $props)
    {
        $alias = "";
        $record = \BeanFactory::newBean($module);
        foreach ($props as $field => $value) {
            //Check for identifier field
            if (substr($field, 0, 1) === '*') {
                $alias = $value;
                $field = substr($field, 1);
                //if the ident was only '*', don't set it on the bean, just continue
                if (empty($field)) {
                    continue;
                }
            }
            $record->$field = $value;
        }

        $record->save();
        if (empty($alias)) {
            $alias = $record->id;
        }

        $this->records[$module][$alias] = $record;

        return $record;
    }

    /**
     * @param string $alias
     * @param string $module
     *
     * @return \SugarBean
     */
    public function updateRecord(\SugarBean $record, $props)
    {
        foreach ($props as $field => $value) {
            if (!isset($record->field_defs[$field])) {
                Assert::fail("unkown field $field");
            }
            $def = $record->field_defs[$field];
            $reloadRelationshipFields = false;
            if ($def['type'] == 'relate') {
                $this->linkRecordByRelateField($record, $def, $value);
                $reloadRelationshipFields = true;
                continue;
            }

            $record->$field = $value;
        }
        $record->save();

        if ($reloadRelationshipFields) {
            $record->fill_in_relationship_fields();
        }
    }

    /**
     * Some features reference related records by name (having created them earlier)
     * We should switch this to alias identifiers and map back to name when checking relate fields
     * at the UX level.
     *
     * @param \SugarBean $parentRecord
     * @param $def
     * @param $relatedName
     */
    private function linkRecordByRelateField(\SugarBean $parentRecord, $def, $relatedName)
    {
        if (empty($def['link'])) {
            Assert::fail("relate field $field does not have a link");
        }
        if (empty($def['rname'])) {
            Assert::fail("relate field $field does not have an rname");
        }
        $linkName = $def['link'];
        $idName = $def['id_name'] ?? '';


        if (!$parentRecord->load_relationship($linkName)) {
            Assert::fail("Unable to load link $linkName");
        }
        $relatedModule = $parentRecord->$linkName->getRelatedModuleName();
        $seedBean = \BeanFactory::newBean($relatedModule);

        try {
            $query = new \SugarQuery();
            $query->from($seedBean, ['team_security' => false]);
            $query->where()->equals($def['rname'], $relatedName);
            $query->limit(1);
            $result = $seedBean->fetchFromQuery($query, ['id']);
            if (!empty($result)) {
                $bean = reset($result);
                //Check if we actually found a valid bean by the rname field

                if (!empty($bean->id)) {
                    if ($idName) {
                        $parentRecord->$idName = $bean->id;
                    } else {
                        $parentRecord->$linkName->add($bean->id);
                    }
                }
            }
        }
        catch (\SugarQueryException $e) {
            Assert::fail("Unable to link related bean through {$def['name']} : " . $e->getMessage());
        }
    }

    /**
     * @param string $alias
     * @param string $module
     *
     * @return \SugarBean
     */
    public function getRecord($alias, $module = null)
    {
        if (!empty($module)) {
            if (isset($this->records[$module][$alias])) {
                return $this->records[$module][$alias];
            }
        } else {
            foreach ($this->records as $recordSet) {
                if (isset($recordSet[$alias])) {
                    return $recordSet[$alias];
                }
            }
        }

        return null;
    }

    /**
     * @param \SugarBean $record
     * @param string $fieldName
     * @param mixed $expected
     *
     * @return mixed
     */
    public function assertFieldValue(\SugarBean $record, string $fieldName, $expected)
    {
        if (!isset($record->field_defs[$fieldName])) {
            Assert::fail("unkown field $fieldName");
        }

        $type = $record->field_defs[$fieldName]['type'];
        $value = $record->$fieldName;

        if ($type === 'currency') {
            if (strlen($expected) > 0 && !preg_match('/\d/', substr($expected, 0, 1))) {
                $expected = floatval(substr($expected, 1));
                if (is_string($value)) {
                    $value = floatval($value);
                }
            }
        }

        Assert::assertSame($expected, $value);
    }

    public function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
        foreach ($this->records as $recordSet) {
            foreach ($recordSet as $record) {
                $record->mark_deleted($record->id);
            }
        }
        $this->records = [];
    }
}