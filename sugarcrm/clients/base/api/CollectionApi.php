<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once 'include/api/SugarApi.php';
require_once 'clients/base/api/RelateApi.php';

/**
 * Collection API
 */
class CollectionApi extends SugarApi
{
    protected $relateApi;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relateApi = new RelateApi();
    }

    /**
     * Registers API
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function registerApiRest()
    {
        return array(
            'getCollection' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'collection', '?'),
                'pathVars' => array('module', 'record', '', 'collection_name'),
                'method' => 'getCollection',
                'shortHelp' => 'Lists collection records.',
                'longHelp' => 'include/api/help/module_record_collection_collection_name_get_help.html',
            ),
        );
    }

    /**
     * API endpoint
     *
     * @param ServiceBase $api
     * @param array $args
     *
     * @return array
     * @throws SugarApiExceptionError
     * @throws SugarApiExceptionInvalidParameter
     * @throws SugarApiExceptionMissingParameter
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiExceptionNotFound
     */
    public function getCollection(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'record', 'collection_name'));
        $bean = $this->loadBean($api, $args);

        $definition = $this->getCollectionDefinition($bean, $args['collection_name']);
        $args = $this->normalizeArguments($args, $definition);

        $data = $this->getData($api, $args, $definition['links']);
        $allRecords = $this->flattenData($data, $nextOffset);
        $this->sortRecords($allRecords, $args['order_by'], $definition['links']);

        $records = array_slice($allRecords, 0, $args['max_num']);
        $remainder = array_slice($allRecords, $args['max_num']);
        $nextOffset = $this->getNextOffset($args['offset'], $records, $nextOffset, $remainder);

        return array(
            'records' => $records,
            'next_offset' => $nextOffset,
        );
    }

    /**
     * Retrieves records from collection links
     *
     * @param ServiceBase $api
     * @param array $args API arguments
     * @param array $links Collection link definitions
     *
     * @return array
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiExceptionNotFound
     */
    protected function getData(ServiceBase $api, array $args, array $links)
    {
        $data = array();
        foreach ($links as $link) {
            $linkName = $link['name'];
            if ($args['offset'][$linkName] >= 0) {
                $linkArgs = $this->getLinkArguments($args, $link);
                $data[$linkName] = $this->relateApi->filterRelated($api, $linkArgs);
            }
        }

        return $data;
    }

    /**
     * Creates arguments for RelateApi for specific link
     *
     * @param array $args CollectionApi arguments
     * @param array $link Collection link definition
     *
     * @return array RelateApi arguments
     */
    protected function getLinkArguments(array $args, array $link)
    {
        $args = array_merge($args, array(
            'link_name' => $link['name'],
            'offset' => $args['offset'][$link['name']],
        ));

        if (isset($link['field_map']) && isset($args['filter'])) {
            $args['filter'] = $this->mapFilter($args['filter'], $link['field_map']);
        }

        if (isset($link['field_map'])) {
            $args['order_by'] = $this->mapOrderBy($args['order_by'], $link['field_map']);
        }

        $args['order_by'] = $this->formatOrderBy($args['order_by']);

        return $args;
    }

    /**
     * @param SugarBean $bean SugarBean instance that represents module metadata
     * @param string $collectionName Collection name
     *
     * @return array Link definition
     * @throws SugarApiExceptionError
     * @throws SugarApiExceptionNotFound
     */
    protected function getCollectionDefinition(SugarBean $bean, $collectionName)
    {
        $definition = $bean->getFieldDefinition($collectionName);
        if (!is_array($definition) || !isset($definition['type']) || $definition['type'] !== 'collection') {
            throw new SugarApiExceptionNotFound(
                sprintf('Could not find collection %s in module %s', $collectionName, $bean->module_name)
            );
        }

        if (!isset($definition['links'])) {
            throw new SugarApiExceptionError(
                sprintf('Links are not defined for collection %s in module %s', $collectionName, $bean->module_name)
            );
        }

        $definition['links'] = $this->normalizeLinks($definition['links'], $collectionName, $bean->module_name);

        return $definition;
    }

    /**
     * Normalizes and validates link definitions in collection metadata
     *
     * @param array $links
     * @param $collectionName
     * @param $moduleName
     *
     * @return array Normalized definitions
     * @throws SugarApiExceptionError
     */
    protected function normalizeLinks($links, $collectionName, $moduleName)
    {
        if (!is_array($links)) {
            throw new SugarApiExceptionError(
                sprintf(
                    'Links must be array, %s is given for collection %s in module %s',
                    gettype($links),
                    $collectionName,
                    $moduleName
                )
            );
        }

        $normalized = array();
        foreach ($links as $i => $link) {
            if (is_string($link)) {
                $link = array('name' => $link);
            } elseif (is_array($link)) {
                if (!isset($link['name']) || !is_string($link['name'])) {
                    throw new SugarApiExceptionError(
                        sprintf(
                            'Link #%d name is not defined for collection %s in module %s',
                            $i,
                            $collectionName,
                            $moduleName
                        )
                    );
                }
            } else {
                throw new SugarApiExceptionError(
                    sprintf(
                        'Link definition must be string or array, %s is given for link #%d, collection %s in module %s',
                        gettype($link),
                        $i,
                        $collectionName,
                        $moduleName
                    )
                );
            }

            $normalized[] = $link;
        }

        return $normalized;
    }

    protected function normalizeArguments(array $args, array $definition)
    {
        $args['offset'] = $this->normalizeOffset($args, $definition['links']);
        if (!isset($args['max_num'])) {
            $args['max_num'] = $this->relateApi->getDefaultLimit();
        }

        $args['order_by'] = $this->getOrderByFromArgs($args);

        if (!$args['order_by']) {
            if (isset($definition['order_by'])) {
                $args['order_by'] = $this->getOrderByFromArgs(array(
                    'order_by' => $definition['order_by'],
                ));
            } else {
                $args['order_by'] = $this->getDefaultOrderBy();
            }
        }

        return $args;
    }

    /**
     * Normalizes and validates offset API argument
     *
     * @param array $args API arguments
     * @param array $links Link definitions
     *
     * @return array Normalized value
     * @throws SugarApiExceptionInvalidParameter
     */
    protected function normalizeOffset(array $args, array $links)
    {
        if (isset($args['offset'])) {
            if (!is_array($args['offset'])) {
                throw new SugarApiExceptionInvalidParameter(
                    sprintf('Offset must be an array, %s given', gettype($args['offset']))
                );
            }

            $offset = $args['offset'];
        } else {
            $offset = array();
        }

        $keys = array();
        foreach ($links as $link) {
            $name = $link['name'];
            $keys[$name] = true;
            if (!isset($offset[$name])) {
                $offset[$name] = 0;
            } else {
                $offset[$name] = (int) $offset[$name];
                if ($offset[$name] < 0) {
                    $offset[$name] = -1;
                }
            }
        }

        // we remove all irrelevant offsets here, since later we'll be returning new offsets,
        // and we don't need irrelevant offsets to be returned
        $offset = array_intersect_key($offset, $keys);

        return $offset;
    }

    /**
     * Create one-dimensional array of records from multiple arrays
     *
     * @param array $data Multi-dimensional array of records retrieved from links
     * @param array $nextOffset Associative array of next offset for each link
     *
     * @return array Flattened array
     */
    protected function flattenData(array $data, &$nextOffset)
    {
        $flattened = array();
        foreach ($data as $linkName => $response) {
            foreach ($response['records'] as $record) {
                $record['_link'] = $linkName;
                $flattened[] = $record;
            }
            $nextOffset[$linkName] = $response['next_offset'];
        }

        return $flattened;
    }

    /**
     * Sorts collection data
     *
     * @param array $data Collection data
     * @param array $orderBy Order specification
     * @param array $links Link definitions
     */
    protected function sortRecords(array &$data, $orderBy, array $links)
    {
        $map = array();
        foreach ($links as $link) {
            if (isset($link['field_map'])) {
                foreach ($link['field_map'] as $alias => $field) {
                    $map[$link['name']][$field] = $alias;
                }
            }
        }

        usort($data, function ($a, $b) use ($map, $orderBy) {
            foreach ($orderBy as $alias => $direction) {

                if (isset($a['_link'], $map[$a['_link']][$alias])) {
                    $fieldA = $map[$a['_link']][$alias];
                } else {
                    $fieldA = $alias;
                }

                if (isset($b['_link'], $map[$b['_link']][$alias])) {
                    $fieldB = $map[$b['_link']][$alias];
                } else {
                    $fieldB = $alias;
                }

                if (!isset($a[$fieldA], $b[$fieldB])) {
                    continue;
                }

                $result = strcasecmp($a[$fieldA], $b[$fieldB]);
                if ($result != 0) {
                    return $result * ($direction ? 1 : -1);
                }
            }

            return 0;
        });
    }

    /**
     * Generates the value of new offset based on initial offset and the set of records being returned
     *
     * @param array $offset Initial value of offset
     * @param array $records Returned records
     * @param array $nextOffset Collection of offsets returned by Relate API
     * @param array $remainder Not returned records
     *
     * @return array New value of offset
     */
    protected function getNextOffset(array $offset, array $records, array $nextOffset, array $remainder)
    {
        $returned = $truncated = array();

        foreach ($nextOffset as $linkName => $_) {
            $returned[$linkName] = 0;
        }

        foreach ($records as $record) {
            $returned[$record['_link']]++;
        }

        foreach ($remainder as $record) {
            $truncated[$record['_link']] = true;
        }

        foreach ($offset as $linkName => $value) {
            if (!isset($nextOffset[$linkName])) {
                $nextOffset[$linkName] = $value;
            } elseif (isset($truncated[$linkName])) {
                $nextOffset[$linkName] = $offset[$linkName] + $returned[$linkName];
            }
        }

        return $nextOffset;
    }

    /**
     * Map filter definition using field map
     *
     * @param array $filter
     * @param array $fieldMap
     *
     * @return array
     */
    protected function mapFilter(array $filter, array $fieldMap)
    {
        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $filter[$key] = $this->mapFilter($filter[$key], $fieldMap);
            }
        }

        return $this->mapArray($filter, $fieldMap);
    }

    /**
     * Maps internal representation of ORDER BY definition
     *
     * @param array $orderBy
     * @param array $fieldMap
     *
     * @return array
     */
    protected function mapOrderBy(array $orderBy, array $fieldMap)
    {
        return $this->mapArray($orderBy, $fieldMap);
    }

    /**
     * Converts array by replacing aliased keys with real field names
     *
     * @param array $array
     * @param array $fieldMap
     *
     * @return array
     * @throws SugarApiExceptionInvalidParameter
     */
    protected function mapArray(array $array, array $fieldMap)
    {
        $mapped = array();
        foreach ($array as $alias => $value) {
            if (isset($fieldMap[$alias])) {
                $field = $fieldMap[$alias];
            } else {
                $field = $alias;
            }

            if (isset($mapped[$field])) {
                throw new SugarApiExceptionInvalidParameter(
                    'More than one alias pointing to the same field is used in expression'
                );
            }

            $mapped[$field] = $value;
        }

        return $mapped;
    }

    /**
     * Formats ORDER BY from internal representation
     *
     * @param array $orderBy
     *
     * @return string
     */
    protected function formatOrderBy(array $orderBy)
    {
        $formatted = array();
        foreach ($orderBy as $field => $direction) {
            $column = $field;
            if (!$direction) {
                $column .= ':desc';
            }
            $formatted[] = $column;
        }

        return implode(',', $formatted);
    }

    /**
     * Returns default ORDER BY in internal representation
     *
     * @return array
     */
    protected function getDefaultOrderBy()
    {
        $orderBy = array();
        foreach ($this->relateApi->getDefaultOrderBy() as $column) {
            $field = array_shift($column);
            $direction = array_shift($column);
            $orderBy[$field] = strtolower($direction) != 'desc';
        }

        return $orderBy;
    }
}
