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

$dictionary['VisualPipeline'] = array(
    'table' => 'visual_pipeline',
    'comment' => 'VisualPipeline is required for module name and routing',
    'audited' => false,
    'activity_enabled' => false,
    'duplicate_merge' => false,
    'fields' => array(),
);

VardefManager::createVardef(
    'VisualPipeline',
    'VisualPipeline'
);
