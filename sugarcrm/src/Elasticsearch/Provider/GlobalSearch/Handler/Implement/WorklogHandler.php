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
class WorklogHandler extends AbstractHandler implements
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
        'gs_worklog' => [
            'type' => 'text',
            'index' => true,
            'analyzer' => 'gs_analyzer_worklog',
            'store' => true,
        ],
        'gs_worklog_wildcard' => [
            'type' => 'text',
            'index' => true,
            'analyzer' => 'gs_analyzer_worklog_ngram',
            'search_analyzer' => 'gs_analyzer_worklog',
            'store' => true,
        ],
    ];

    /**
     * Weighted boost definition
     * @var array
     */
    protected $weightedBoost = array(
        'gs_worklog_wildcard_worklog_entry' => 0.45,
    );

    /**
     * Highlighter field definitions
     * @var array
     */
    protected $highlighterFields = array(
        '*.gs_worklog' => array(
            'number_of_fragments' => 0,
        ),
        '*.gs_worklog_wildcard' => array(
            'number_of_fragments' => 0,
        ),
    );

    /**
     * Field name to use for worklog search
     * @var string
     */
    protected $searchField = 'worklog_search';

    /**
     * {@inheritdoc}
     */
    public function setProvider(GlobalSearch $provider)
    {
        parent::setProvider($provider);

        $provider->addSupportedTypes(array('worklog'));
        $provider->addHighlighterFields($this->highlighterFields);
        $provider->addWeightedBoosts($this->weightedBoost);

        // As we are searching against worklog_search field, we want to remap the
        // highlights from that field back to the original worklog field.
        $provider->addFieldRemap(array($this->searchField => 'worklog'));

        // We don't want to add the worklog field to the queuemanager query
        // because we will populate the worklogs seperately.
        $provider->addSkipTypesFromQueue(array('worklog'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $analysisBuilder
            ->addCustomAnalyzer(
                'gs_analyzer_worklog',
                'whitespace',
                array('lowercase')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_worklog_ngram',
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
        if (!$this->isWorklogField($defs)) {
            return;
        }

        // Use original field to store the raw json content
        $baseObject = new ObjectProperty();
        $baseObject->setEnabled(false);
        $mapping->addModuleObjectProperty($field, $baseObject);

        // Prepare multifield
        $worklog = new MultiFieldBaseProperty();
        foreach ($this->multiFieldDefs as $multiField => $defs) {
            $multiFieldProp = new MultiFieldProperty();
            $multiFieldProp->setMapping($defs);
            $worklog->addField($multiField, $multiFieldProp);
        }

        // Additional field holding both primary/secondary addresses
        $searchField = new ObjectProperty();
        $searchField->addProperty('worklog_entry', $worklog);

        $searchFieldName = $mapping->getModule() . Mapping::PREFIX_SEP . $this->searchField;
        $mapping->addObjectProperty($searchFieldName, $searchField);
        $mapping->excludeFromSource($searchFieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchFields(SearchFields $sfs, $module, $field, array $defs)
    {
        if (!$this->isWorklogField($defs)) {
            return;
        }

        $worklogFields = array('worklog_entry');
        $multiFields = array('gs_worklog', 'gs_worklog_wildcard');

        foreach ($worklogFields as $worklogField) {
            foreach ($multiFields as $multiField) {
                $sf = new SearchField($module, $defs['name'], $defs);
                $sf->setPath([$this->searchField, $worklogField, $multiField]);
                $sfs->addSearchField($sf, $multiField . '_' . $worklogField);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return array('worklog');
    }

    /**
     * {@inheritdoc}
     */
    public function processDocumentPreIndex(Document $document, \SugarBean $bean)
    {
        // skip if there is no worklog field
        if (!isset($bean->field_defs['worklog'])) {
            return;
        }
        $defs = $bean->field_defs['worklog'];
        if (!$this->isWorklogField($defs)) {
            return;
        }

        $bean->load_relationship('worklog_link');

        if (!$bean->worklog_link) {
            // exit when relationship don't exist
            return;
        }

        $worklog_beans = $bean->worklog_link->getBeans();
        $worklogs = array();

        foreach ($worklog_beans as $id => $worklog_bean) {
            $worklogs[] = $worklog_bean->entry;
        }

        $document->setDataField($document->getType() . Mapping::PREFIX_SEP . 'worklog', $worklogs);
        $document->removeDataField('worklog');

        // Format data for worklog search fields
        $value = array(
            'worklog_entry' => array(),
        );

        foreach ($worklogs as $worklog) {
            $value['worklog_entry'][] = $worklog;
        }

        // Set formatted value in special worklog search field
        $searchField = $document->getType() . Mapping::PREFIX_SEP . $this->searchField;
        $document->setDataField($searchField, $value);
    }

    /**
     * Check if given field def is an worklog field
     * @param array $defs
     * @return boolean
     */
    protected function isWorklogField(array $defs)
    {
        return $defs['name'] === 'worklog' && $defs['type'] === 'worklog';
    }
}
