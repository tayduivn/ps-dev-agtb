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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty;


/**
 *
 * Tags handler
 *
 */
class TagsHandler extends AbstractHandler implements
    MappingHandlerInterface,
    ProcessDocumentHandlerInterface
{
    /**
     * Field name to use for tag Ids
     * @var string
     */
    const TAGS_FIELD = 'tags';

    /**
     * {@inheritdoc}
     */
    public function processDocumentPreIndex(Document $document, \SugarBean $bean)
    {
        $tags = $this->retrieveTagIdsByQuery($bean->id);
        $document->setDataField(self::TAGS_FIELD, $tags);
    }

    /**
     * Retrieve the value of a given field from the database.
     * @param string $beanId the id of the associated bean
     * @return array
     */
    protected function retrieveTagIdsByQuery($beanId)
    {
        //Use SugarBean
        $tagBean = \BeanFactory::getBean("Tags");
        $tags = $tagBean->getTagIdsByBeanId($beanId);
        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
        // We only handle 'tag' fields of 'tag' type
        if ($defs['name'] !== 'tag' || $defs['type'] !== 'tag') {
            return;
        }

        // we just need an not_analyzed field here
        $mapping->addNotAnalyzedField($field);
    }
}
