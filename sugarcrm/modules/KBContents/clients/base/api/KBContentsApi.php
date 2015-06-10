<?php
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

use \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder;

require_once 'include/api/SugarListApi.php';
require_once 'data/BeanFactory.php';

class KBContentsApi extends SugarListApi
{
    public function registerApiRest()
    {
        return array(
            'related_documents' => array(
                'reqType' => 'GET',
                'path' => array('KBContents', '?', 'related_documents'),
                'pathVars' => array('module', 'record'),
                'method' => 'relatedDocuments',
                'shortHelp' => 'Get related documents for current record.',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Return related document using "more like this" query.
     *
     * @param $api ServiceBase The API class of the request.
     * @param $args array The arguments array passed in from the API.
     * @return array 'records' the list of returned records formatted through FormatBean, and 'next_offset'
     * which will indicate to the user if there are additional records to be returned.
     */
    public function relatedDocuments($api, $args)
    {
        $targetBean = BeanFactory::getBean($args['module'], $args['record']);
        if (!$targetBean->ACLAccess('view')) {
            return;
        }
        $options = $this->parseArguments($api, $args);

        $builder = $this->getElasticQueryBuilder($args, $options);
        $ftsFields = ApiHelper::getHelper($api, $targetBean)->getElasticSearchFields(array('name', 'kbdocument_body'));

        // TODO: Current sugar search interface doesn't allow using any query except "query string".
        // Construct it manually.
        $mltName = new \Elastica\Query\MoreLikeThis();
        $mltName->setFields($ftsFields['name']);
        $mltName->setLikeText($targetBean->name);
        // TODO: Configure after demo.
        $mltName->setMinTermFrequency(1);
        $mltName->setMinDocFrequency(1);

        $mltBody = new \Elastica\Query\MoreLikeThis();
        $mltBody->setFields($ftsFields['kbdocument_body']);
        $mltBody->setLikeText($targetBean->kbdocument_body);
        // TODO: Configure after demo.
        $mltBody->setMinTermFrequency(1);
        $mltBody->setMinDocFrequency(1);

        $boolQuery = new \Elastica\Query\Bool();
        $boolQuery->addShould($mltName); // And, addMust() for OR.
        $boolQuery->addShould($mltBody);

        // Exclude the target record.
        $mainFilter = new \Elastica\Filter\Bool();
        $currentIdFilter = new \Elastica\Filter\Term();
        $currentIdFilter->setTerm('_id', $targetBean->id);
        $mainFilter->addMustNot($currentIdFilter);

        $activeRevFilter = new \Elastica\Filter\Term();
        $activeRevFilter->setTerm('active_rev', 1);
        $mainFilter->addMust($activeRevFilter);

        $statusFilterOr = new \Elastica\Filter\BoolOr();
        foreach ($targetBean->getPublishedStatuses() as $status) {
            $statusFilterOr->addFilter(new \Elastica\Filter\Term(array('status' => $status)));
        }
        $mainFilter->addMust($statusFilterOr);

        $builder->setQuery($boolQuery);
        $builder->addFilter($mainFilter);

        $resultSet = $builder->executeSearch();

        $returnedRecords = array();

        foreach ($resultSet as $result) {
            $record = BeanFactory::retrieveBean($result->getType(), $result->getId());
            if (!$record) {
                continue;
            }
            $formattedRecord = $this->formatBean($api, $args, $record);
            $formattedRecord['_module'] = $result->getType();
            $returnedRecords[] = $formattedRecord;
        }

        if ($resultSet->getTotalHits() > ($options['limit'] + $options['offset'])) {
            $nextOffset = $options['offset'] + $options['limit'];
        } else {
            $nextOffset = -1;
        }

        return array('next_offset' => $nextOffset, 'records' => $returnedRecords);
    }

    /**
     * Get configured Elastic search builder.
     * @param $args array The arguments array passed in from the API.
     * @param $options array An array with the options limit, offset, fields and order_by set
     * @return QueryBuilder
     */
    protected function getElasticQueryBuilder(array $args, array $options)
    {
        global $current_user;

        $engineContainer = SearchEngine::getInstance()->getEngine()->getContainer();
        $builder = new QueryBuilder($engineContainer);
        $builder
            ->setUser($current_user)
            ->setModules(array($args['module']))
            ->setOffset($options['offset'])
            ->setLimit($options['limit']);

        return $builder;
    }
}
