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

/**
 * Class KBSDuplicateCheckApi
 * Check duplicates for KBContents.
 */
class KBSDuplicateCheckApi extends SugarListApi
{
    public function registerApiRest()
    {
        return array(
            'duplicateCheck' => array(
                'reqType' => 'POST',
                'path' => array('KBContents','duplicateCheck'),
                'pathVars' => array('module',''),
                'method' => 'checkForDuplicates',
                'shortHelp' => 'Check for duplicate records within a module',
                'longHelp' => 'include/api/help/module_duplicatecheck_post_help.html',
            ),
        );
    }

    //FIXME It's better to use DuplicateCheckStrategy, but impossible due architectural limitations.
    /**
     * Using the appropriate duplicate check service, search for duplicates in the system
     * @see KBContentsApi::relatedDocuments
     * @param ServiceBase $api
     * @param array $args
     * @throws SugarApiExceptionInvalidParameter
     * @returns array
     */
    public function checkForDuplicates(ServiceBase $api, array $args)
    {
        $bean = BeanFactory::newBean($args['module']);
        if (!$bean->ACLAccess('view') || !$bean->ACLAccess('read')) {
            return;
        }
        $options = $this->parseArguments($api, $args);
        $errors = ApiHelper::getHelper($api, $bean)->populateFromApi($bean, $args, $options);
        if ($errors !== true) {
            $displayErrors = var_export($errors, true);
            throw new SugarApiExceptionInvalidParameter(
                "Unable to run duplicate check. There were validation errors on the submitted data: $displayErrors"
            );
        }

        $searchEngine = SugarSearchEngineFactory::getInstance();

        // Construct it manually.
        $searchObj = new \Elastica\Search($searchEngine->getClient());
        $searchObj->addType($args['module']);
        $searchObj->addIndex($searchEngine->getReadIndexName(array($args['module'])));

        $mltName = new \Elastica\Query\MoreLikeThis();
        $mltName->setFields(array('name'));
        $mltName->setLikeText($bean->name);
        $mltName->setMinTermFrequency(1);
        $mltName->setMinDocFrequency(1);

        $mltBody = new \Elastica\Query\MoreLikeThis();
        $mltBody->setFields(array('kbdocument_body'));
        $mltBody->setLikeText($bean->kbdocument_body);
        $mltBody->setMinTermFrequency(1);
        $mltBody->setMinDocFrequency(1);

        $boolQuery = new \Elastica\Query\Bool();
        $boolQuery->addShould($mltName);
        $boolQuery->addShould($mltBody);

        $mainFilter = new \Elastica\Filter\Bool();

        $activeRevFilter = new \Elastica\Filter\Term();
        $activeRevFilter->setTerm('active_rev', 1);
        $mainFilter->addMust($activeRevFilter);

        $langFilter = new \Elastica\Filter\Term();
        $langFilter->setTerm('language', $bean->language);
        $mainFilter->addMust($langFilter);

        $statusFilterOr = new \Elastica\Filter\BoolOr();
        foreach ($bean->getPublishedStatuses() as $status) {
            $statusFilterOr->addFilter(new \Elastica\Filter\Term(array('status' => $status)));
        }

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
