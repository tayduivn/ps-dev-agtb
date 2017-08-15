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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Factory;

/**
 *
 * Factory class to create mapping field base and mapping definition objects
 *
 */
class MappingFactory
{
    /**
     * values of index types
     */
    const INDEX_TRUE = true;
    const INDEX_FALSE = false;

    /**
     * string type
     */
    const TEXT_TYPE = 'text';
    const KEYWORD_TYPE = 'keyword';

    /**
     * elastic mapping keywords
     */
    const ES_KEYWORD_INDEX = 'index';
    const ES_KEYWORD_TYPE = 'type';
    const ES_KEYWORD_STORE = 'store';
    const ES_KEYWORD_FORMAT = 'format';
    const ES_KEYWORD_ANALYZER = 'analyzer';
    const ES_KEYWORD_SEARCH_ANALYZER = 'search_analyzer';
    const ES_KEYWORD_INCINALL = 'include_in_all';

    /**
     * allowed elastic types exception string or text
     * @var array
     */
    protected static $allowedTypes = array(
        'float',
        'double',
        'byte',
        'short',
        'integer',
        'long',
        'token_count',
        'date',
        'boolean',
    );

    /**
     * to create a FieldBase for mapping
     *
     * @param string $type, elastic type
     * @param bool|null $index, the index type
     * @param bool $includeInAll, elastic flag
     * @return array
     * @throws \Exception
     */
    public static function createFieldBase($type, $index, $includeInAll = false)
    {
        return self::createBaseProperty($type, $index);
    }

    /**
     * create base property, logic for es 1.x and 5.x are different
     *
     * @param string $type, elastic type
     * @param bool|null $index, index type
     * @return array
     * @throws \Exception
     */
    public static function createBaseProperty($type, $index)
    {
        $baseProperty = array();

        $baseProperty[self::ES_KEYWORD_TYPE] = $type;
        if (is_bool($index)) {
            $baseProperty[self::ES_KEYWORD_INDEX] = $index;
        } elseif (!empty($index)) {
            throw new \Exception("wrong index type: $index.");
        }

        return $baseProperty;
    }

    /**
     * core string type
     * @return string
     */
    public static function getBaseStringType()
    {
        return self::TEXT_TYPE;
    }

    /**
     * create mapping with analyzer
     * @param string $type, elastic type
     * @param bool|null $index, index type
     * @param string $analyzerName, analyzer name
     * @param string $searchAnalyzerName, search_analyzer name
     * @param boolean $store, to store or not to store
     * @param string $format, date format
     * @return array
     * @throws \Exception
     */
    public static function createMappingDef(
        $type,
        $index,
        $analyzerName,
        $searchAnalyzerName = null,
        $store = true,
        $format = null
    ) {
        $mappingAnalyzer = self::createBaseProperty($type, $index);

        // set up analyzer
        if (!empty($analyzerName)) {
            $mappingAnalyzer[self::ES_KEYWORD_ANALYZER] = $analyzerName;
        }

        // don't need to add search_analyzer if search_analyzer is the same as analyzer
        if (!empty($searchAnalyzerName) && $analyzerName != $searchAnalyzerName) {
            $mappingAnalyzer[self::ES_KEYWORD_SEARCH_ANALYZER] = $searchAnalyzerName;
        }
        $mappingAnalyzer[self::ES_KEYWORD_STORE] = $store;

        if (!empty($format)) {
            $mappingAnalyzer[self::ES_KEYWORD_FORMAT] = $format;
        }

        return $mappingAnalyzer;
    }

    /**
     * get allowed types
     * @return array
     */
    public static function getAllowedTypes()
    {
        return array_merge(array(self::TEXT_TYPE, self::KEYWORD_TYPE), self::$allowedTypes);
    }

    /**
     * check if it is a string type
     * @param string $type
     * @return bool
     */
    public static function isStringType($type)
    {
        return in_array($type, array(self::TEXT_TYPE, self::KEYWORD_TYPE));
    }
}

