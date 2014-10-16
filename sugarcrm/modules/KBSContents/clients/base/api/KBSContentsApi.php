<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'include/api/SugarListApi.php';
require_once 'data/BeanFactory.php';

class KBSContentsApi extends SugarListApi
{
    public function registerApiRest()
    {
        return array(
            'related_documents' => array(
                'reqType' => 'GET',
                'path' => array('KBSContents', '?', 'related_documents'),
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

        $searchEngine = SugarSearchEngineFactory::getInstance();

        // TODO: Current sugar search interface doesn't allow using any query except "query string".
        // Construct it manually.
        $searchObj = new \Elastica\Search($searchEngine->getClient());
        $searchObj->addType($args['module']);
        $searchObj->addIndex($searchEngine->getReadIndexName(array($args['module'])));

        $mltName = new \Elastica\Query\MoreLikeThis();
        $mltName->setFields(array('name'));
        $mltName->setLikeText($targetBean->name);
        // TODO: Configure after demo.
        $mltName->setMinTermFrequency(1);
        $mltName->setMinDocFrequency(1);

        $mltBody = new \Elastica\Query\MoreLikeThis();
        $mltBody->setFields(array('kbdocument_body'));
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
        $statusFilterOr->addFilter(new \Elastica\Filter\Term(array('status' => 'published')));
        $statusFilterOr->addFilter(new \Elastica\Filter\Term(array('status' => 'published-in')));
        $statusFilterOr->addFilter(new \Elastica\Filter\Term(array('status' => 'published-ex')));
        $mainFilter->addMust($statusFilterOr);

        $query = new \Elastica\Query($boolQuery);
        $query->setFilter($mainFilter);
        $query->setParam('from', $options['offset']);
        $query->setSize($options['limit']);

        $resultSet = $searchObj->search($query);

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
}
