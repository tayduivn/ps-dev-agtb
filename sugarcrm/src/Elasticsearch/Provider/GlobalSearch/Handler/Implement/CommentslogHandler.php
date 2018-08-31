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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement;

use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\ObjectProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldBaseProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\AbstractHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\AnalysisHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MappingHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\SearchFieldsHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\ProcessDocumentHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchField;

/**
 *
 * Worklog Handler
 *
 */
class CommentslogHandler extends AbstractHandler implements
    AnalysisHandlerInterface,
    MappingHandlerInterface,
    SearchFieldsHandlerInterface,
    ProcessDocumentHandlerInterface
{
    /**
     * Multi field definitions
     * @var array
     */
    protected $multiFieldDefs = [
        'gs_commentslog' => [
            'type' => 'text',
            'index' => true,
            'analyzer' => 'gs_analyzer_commentslog',
            'store' => true,
        ],
        'gs_commentslog_wildcard' => [
            'type' => 'text',
            'index' => true,
            'analyzer' => 'gs_analyzer_commentslog_ngram',
            'search_analyzer' => 'gs_analyzer_commentslog',
            'store' => true,
        ],
    ];

    /**
     * Weighted boost definition
     * @var array
     */
    protected $weightedBoost = array(
        'gs_commentslog_wildcard_commentslog_entry' => 0.45,
    );

    /**
     * Highlighter field definitions
     * @var array
     */
    protected $highlighterFields = array(
        '*.gs_commentslog' => array(
            'number_of_fragments' => 0,
        ),
        '*.gs_commentslog_wildcard' => array(
            'number_of_fragments' => 0,
        ),
    );

    /**
     * Field name to use for commentslog search
     * @var string
     */
    protected $searchField = 'commentslog_search';

    /**
     * {@inheritdoc}
     */
    public function setProvider(GlobalSearch $provider)
    {
        parent::setProvider($provider);

        $provider->addSupportedTypes(array('commentslog'));
        $provider->addHighlighterFields($this->highlighterFields);
        $provider->addWeightedBoosts($this->weightedBoost);

        // As we are searching against commentslog_search field, we want to remap the
        // highlights from that field back to the original commentslog field.
        $provider->addFieldRemap(array($this->searchField => 'commentslog'));

        // We don't want to add the commentslog field to the queuemanager query
        // because we will populate the commentslogs seperately.
        $provider->addSkipTypesFromQueue(array('commentslog'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $analysisBuilder
            ->addCustomAnalyzer(
                'gs_analyzer_commentslog',
                'whitespace',
                array('lowercase')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_commentslog_ngram',
                'whitespace',
                array('lowercase', 'gs_filter_ngram_1_15')
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
        if (!$this->isCommentslogField($defs)) {
            return;
        }

        // Use original field to store the raw json content
        $baseObject = new ObjectProperty();
        $baseObject->setEnabled(false);
        $mapping->addModuleObjectProperty($field, $baseObject);

        // Prepare multifield
        $commentslog = new MultiFieldBaseProperty();
        foreach ($this->multiFieldDefs as $multiField => $defs) {
            $multiFieldProp = new MultiFieldProperty();
            $multiFieldProp->setMapping($defs);
            $commentslog->addField($multiField, $multiFieldProp);
        }

        // Additional field holding both primary/secondary addresses
        $searchField = new ObjectProperty();
        $searchField->addProperty('commentslog_entry', $commentslog);

        $searchFieldName = $mapping->getModule() . Mapping::PREFIX_SEP . $this->searchField;
        $mapping->addObjectProperty($searchFieldName, $searchField);
        $mapping->excludeFromSource($searchFieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchFields(SearchFields $sfs, $module, $field, array $defs)
    {
        if (!$this->isCommentslogField($defs)) {
            return;
        }

        $commentslogFields = array('commentslog_entry');
        $multiFields = array('gs_commentslog', 'gs_commentslog_wildcard');

        foreach ($commentslogFields as $commentslogField) {
            foreach ($multiFields as $multiField) {
                $sf = new SearchField($module, $defs['name'], $defs);
                $sf->setPath([$this->searchField, $commentslogField, $multiField]);
                $sfs->addSearchField($sf, $multiField . '_' . $commentslogField);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return array('commentslog');
    }

    /**
     * {@inheritdoc}
     */
    public function processDocumentPreIndex(Document $document, \SugarBean $bean)
    {
        // skip if there is no commentslog field
        if (!isset($bean->field_defs['commentslog'])) {
            return;
        }
        $defs = $bean->field_defs['commentslog'];
        if (!$this->isCommentslogField($defs)) {
            return;
        }

        $bean->load_relationship('commentslog_link');

        if (!$bean->commentslog_link) {
            // exit when relationship don't exist
            return;
        }

        $commentslog_beans = $bean->commentslog_link->getBeans();
        $commentslogs = array();

        foreach ($commentslog_beans as $id => $commentslog_bean) {
            $commentslogs[] = $commentslog_bean->entry;
        }

        $document->setDataField($document->getType() . Mapping::PREFIX_SEP . 'commentslog', $commentslogs);
        $document->removeDataField('commentslog');

        // Format data for commentslog search fields
        $value = array(
            'commentslog_entry' => array(),
        );

        foreach ($commentslogs as $commentslog) {
            $value['commentslog_entry'][] = $commentslog;
        }

        // Set formatted value in special commentslog search field
        $searchField = $document->getType() . Mapping::PREFIX_SEP . $this->searchField;
        $document->setDataField($searchField, $value);
    }

    /**
     * Check if given field def is an commentslog field
     * @param array $defs
     * @return boolean
     */
    protected function isCommentslogField(array $defs)
    {
        return $defs['name'] === 'commentslog' && $defs['type'] === 'commentslog';
    }
}
