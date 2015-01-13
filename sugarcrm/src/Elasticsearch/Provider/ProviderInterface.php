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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;

/**
 *
 * Provider interface
 *
 */
interface ProviderInterface
{
    /**
     * Create mapping for given mapping context.
     * @param \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping\Mapping $mapping
     */
    public function buildProviderMapping(Mapping $mapping);

    /**
     * Create analysis settings based on the different index_analyzers and/or
     * search_analyzers which are being used in the field mappings.
     * @param \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder $analysisBuilder
     */
    public function buildProviderAnalysis(AnalysisBuilder $analysisBuilder);

    /**
     * Returns the list of fields to be indexed associated with its sugar type.
     * @param string $module
     * @return array
     */
    public function getBeanIndexFields($module);
}
