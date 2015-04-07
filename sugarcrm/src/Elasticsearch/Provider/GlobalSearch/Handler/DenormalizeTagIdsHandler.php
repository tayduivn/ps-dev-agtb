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
 * Auto increment field handler
 *
 */
class DenormalizeTagIdsHandler extends AbstractHandler implements
    MappingHandlerInterface,
    ProcessDocumentHandlerInterface
{

    /**
     * Field name to use for tag Ids
     * @var string
     */
    protected $tagIdsField = 'tagIds';

    /**
     * {@inheritdoc}
     */
    public function processDocumentPreIndex(Document $document, \SugarBean $bean)
    {
        $tagIds = $this->retrieveTagIdsByQuery($bean->id);
        $document->setDataField($this->tagIdsField, $tagIds);
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
        $tagIds = $tagBean->getTagIdsByBeanId($beanId);
        return $tagIds;
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

        $tagIdsProperty = new RawProperty();
        $tagIdsProperty->setIsGenetic(true);
        $tagIdsProperty->setMapping(array(
            'type' => 'string',
            'index' => 'not_analyzed',
        ));
        $mapping->addRawProperty($this->tagIdsField, $tagIdsProperty);
    }
}
